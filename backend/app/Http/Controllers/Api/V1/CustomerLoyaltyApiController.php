<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerLoyaltyApiController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService)
    {
    }

    /**
     * Get authenticated customer's stamp card, reward tiers, earned vouchers, and logs.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $user?->customer;

        if (!$customer && $user?->customer_id) {
            $customer = \App\Models\Customer::find($user->customer_id);
        }

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found.',
            ], 404);
        }

        $card = $this->loyaltyService->getOrCreateCard($customer);

        $rewardsTiers = LoyaltyReward::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('stamps_required')
            ->get();

        $vouchers = $customer->rewards()
            ->with('reward')
            ->orderByDesc('id')
            ->get();

        $logs = $card->logs()
            ->limit(15)
            ->get(['id', 'action', 'change', 'reason', 'created_at']);

        return response()->json([
            'success' => true,
            'data'    => [
                'card' => [
                    'id'               => $card->id,
                    'customer_id'      => $customer->id,
                    'customer_name'    => $customer->name,
                    'current_stamps'   => $card->current_stamps,
                    'max_stamps'       => LoyaltyService::STAMPS_PER_CARD,
                    'lifetime_stamps'  => $card->lifetime_stamps,
                    'card_cycle'       => $card->card_cycle,
                    'completed_at'     => $card->completed_at?->toIso8601String(),
                ],
                'tiers'    => $rewardsTiers,
                'vouchers' => $vouchers,
                'logs'     => $logs,
            ],
        ]);
    }
}
