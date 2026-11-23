<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Service;
use App\Services\BookingScheduleGuard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminWalkInApiController extends Controller
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

        $validated = $request->validate([
            'customer_id' => $isNewCustomer
                ? ['required', Rule::in(['new'])]
                : ['required', 'integer', 'exists:customers,id'],
            'customer_name' => [$isNewCustomer ? 'required' : 'nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'provider_id' => ['required', 'integer', Rule::exists('providers', 'id')->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'status' => ['required', Rule::in(['in_progress', 'completed'])],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(['cash', 'card', 'momo'])],
            'notes' => ['nullable', 'string', 'max:5000'],
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

        $total = (float) $services->sum('price');
        $paymentAmount = (float) ($validated['payment_amount'] ?? 0);
        if ($paymentAmount > $total) {
            throw ValidationException::withMessages([
                'payment_amount' => 'Payment cannot be greater than the selected services total.',
            ]);
        }
        if ($paymentAmount > 0 && empty($validated['payment_method'])) {
            throw ValidationException::withMessages([
                'payment_method' => 'Choose a payment method when recording a payment.',
            ]);
        }

        $booking = DB::transaction(function () use ($validated, $isNewCustomer, $serviceIds, $paymentAmount, $services) {
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

            $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['time'], config('app.timezone'));
            $now = now(config('app.timezone'));
            if ($start->gt($now)) {
                throw ValidationException::withMessages([
                    'time' => 'A walk-in visit must have already started or finished. Future visits should be recorded as a manual booking.',
                ]);
            }
            if ($validated['status'] === 'in_progress' && $start->toDateString() !== $now->toDateString()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a visit taking place today can be marked in progress.',
                ]);
            }

            $duration = max(15, (int) $services->sum(fn (Service $service) => max(15, (int) ($service->duration_minutes ?: 60))));
            $scheduleGuard->assertNoOverlap(
                $provider,
                $validated['date'],
                $validated['time'],
                $duration,
                null,
                ['pending', 'accepted', 'confirmed', 'in_progress', 'completed']
            );

            $customer = $isNewCustomer
                ? Customer::create([
                    'name' => trim($validated['customer_name']),
                    'email' => filled($validated['customer_email'] ?? null)
                        ? trim($validated['customer_email'])
                        : null,
                    'phone' => filled($validated['customer_phone'] ?? null)
                        ? trim($validated['customer_phone'])
                        : null,
                ])
                : Customer::findOrFail($validated['customer_id']);

            do {
                $reference = 'WALK-' . Str::upper(Str::random(12));
            } while (Booking::where('reference', $reference)->exists());

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $validated['provider_id'],
                'date' => $validated['date'],
                'time' => $validated['time'],
                'payment_option' => 'full',
                'deposit_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
                'source' => 'walk_in',
                'reference' => $reference,
            ]);

            $booking->services()->attach($services->mapWithKeys(fn (Service $service) => [
                $service->id => [
                    'price_at_booking' => $service->price,
                    'duration_minutes_at_booking' => max(15, (int) ($service->duration_minutes ?: 60)),
                ],
            ])->all());

            if ($paymentAmount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $paymentAmount,
                    'method' => $validated['payment_method'],
                    'status' => 'paid',
                    'reference' => 'WALK-' . Str::upper(Str::random(12)),
                    'paid_at' => now(),
                ]);

                if ($paymentAmount >= (float) Service::whereIn('id', $serviceIds)->sum('price')) {
                    $booking->is_fully_paid = true;
                    $booking->save();
                }
            }

            return $booking->load(['customer', 'provider', 'services']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Walk-in visit recorded successfully.',
            'data' => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'source' => $booking->source,
                'status' => $booking->status,
                'customer_name' => $booking->customer?->name,
                'provider_name' => $booking->provider?->name,
                'services' => $booking->services->pluck('name')->values(),
                'duration_minutes' => (int) $booking->services->sum(
                    fn (Service $service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
                ),
                'total' => $total,
            ],
        ], 201);
    }
}
