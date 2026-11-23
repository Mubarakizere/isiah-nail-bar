<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;
use App\Models\Service;
use App\Models\User;
use App\Support\ApiToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminBookingOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => '0123456789abcdef0123456789abcdef']);
        Carbon::setTestNow(Carbon::parse('2026-11-17 12:00:00', config('app.timezone')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_calendar_api_requires_admin_role(): void
    {
        $query = http_build_query([
            'start' => now()->toDateString(),
            'end' => now()->toDateString(),
        ]);

        $this->getJson('/api/v1/admin/calendar?'.$query)->assertUnauthorized();

        $providerUser = User::factory()->create();
        $providerUser->assignRole($this->role('provider'));

        $this->withHeaders($this->tokenHeaders($providerUser))
            ->getJson('/api/v1/admin/calendar?'.$query)
            ->assertForbidden();
    }

    public function test_calendar_api_returns_booking_snapshots_working_hours_and_blocks(): void
    {
        [$provider, $service] = $this->providerWithService();
        $customer = $this->customer();
        $date = now()->toDateString();
        $booking = $this->booking($provider, $service, $customer, $date, '10:00', 'accepted');
        $booking->services()->updateExistingPivot($service->id, [
            'price_at_booking' => 15000,
            'duration_minutes_at_booking' => 30,
        ]);
        $service->update(['price' => 22000, 'duration_minutes' => 45]);
        Payment::create([
            'booking_id' => $booking->id,
            'reference' => 'PAY-TEST-01',
            'amount' => 5000,
            'method' => 'cash',
            'status' => 'paid',
        ]);

        ProviderWorkingHour::create([
            'provider_id' => $provider->id,
            'day_of_week' => Carbon::parse($date)->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'is_day_off' => false,
            'is_holiday' => false,
        ]);
        $this->postJson('/api/v1/admin/slots/block', [
            'provider_id' => $provider->id,
            'date' => $date,
            'time' => '15:00',
        ], $this->adminHeaders())->assertOk();

        $query = http_build_query(['start' => $date, 'end' => $date]);
        $this->getJson('/api/v1/admin/calendar?'.$query, $this->adminHeaders())
            ->assertOk()
            ->assertJsonPath('events.0.source', 'website')
            ->assertJsonPath('events.0.provider_name', $provider->name)
            ->assertJsonPath('events.0.services.0.price', 15000)
            ->assertJsonPath('events.0.services.0.duration_minutes', 30)
            ->assertJsonPath('events.0.end_time', '10:30')
            ->assertJsonPath('events.0.total_paid', 5000)
            ->assertJsonPath('events.0.balance', 10000)
            ->assertJsonPath('working_hours.0.start_time', '09:00')
            ->assertJsonPath('blocked_slots.0.time', '15:00')
            ->assertJsonPath('summary.bookings', 1);
    }

    public function test_walk_in_records_customer_provider_services_and_payment(): void
    {
        [$provider, $service] = $this->providerWithService([
            'price' => 20000,
            'duration_minutes' => 45,
        ]);
        $date = now()->toDateString();

        $response = $this->postJson('/api/v1/admin/bookings/walk-in', [
            'customer_id' => 'new',
            'customer_name' => 'Walk-in Guest',
            'customer_phone' => '0780000000',
            'provider_id' => $provider->id,
            'services' => [$service->id],
            'date' => $date,
            'time' => '10:00',
            'status' => 'completed',
            'payment_amount' => 6000,
            'payment_method' => 'cash',
            'notes' => 'Walk-in feature test',
        ], $this->adminHeaders())->assertCreated();

        $bookingId = $response->json('data.id');
        $booking = Booking::with('services')->findOrFail($bookingId);

        $this->assertSame('walk_in', $booking->source);
        $this->assertSame('completed', $booking->status);
        $this->assertSame($provider->id, $booking->provider_id);
        $this->assertSame('Walk-in Guest', $booking->customer->name);
        $this->assertSame(20000.0, (float) $booking->services->first()->pivot->price_at_booking);
        $this->assertSame(45, (int) $booking->services->first()->pivot->duration_minutes_at_booking);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $bookingId,
            'amount' => 6000,
            'method' => 'cash',
            'status' => 'paid',
        ]);
    }

    public function test_walk_in_rejects_a_provider_time_conflict(): void
    {
        [$provider, $service] = $this->providerWithService();
        $customer = $this->customer();
        $date = now()->toDateString();
        $this->booking($provider, $service, $customer, $date, '10:00', 'accepted');

        $this->postJson('/api/v1/admin/bookings/walk-in', [
            'customer_id' => 'new',
            'customer_name' => 'Conflicting Walk-in',
            'provider_id' => $provider->id,
            'services' => [$service->id],
            'date' => $date,
            'time' => '10:15',
            'status' => 'completed',
        ], $this->adminHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('time');

        $this->assertDatabaseMissing('customers', ['name' => 'Conflicting Walk-in']);
    }

    public function test_walk_in_rejects_future_visits(): void
    {
        [$provider, $service] = $this->providerWithService();

        $this->postJson('/api/v1/admin/bookings/walk-in', [
            'customer_id' => 'new',
            'customer_name' => 'Future Walk-in',
            'provider_id' => $provider->id,
            'services' => [$service->id],
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'status' => 'in_progress',
        ], $this->adminHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function test_manual_booking_uses_provider_hours_and_saves_price_duration_snapshots(): void
    {
        [$provider, $service] = $this->providerWithService();
        $customer = $this->customer();
        $date = now()->addDays(2)->toDateString();
        $this->workingHours($provider, $date);

        $response = $this->postJson('/api/v1/admin/bookings/manual', [
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'services' => [$service->id],
            'date' => $date,
            'time' => '10:00',
            'payment_option' => 'full',
            'send_email' => false,
        ], $this->adminHeaders())->assertCreated();

        $booking = Booking::with('services')->findOrFail($response->json('data.id'));
        $this->assertSame('admin_manual', $booking->source);
        $this->assertSame((float) $service->price, (float) $booking->services->first()->pivot->price_at_booking);
        $this->assertSame((int) $service->duration_minutes, (int) $booking->services->first()->pivot->duration_minutes_at_booking);
    }

    public function test_manual_booking_rejects_times_outside_provider_hours(): void
    {
        [$provider, $service] = $this->providerWithService();
        $customer = $this->customer();
        $date = now()->addDays(2)->toDateString();
        $this->workingHours($provider, $date);

        $this->postJson('/api/v1/admin/bookings/manual', [
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'services' => [$service->id],
            'date' => $date,
            'time' => '08:30',
            'payment_option' => 'full',
            'send_email' => false,
        ], $this->adminHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('time');

        $this->assertDatabaseMissing('bookings', ['date' => $date, 'time' => '08:30']);
    }

    public function test_sitemap_is_served_as_xml_without_a_blade_view(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false);
    }

    public function test_customer_receipt_is_rendered_as_a_pdf_without_a_blade_view(): void
    {
        [$provider, $service] = $this->providerWithService();
        $customer = $this->customer();
        $customerUser = User::factory()->create(['customer_id' => $customer->id]);
        $customerUser->assignRole($this->role('customer'));
        $booking = $this->booking($provider, $service, $customer, now()->addDay()->toDateString(), '10:00', 'accepted');

        $response = $this->get('/api/v1/customer/bookings/'.$booking->id.'/receipt-pdf', $this->tokenHeaders($customerUser))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function providerWithService(array $serviceAttributes = []): array
    {
        $provider = Provider::create([
            'name' => 'Test Provider',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0780000000',
            'active' => true,
        ]);

        $service = Service::create(array_merge([
            'name' => 'Test Manicure',
            'description' => 'Feature test service',
            'price' => 15000,
            'duration_minutes' => 30,
            'approved' => true,
        ], $serviceAttributes));

        $provider->services()->attach($service->id);

        return [$provider, $service];
    }

    private function customer(): Customer
    {
        return Customer::create([
            'name' => 'Test Customer',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0780000001',
        ]);
    }

    private function booking(
        Provider $provider,
        Service $service,
        Customer $customer,
        string $date,
        string $time,
        string $status
    ): Booking {
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'provider_id' => $provider->id,
            'date' => $date,
            'time' => $time,
            'payment_option' => 'full',
            'status' => $status,
            'reference' => 'TEST-'.fake()->unique()->numerify('######'),
            'source' => 'website',
        ]);
        $booking->services()->attach($service->id, [
            'price_at_booking' => $service->price,
            'duration_minutes_at_booking' => $service->duration_minutes,
        ]);

        return $booking;
    }

    private function workingHours(Provider $provider, string $date): void
    {
        ProviderWorkingHour::create([
            'provider_id' => $provider->id,
            'day_of_week' => Carbon::parse($date)->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'is_day_off' => false,
            'is_holiday' => false,
        ]);
    }

    private function role(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    private function adminHeaders(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole($this->role('admin'));

        return $this->tokenHeaders($admin);
    }

    private function tokenHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.ApiToken::issue($user)];
    }
}
