<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class AdminWebhookLogApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['page' => ['nullable', 'integer', 'min:1']]);

        $logs = WebhookLog::query()->latest()->paginate(20);
        $statusCounts = WebhookLog::query()
            ->selectRaw('LOWER(COALESCE(status, \'other\')) as normalized_status, COUNT(*) as aggregate')
            ->groupBy('normalized_status')
            ->pluck('aggregate', 'normalized_status');

        return response()->json([
            'success' => true,
            'data' => $logs->getCollection()->map(fn (WebhookLog $log) => [
                'id' => $log->id,
                'type' => $log->type,
                'status' => $log->status,
                'payload' => $log->payload,
                'created_at' => $log->created_at?->toISOString(),
                'created_ago' => $log->created_at?->diffForHumans(),
            ])->values(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'stats' => [
                'total' => (int) $statusCounts->sum(),
                'success' => (int) ($statusCounts['success'] ?? 0),
                'failed' => (int) ($statusCounts['failed'] ?? 0),
                'other' => (int) $statusCounts->except(['success', 'failed'])->sum(),
            ],
        ]);
    }
}
