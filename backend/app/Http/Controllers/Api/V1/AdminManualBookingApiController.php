<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Provider;
use App\Models\Service;
use App\Notifications\BookingCreatedManually;
use App\Services\BookingScheduleGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminManualBookingApiController extends Controller
{
    public function options()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'customers' => Customer::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'email', 'phone']),
                'providers' => Provider::query()
                    ->where('active', true)
                    ->with('services:id')
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Provider $provider) => [
                        'id' => (int) $provider->id,
                        'name' => $provider->name,
                        'service_ids' => $provider->services->pluck('id')->map(fn ($id) => (int) $id)->values(),
                    ])
                    ->values(),
                'services' => Service::query()
                    ->where('approved', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'price', 'duration_minutes']),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $isNewCustomer = $request->input('customer_id') === 'new';
        $newCustomerRule = $isNewCustomer ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
        $newEmailRule = $isNewCustomer
            ? ['required', 'email', 'max:255', 'unique:customers,email']
            : ['nullable', 'email', 'max:255'];

        $validated = $request->validate([
            'customer_id' => $isNewCustomer
                ? ['required', Rule::in(['new'])]
                : ['required', 'integer', 'exists:customers,id'],
            'new_customer_name' => $newCustomerRule,
            'new_customer_email' => $newEmailRule,
            'new_customer_phone' => ['nullable', 'string', 'max:50'],
            'provider_id' => ['required', 'integer', Rule::exists('providers', 'id')->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'payment_option' => ['required', Rule::in(['full', 'deposit'])],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'required_if:payment_option,deposit'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $serviceIds = array_values(array_unique(array_map('intval', $validated['services'])));
        $services = Service::query()
            ->where('approved', true)
            ->whereIn('id', $serviceIds)
            ->get(['id', 'name', 'price', 'duration_minutes']);

        if ($services->count() !== count($serviceIds)) {
            throw ValidationException::withMessages([
                'services' => 'Choose services that are currently approved and available.',
            ]);
        }

        $providerOffersServices = DB::table('provider_service')
            ->where('provider_id', $validated['provider_id'])
            ->whereIn('service_id', $serviceIds)
            ->distinct()
            ->count('service_id');
        if ($providerOffersServices !== count($serviceIds)) {
            throw ValidationException::withMessages([
                'provider_id' => 'Choose a provider who is assigned to every service selected for this booking.',
            ]);
        }

        $total = (float) $services->sum('price');
        $deposit = $validated['payment_option'] === 'deposit'
            ? (float) ($validated['deposit_amount'] ?? 0)
            : 0;

        if ($validated['payment_option'] === 'deposit' && $deposit > $total) {
            throw ValidationException::withMessages([
                'deposit_amount' => 'Deposit cannot be greater than the selected services total.',
            ]);
        }

        [$booking, $customer] = DB::transaction(function () use ($validated, $isNewCustomer, $serviceIds, $deposit, $services) {
            if (DB::getDriverName() === 'sqlite') {
                DB::table('providers')->where('id', $validated['provider_id'])->update(['id' => DB::raw('id')]);
            }

            $provider = Provider::query()
                ->whereKey($validated['provider_id'])
                ->where('active', true)
                ->lockForUpdate()
                ->first();
            if (!$provider) {
                throw ValidationException::withMessages(['provider_id' => 'Choose an active provider.']);
            }

            $scheduleGuard = app(BookingScheduleGuard::class);
            $scheduleGuard->assertProviderOffersServices($provider->id, $serviceIds);
            $duration = max(15, (int) $services->sum(fn (Service $service) => max(15, (int) ($service->duration_minutes ?: 60))));
            $scheduleGuard->assertAvailable($provider, $validated['date'], $validated['time'], $duration);

            $customer = $isNewCustomer
                ? Customer::create([
                    'name' => trim($validated['new_customer_name']),
                    'email' => trim($validated['new_customer_email']),
                    'phone' => $validated['new_customer_phone'] ?? null,
                ])
                : Customer::findOrFail($validated['customer_id']);

            do {
                $reference = 'MAN-' . Str::upper(Str::random(14));
            } while (Booking::where('reference', $reference)->exists());

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $validated['provider_id'],
                'date' => $validated['date'],
                'time' => $validated['time'],
                'payment_option' => $validated['payment_option'],
                'deposit_amount' => $deposit,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'reference' => $reference,
                'source' => 'admin_manual',
            ]);

            $booking->services()->attach($services->mapWithKeys(fn (Service $service) => [
                $service->id => [
                    'price_at_booking' => $service->price,
                    'duration_minutes_at_booking' => max(15, (int) ($service->duration_minutes ?: 60)),
                ],
            ])->all());

            return [$booking->load(['services:id,name,price,duration_minutes', 'provider:id,name']), $customer];
        });

        $emailRequested = $request->boolean('send_email', true);
        $emailSent = null;
        if ($emailRequested) {
            try {
                $customer->notify(new BookingCreatedManually($booking));
                $emailSent = true;
            } catch (\Throwable $exception) {
                report($exception);
                $emailSent = false;
            }
        }

        $message = $emailRequested
            ? ($emailSent
                ? 'Booking created successfully. Confirmation email sent to the customer.'
                : 'Booking created successfully, but the confirmation email could not be sent.')
            : 'Booking created successfully.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'provider' => $booking->provider ? [
                    'id' => $booking->provider->id,
                    'name' => $booking->provider->name,
                ] : null,
                'services' => $booking->services->map(fn (Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => (float) $service->price,
                    'duration_minutes' => (int) $service->duration_minutes,
                ])->values(),
                'date' => $booking->date,
                'time' => $booking->time,
                'payment_option' => $booking->payment_option,
                'deposit_amount' => $deposit,
                'total' => $total,
                'status' => $booking->status,
                'email_sent' => $emailSent,
            ],
        ], 201);
    }
}
