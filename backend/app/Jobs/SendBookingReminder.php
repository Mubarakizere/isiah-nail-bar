<?php

namespace App\Jobs;

use App\Mail\BookingReminderMail;
use App\Mail\ProviderBookingReminderMail;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendBookingReminder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 90;

    public int $uniqueFor = 1800;

    public bool $failOnTimeout = true;

    public function __construct(public int $bookingId)
    {
    }

    public function uniqueId(): string
    {
        return 'booking-reminder-'.$this->bookingId;
    }

    public function handle(): void
    {
        $booking = Booking::query()
            ->with(['customer.user', 'provider', 'services'])
            ->find($this->bookingId);

        if (!$booking || $booking->reminder_sent || $booking->status !== 'accepted') {
            return;
        }

        $startsAt = Carbon::parse($booking->date.' '.$booking->time, config('app.timezone'));
        if (!$startsAt->isFuture()) {
            return;
        }

        $customer = $booking->customer;
        $user = $customer?->user;
        $provider = $booking->provider;
        $failedChannels = [];

        $customerEmail = $customer?->email ?? $user?->email;
        if ($customerEmail) {
            $this->sendOnce($booking, 'customer_email', function () use ($customerEmail, $booking): void {
                Mail::to($customerEmail)->send(new BookingReminderMail($booking));
            }, $failedChannels);
        } else {
            Log::notice('Booking reminder has no customer email address.', ['booking_id' => $booking->id]);
        }

        if ($provider) {
            foreach (array_unique($provider->getNotificationEmails()) as $providerEmail) {
                if (!is_string($providerEmail) || trim($providerEmail) === '') {
                    continue;
                }

                $emailKey = 'provider_email_'.hash('sha256', strtolower(trim($providerEmail)));
                $this->sendOnce($booking, $emailKey, function () use ($providerEmail, $booking): void {
                    Mail::to($providerEmail)->send(new ProviderBookingReminderMail($booking));
                }, $failedChannels);
            }
        }

        if ($failedChannels !== []) {
            throw new RuntimeException('One or more booking reminder deliveries failed.');
        }

        Booking::query()
            ->whereKey($booking->id)
            ->where('status', 'accepted')
            ->where('reminder_sent', false)
            ->update([
                'reminder_sent' => true,
                'reminder_failed_at' => null,
                'updated_at' => now(),
            ]);
    }

    public function failed(Throwable $exception): void
    {
        Booking::query()
            ->whereKey($this->bookingId)
            ->where('reminder_sent', false)
            ->update([
                'reminder_failed_at' => now(),
                'updated_at' => now(),
            ]);

        Log::error('Booking reminder permanently failed after queue retries.', [
            'booking_id' => $this->bookingId,
            'exception' => $exception::class,
        ]);
    }

    private function sendOnce(Booking $booking, string $deliveryKey, callable $send, array &$failedChannels): void
    {
        $freshBooking = Booking::query()->find($booking->id);
        if (!$freshBooking || $freshBooking->reminder_sent) {
            return;
        }

        $deliveries = $freshBooking->reminder_delivery_status ?? [];
        if (!empty($deliveries[$deliveryKey])) {
            return;
        }

        try {
            $send();
            DB::transaction(function () use ($booking, $deliveryKey): void {
                $lockedBooking = Booking::query()->lockForUpdate()->find($booking->id);
                if (!$lockedBooking) {
                    return;
                }

                $deliveries = $lockedBooking->reminder_delivery_status ?? [];
                $deliveries[$deliveryKey] = now()->toIso8601String();
                $lockedBooking->reminder_delivery_status = $deliveries;
                $lockedBooking->save();
            }, 3);
        } catch (Throwable $exception) {
            $failedChannels[] = $deliveryKey;
            Log::warning('Booking reminder delivery failed and will be retried.', [
                'booking_id' => $booking->id,
                'channel' => $deliveryKey,
                'exception' => $exception::class,
            ]);
        }
    }

}
