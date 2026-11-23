<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReward;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdminLoyaltyApiController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService)
    {
    }

    /**
     * List all client loyalty cards with search, filtering, and summary statistics.
     */
    public function cards(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $filter = $request->query('filter', 'all'); // all, ready_for_reward, completed, vip

        $query = LoyaltyCard::query()
            ->with(['customer.user', 'customer.rewards.reward'])
            ->join('customers', 'customers.id', '=', 'loyalty_cards.customer_id')
            ->select('loyalty_cards.*');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.phone', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        if ($filter === 'ready_for_reward') {
            $query->where('loyalty_cards.current_stamps', '>=', 3);
        } elseif ($filter === 'completed') {
            $query->where('loyalty_cards.current_stamps', '>=', LoyaltyService::STAMPS_PER_CARD);
        } elseif ($filter === 'vip') {
            $query->where('loyalty_cards.lifetime_stamps', '>=', LoyaltyService::STAMPS_PER_CARD);
        }

        $cards = $query->orderByDesc('loyalty_cards.lifetime_stamps')->paginate(20);

        // Global metrics
        $totalCards = LoyaltyCard::count();
        $unclaimedVouchers = CustomerReward::where('status', 'unclaimed')->count();
        $redeemedVouchers = CustomerReward::where('status', 'redeemed')->count();
        $vipCount = LoyaltyCard::where('lifetime_stamps', '>=', LoyaltyService::STAMPS_PER_CARD)->count();

        return response()->json([
            'success' => true,
            'metrics' => [
                'total_cards'        => $totalCards,
                'unclaimed_vouchers' => $unclaimedVouchers,
                'redeemed_vouchers'  => $redeemedVouchers,
                'vip_clients'        => $vipCount,
                'max_stamps'         => LoyaltyService::STAMPS_PER_CARD,
            ],
            'data'    => $cards->items(),
            'meta'    => [
                'current_page' => $cards->currentPage(),
                'last_page'    => $cards->lastPage(),
                'per_page'     => $cards->perPage(),
                'total'        => $cards->total(),
            ],
        ]);
    }

    /**
     * Manually add or deduct stamps on a client's card.
     */
    public function adjust(Request $request, int $customerId): JsonResponse
    {
        $validated = $request->validate([
            'change' => ['required', 'integer', 'not_in:0', 'min:-8', 'max:8'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $customer = Customer::findOrFail($customerId);

        try {
            $result = $this->loyaltyService->adjustStamps(
                $customer,
                $validated['change'],
                $validated['reason'],
                $request->user()
            );

            return response()->json([
                'success'          => true,
                'message'          => 'Stamps adjusted successfully.',
                'card'             => $result['card']->load('customer'),
                'unlocked_rewards' => $result['unlocked_rewards'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stamps: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * List all loyalty reward milestones.
     */
    public function rewards(): JsonResponse
    {
        $rewards = LoyaltyReward::query()
            ->orderBy('sort_order')
            ->orderBy('stamps_required')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rewards,
        ]);
    }

    /**
     * Update a reward milestone tier.
     */
    public function updateReward(Request $request, int $id): JsonResponse
    {
        $reward = LoyaltyReward::findOrFail($id);

        $validated = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'stamps_required'     => ['required', 'integer', 'min:1', 'max:20'],
            'reward_type'         => ['required', 'in:perk,discount,free_service'],
            'discount_percentage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'icon'                => ['required', 'string', 'max:50'],
            'is_active'           => ['required', 'boolean'],
        ]);

        $reward->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Loyalty reward updated successfully.',
            'data'    => $reward,
        ]);
    }

    /**
     * Redeem a customer's reward voucher on-site.
     */
    public function redeemVoucher(Request $request, string $code): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $voucher = $this->loyaltyService->redeemVoucher(
                $code,
                $request->user(),
                null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Reward '{$voucher->reward?->title}' successfully redeemed for {$voucher->customer?->name}.",
                'voucher' => $voucher,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
