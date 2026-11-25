<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Service;
use App\Models\WebhookLog;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WeFlexfyWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_SECRET = 'test-weflexfy-webhook-secret-key-hs256';

    public function test_web_webhook_route_excludes_validate_csrf_token_and_confirms_booking(): void
    {
        Mail::fake();
        config(['services.weflexfy.secret_key' => self::TEST_SECRET]);

        $route = app('router')->getRoutes()->getByName('weflexfy.webhook');
        $this->assertNotNull($route);
        $this->assertContains(ValidateCsrfToken::class, $route->excludedMiddleware());

        [$booking, $payment] = $this->createPendingBookingPayment('req-tok-web-001', 15000);

        $jwt = JWT::encode([
            'requestToken' => 'req-tok-web-001',
            'paymentRef' => 'PAY-WEB-001',
            'status' => 'SUCCESS',
            'amount' => 15000,
            'paymentMethod' => 'MTN_MOMO',
            'transactionId' => 'TXN-WEB-001',
            'exp' => time() + 300,
        ], self::TEST_SECRET, 'HS256');

        $this->postJson('/webhook/weflexfy', [
            'token' => $jwt,
            'requestType' => 'payment',
        ])->assertOk();

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('momo', $payment->actual_method_used);
        $this->assertNotSame('pending', $booking->status);
        $this->assertDatabaseHas('webhook_logs', [
            'type' => 'weflexfy_payment',
            'status' => 'SUCCESS',
        ]);
    }

    public function test_api_webhook_accepts_signed_payment_event_when_payment_method_is_omitted(): void
    {
        Mail::fake();
        config(['services.weflexfy.secret_key' => self::TEST_SECRET]);

        [$booking, $payment] = $this->createPendingBookingPayment('req-tok-api-002', 20000);

        $jwt = JWT::encode([
            'requestToken' => 'req-tok-api-002',
            'paymentRef' => 'PAY-API-002',
            'status' => 'FAILED',
            'amount' => 20000,
            'exp' => time() + 300,
        ], self::TEST_SECRET, 'HS256');

        $this->postJson('/api/webhook/weflexfy', [
            'token' => $jwt,
            'requestType' => 'payment',
        ])->assertOk();

        $payment->refresh();
        $booking->refresh();

        $this->assertSame('failed', $payment->status);
        $this->assertSame('declined', $booking->status);
    }

    public function test_webhook_rejects_transfer_payload_replayed_as_payment(): void
    {
        config(['services.weflexfy.secret_key' => self::TEST_SECRET]);

        [, $payment] = $this->createPendingBookingPayment('req-tok-replay-003', 10000);

        $jwt = JWT::encode([
            'requestToken' => 'req-tok-replay-003',
            'transferRef' => 'CRPWEC123456',
            'status' => 'SUCCESS',
            'amount' => 10000,
            'exp' => time() + 300,
        ], self::TEST_SECRET, 'HS256');

        $this->postJson('/api/webhook/weflexfy', [
            'token' => $jwt,
            'requestType' => 'payment',
        ])
            ->assertStatus(400)
            ->assertJson(['error' => 'Webhook payload does not describe a payment event.']);

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_rejected_webhooks_are_logged_without_exposing_raw_tokens(): void
    {
        config(['services.weflexfy.secret_key' => self::TEST_SECRET]);

        $this->postJson('/webhook/weflexfy', [])
            ->assertStatus(400)
            ->assertJson(['error' => 'Invalid webhook payload.']);

        $rawBadJwt = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJiYWQifQ.invalidsig';
        $this->postJson('/api/webhook/weflexfy', [
            'token' => $rawBadJwt,
            'requestType' => 'payment',
        ])
            ->assertStatus(401)
            ->assertJson(['error' => 'Invalid webhook signature.']);

        $rejectedLogs = WebhookLog::where('type', 'weflexfy_rejected')->get();
        $this->assertCount(2, $rejectedLogs);

        foreach ($rejectedLogs as $log) {
            $encodedPayload = json_encode($log->payload);
            $this->assertIsString($encodedPayload);
            $this->assertStringNotContainsString($rawBadJwt, $encodedPayload);
            $this->assertStringNotContainsString(self::TEST_SECRET, $encodedPayload);
        }
    }

    private function createPendingBookingPayment(string $requestToken, float $amount): array
    {
        $provider = Provider::create([
            'name' => 'Webhook Stylist',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0780000100',
            'active' => true,
        ]);

        $service = Service::create([
            'name' => 'Gel Manicure',
            'description' => 'Test service',
            'price' => $amount,
            'duration_minutes' => 45,
            'approved' => true,
        ]);
        $provider->services()->attach($service->id);

        $customer = Customer::create([
            'name' => 'Webhook Guest',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0780000101',
        ]);

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '11:00',
            'payment_option' => 'full',
            'deposit_amount' => 0,
            'status' => 'pending',
            'reference' => 'INB-'.strtoupper(fake()->unique()->bothify('????######')),
            'source' => 'website',
        ]);
        $booking->services()->attach($service->id, [
            'price_at_booking' => $service->price,
            'duration_minutes_at_booking' => $service->duration_minutes,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'request_token' => $requestToken,
            'amount' => $amount,
            'method' => 'momo',
            'status' => 'pending',
        ]);

        return [$booking, $payment];
    }
}
