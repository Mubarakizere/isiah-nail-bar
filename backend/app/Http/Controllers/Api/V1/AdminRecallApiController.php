<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CustomerRecall;
use App\Models\EmailLog;
use App\Mail\RebookingReminderMail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminRecallApiController extends Controller
{
    /**
     * Retrieve list of customers due for re-booking (last visit >= 21 days ago)
     * with metrics and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $bracket = $request->query('bracket', 'all'); // 3_weeks, 4_weeks, overdue, all
        $statusFilter = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $providerId = $request->query('provider_id');
        $today = now(config('app.timezone'))->startOfDay();

        // 1. Identify customers who have an upcoming or today's appointment (active/booked)
        $customerIdsWithUpcoming = Booking::query()
            ->whereDate('date', '>=', $today->toDateString())
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->distinct()
            ->pluck('customer_id')
            ->toArray();

        // 2. Query customers with completed/accepted past bookings
        // Using a subquery for the most recent completed booking date for each customer
        $latestBookingSubquery = Booking::query()
            ->select('customer_id', DB::raw('MAX(date) as max_date'))
            ->whereIn('status', ['accepted', 'completed'])
            ->whereDate('date', '<', $today->toDateString())
            ->whereNotIn('customer_id', $customerIdsWithUpcoming)
            ->groupBy('customer_id');

        $eligibleQuery = DB::table('customers')
            ->joinSub($latestBookingSubquery, 'latest_bookings', function ($join) {
                $join->on('customers.id', '=', 'latest_bookings.customer_id');
            })
            ->whereDate('latest_bookings.max_date', '<=', $today->copy()->subDays(21)->toDateString());

        // Global metrics before pagination
        $allEligible = (clone $eligibleQuery)->get(['customers.id', 'latest_bookings.max_date']);

        $due3wCount = 0;
        $overdueCount = 0;
        foreach ($allEligible as $record) {
            $days = Carbon::parse($record->max_date)->diffInDays($today);
            if ($days >= 21 && $days <= 28) {
                $due3wCount++;
            } elseif ($days > 28) {
                $overdueCount++;
            }
        }

        $contactedTodayCount = CustomerRecall::query()
            ->whereDate('called_at', $today->toDateString())
            ->count();

        $bookedCount = CustomerRecall::query()
            ->where('status', 'booked')
            ->count();

        // 3. Apply search filter
        if ($search !== '') {
            $eligibleQuery->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.phone', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        // Apply bracket filter on query
        if ($bracket === '3_weeks') {
            $eligibleQuery->whereDate('latest_bookings.max_date', '>=', $today->copy()->subDays(28)->toDateString())
                          ->whereDate('latest_bookings.max_date', '<=', $today->copy()->subDays(21)->toDateString());
        } elseif ($bracket === '4_weeks') {
            $eligibleQuery->whereDate('latest_bookings.max_date', '>=', $today->copy()->subDays(42)->toDateString())
                          ->whereDate('latest_bookings.max_date', '<', $today->copy()->subDays(28)->toDateString());
        } elseif ($bracket === 'overdue') {
            $eligibleQuery->whereDate('latest_bookings.max_date', '<', $today->copy()->subDays(42)->toDateString());
        }

        // Execute query to get customer IDs
        $customerIds = $eligibleQuery->pluck('customers.id');

        // 4. Load full Customer models with relationships
        $customers = Customer::query()
            ->with(['user'])
            ->whereIn('id', $customerIds)
            ->get();

        $items = [];
        foreach ($customers as $customer) {
            // Get latest booking
            $lastBooking = Booking::query()
                ->with(['services', 'provider'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['accepted', 'completed'])
                ->whereDate('date', '<', $today->toDateString())
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->first();

            if (!$lastBooking) {
                continue;
            }

            if ($providerId && (int) $lastBooking->provider_id !== (int) $providerId) {
                continue;
            }

            $recall = CustomerRecall::query()
                ->with('caller:id,name')
                ->where('customer_id', $customer->id)
                ->where('last_booking_id', $lastBooking->id)
                ->first();

            $status = $recall?->status ?? 'pending';

            if ($statusFilter !== 'all' && $status !== $statusFilter) {
                continue;
            }

            $lastDate = Carbon::parse($lastBooking->date);
            $daysSinceVisit = $lastDate->diffInDays($today);

            $phone = $customer->phone ?? $customer->user?->phone ?? '';
            $email = $customer->email ?? $customer->user?->email ?? '';
            $services = $lastBooking->services->pluck('name')->values()->all();

            // WhatsApp link helper (Rwandan standard format)
            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (str_starts_with($cleanPhone, '07')) {
                $waPhone = '250' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '250')) {
                $waPhone = $cleanPhone;
            } else {
                $waPhone = '250' . ltrim($cleanPhone, '0');
            }

            $customerFirstName = explode(' ', trim($customer->name))[0] ?? 'there';
            $providerName = $lastBooking->provider?->name ?? 'our team';
            $waMessage = urlencode("Hello {$customerFirstName}! This is Isaiah Nail Bar. It has been 3 weeks since your nail session with {$providerName}. We'd love to welcome you back for your refill or refresh. Would you like us to reserve a slot for you this week?");
            $waUrl = !empty($cleanPhone) ? "https://wa.me/{$waPhone}?text={$waMessage}" : null;

            $items[] = [
                'customer_id'             => $customer->id,
                'customer_name'           => $customer->name,
                'customer_phone'          => $phone,
                'customer_email'          => $email,
                'whatsapp_url'            => $waUrl,
                'last_booking_id'         => $lastBooking->id,
                'last_booking_date'       => $lastBooking->date,
                'days_since_visit'        => $daysSinceVisit,
                'last_services'           => $services,
                'last_provider_id'        => $lastBooking->provider_id,
                'last_provider_name'      => $lastBooking->provider?->name ?? 'Salon Team',
                'recall_status'           => $status,
                'notes'                   => $recall?->notes,
                'called_at'               => $recall?->called_at?->toIso8601String(),
                'called_by_name'          => $recall?->caller?->name,
                'email_reminder_sent_at'  => $recall?->email_reminder_sent_at?->toIso8601String(),
            ];
        }

        // Sort by most days since visit descending
        usort($items, fn ($a, $b) => $b['days_since_visit'] <=> $a['days_since_visit']);

        return response()->json([
            'success' => true,
            'metrics' => [
                'due_3w_count'          => $due3wCount,
                'overdue_count'         => $overdueCount,
                'contacted_today_count' => $contactedTodayCount,
                'booked_count'          => $bookedCount,
                'total_eligible'        => count($allEligible),
            ],
            'data' => $items,
        ]);
    }

    /**
     * Record a staff contact attempt (call / WhatsApp) and notes.
     */
    public function logContact(Request $request, int $customerId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,spoke,no_answer,booked,do_not_contact'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Customer::findOrFail($customerId);

        $lastBooking = Booking::query()
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->orderByDesc('date')
            ->first();

        $recall = CustomerRecall::firstOrNew([
            'customer_id'     => $customer->id,
            'last_booking_id' => $lastBooking?->id,
        ]);

        $recall->status = $validated['status'];
        if (isset($validated['notes'])) {
            $recall->notes = trim((string) $validated['notes']);
        }
        $recall->called_by = $request->user()?->id;
        $recall->called_at = now();
        $recall->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact log updated successfully.',
            'recall'  => $recall->load('caller:id,name'),
        ]);
    }

    /**
     * Manually send or resend the 3-week rebooking email reminder.
     */
    public function sendEmail(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::with('user')->findOrFail($customerId);
        $email = $customer->email ?? $customer->user?->email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Customer has no email address on file.',
            ], 422);
        }

        $lastBooking = Booking::query()
            ->with(['services', 'provider'])
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->orderByDesc('date')
            ->first();

        if (!$lastBooking) {
            return response()->json([
                'success' => false,
                'message' => 'No completed appointment found for this customer.',
            ], 404);
        }

        try {
            Mail::to($email)->send(new RebookingReminderMail($lastBooking));

            $recall = CustomerRecall::firstOrNew([
                'customer_id'     => $customer->id,
                'last_booking_id' => $lastBooking->id,
            ]);
            $recall->email_reminder_sent_at = now();
            $recall->save();

            EmailLog::create([
                'recipient_email' => $email,
                'recipient_name'  => $customer->name,
                'recipient_type'  => 'customer',
                'subject'         => "It's Been 3 Weeks! Time for a Fresh Set or Refill? - Isaiah Nail Bar",
                'email_type'      => 'rebooking_reminder_3w',
                'booking_id'      => $lastBooking->id,
                'status'          => 'sent',
                'sent_at'         => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Re-booking reminder email sent to {$email}.",
                'email_reminder_sent_at' => $recall->email_reminder_sent_at->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            Log::error("Manual rebooking email error for customer {$customerId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
