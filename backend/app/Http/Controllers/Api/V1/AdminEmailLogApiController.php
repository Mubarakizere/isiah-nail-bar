<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminEmailLogApiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['sent', 'failed', 'pending'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = EmailLog::query()->latest('sent_at')->latest('id');

        if (!empty($filters['type'])) {
            $query->where('email_type', $filters['type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($builder) use ($term) {
                $builder->where('recipient_email', 'like', "%{$term}%")
                    ->orWhere('recipient_name', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('sent_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('sent_at', '<=', $filters['date_to']);
        }

        $logs = $query->paginate(20);
        $statusCounts = EmailLog::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return response()->json([
            'success' => true,
            'data' => $logs->getCollection()->map(fn (EmailLog $log) => $this->serializeLog($log))->values(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'email_types' => EmailLog::query()
                ->whereNotNull('email_type')
                ->distinct()
                ->orderBy('email_type')
                ->pluck('email_type')
                ->values(),
            'stats' => [
                'total' => (int) $statusCounts->sum(),
                'sent' => (int) ($statusCounts['sent'] ?? 0),
                'failed' => (int) ($statusCounts['failed'] ?? 0),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
            ],
        ]);
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load(['booking.customer', 'booking.provider']);

        return response()->json([
            'success' => true,
            'data' => $this->serializeLog($emailLog, true),
        ]);
    }

    private function serializeLog(EmailLog $log, bool $withDetails = false): array
    {
        $data = [
            'id' => $log->id,
            'recipient_email' => $log->recipient_email,
            'recipient_name' => $log->recipient_name,
            'recipient_type' => $log->recipient_type,
            'subject' => $log->subject,
            'email_type' => $log->email_type,
            'booking_id' => $log->booking_id,
            'status' => $log->status,
            'sent_at' => $log->sent_at?->toISOString(),
            'sent_at_human' => $log->sent_at?->diffForHumans(),
        ];

        if ($withDetails) {
            $booking = $log->booking;
            $data['error_message'] = $log->error_message;
            $data['metadata'] = $log->metadata;
            $data['booking'] = $booking ? [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'date' => $booking->date,
                'time' => $booking->time,
                'status' => $booking->status,
                'customer_name' => $booking->customer?->name,
                'provider_name' => $booking->provider?->name,
            ] : null;
        }

        return $data;
    }
}
