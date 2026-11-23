<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\CustomerReward;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyStampLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LoyaltyService
{
    public const STAMPS_PER_CARD = 8;

    /**
     * Retrieve or initialize a digital loyalty card for a customer.
     */
    public function getOrCreateCard(Customer $customer): LoyaltyCard
    {
        return LoyaltyCard::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'current_stamps'  => 0,
                'lifetime_stamps' => 0,
                'card_cycle'      => 1,
            ]
        );
    }

    /**
     * Award one or more stamps to a customer's loyalty card upon completed booking or event.
     */
    public function awardStamp(
        Customer $customer,
        ?Booking $booking = null,
        string $reason = 'Completed appointment',
        ?User $staff = null,
        int $stamps = 1
    ): array {
        return DB::transaction(function () use ($customer, $booking, $reason, $staff, $stamps) {
            $card = LoyaltyCard::query()
                ->where('customer_id', $customer->id)
                ->lockForUpdate()
                ->first();

            if (!$card) {
                $card = LoyaltyCard::create([
                    'customer_id'     => $customer->id,
                    'current_stamps'  => 0,
                    'lifetime_stamps' => 0,
                    'card_cycle'      => 1,
                ]);
            }

            // Deduplication guard: avoid duplicate stamps for the exact same completed booking
            if ($booking) {
                $alreadyAwarded = LoyaltyStampLog::query()
                    ->where('loyalty_card_id', $card->id)
                    ->where('booking_id', $booking->id)
                    ->where('action', 'booking_completed')
                    ->exists();

                if ($alreadyAwarded) {
                    return [
                        'card'             => $card,
                        'unlocked_rewards' => [],
                        'already_awarded'  => true,
                    ];
                }
            }

            // If the card was previously completed (at 8/8) and customer earns a new stamp,
            // advance card cycle and reset current stamps to start fresh cycle.
            if ($card->current_stamps >= self::STAMPS_PER_CARD) {
                $card->card_cycle += 1;
                $card->current_stamps = 0;
                $card->completed_at = null;
            }

            $newStamps = $card->current_stamps + $stamps;
            $card->current_stamps = min($newStamps, self::STAMPS_PER_CARD);
            $card->lifetime_stamps += $stamps;

            if ($card->current_stamps >= self::STAMPS_PER_CARD && !$card->completed_at) {
                $card->completed_at = now();
            }

            $card->save();

            // Record audit log
            LoyaltyStampLog::create([
                'loyalty_card_id' => $card->id,
                'booking_id'      => $booking?->id,
                'action'          => $booking ? 'booking_completed' : 'manual_add',
                'change'          => $stamps,
                'reason'          => $reason,
                'awarded_by'      => $staff?->id,
            ]);

            // Check milestone rewards
            $unlocked = $this->checkAndUnlockMilestones($customer, $card);

            return [
                'card'             => $card,
                'unlocked_rewards' => $unlocked,
                'already_awarded'  => false,
            ];
        }, 3);
    }

    /**
     * Manually adjust stamps (+ or -).
     */
    public function adjustStamps(Customer $customer, int $change, string $reason, ?User $staff = null): array
    {
        return DB::transaction(function () use ($customer, $change, $reason, $staff) {
            $card = $this->getOrCreateCard($customer);

            $oldStamps = $card->current_stamps;
            $newStamps = max(0, min($oldStamps + $change, self::STAMPS_PER_CARD));
            $actualChange = $newStamps - $oldStamps;

            $card->current_stamps = $newStamps;
            if ($actualChange > 0) {
                $card->lifetime_stamps += $actualChange;
            }

            if ($card->current_stamps >= self::STAMPS_PER_CARD && !$card->completed_at) {
                $card->completed_at = now();
            } elseif ($card->current_stamps < self::STAMPS_PER_CARD) {
                $card->completed_at = null;
            }

            $card->save();

            LoyaltyStampLog::create([
                'loyalty_card_id' => $card->id,
                'booking_id'      => null,
                'action'          => $change >= 0 ? 'manual_add' : 'manual_deduct',
                'change'          => $actualChange,
                'reason'          => $reason,
                'awarded_by'      => $staff?->id,
            ]);

            $unlocked = $this->checkAndUnlockMilestones($customer, $card);

            return [
                'card'             => $card,
                'unlocked_rewards' => $unlocked,
                'change'           => $actualChange,
            ];
        }, 3);
    }

    /**
     * Redeem a reward voucher on-site at the salon.
     */
    public function redeemVoucher(
        string $voucherCode,
        ?User $staff = null,
        ?Booking $booking = null,
        ?string $notes = null
    ): CustomerReward {
        return DB::transaction(function () use ($voucherCode, $staff, $booking, $notes) {
            $voucher = CustomerReward::query()
                ->with(['reward', 'customer'])
                ->where('voucher_code', strtoupper(trim($voucherCode)))
                ->lockForUpdate()
                ->firstOrFail();

            if ($voucher->status === 'redeemed') {
                throw new RuntimeException("This reward voucher was already redeemed on {$voucher->redeemed_at?->format('M d, Y')}.");
            }

            $voucher->status = 'redeemed';
            $voucher->redeemed_at = now();
            $voucher->redeemed_by = $staff?->id;
            $voucher->redeemed_at_booking_id = $booking?->id;
            if ($notes) {
                $voucher->notes = trim($notes);
            }
            $voucher->save();

            return $voucher;
        }, 3);
    }

    /**
     * Unlock rewards for any milestone achieved in current cycle that hasn't been awarded.
     */
    protected function checkAndUnlockMilestones(Customer $customer, LoyaltyCard $card): array
    {
        $eligibleRewards = LoyaltyReward::query()
            ->where('is_active', true)
            ->where('stamps_required', '<=', $card->current_stamps)
            ->orderBy('stamps_required')
            ->get();

        $unlocked = [];

        foreach ($eligibleRewards as $reward) {
            // Count how many times this milestone reward was earned by this customer
            $timesEarned = CustomerReward::query()
                ->where('customer_id', $customer->id)
                ->where('loyalty_reward_id', $reward->id)
                ->count();

            // A customer can earn each milestone reward once per card cycle
            if ($timesEarned < $card->card_cycle) {
                $prefix = match ($reward->icon) {
                    'wine'   => 'WINE',
                    'crown'  => 'VIP',
                    default  => 'PERK',
                };
                $voucherCode = 'REW-' . $prefix . '-' . strtoupper(Str::random(5));

                $voucher = CustomerReward::create([
                    'customer_id'       => $customer->id,
                    'loyalty_reward_id' => $reward->id,
                    'voucher_code'      => $voucherCode,
                    'status'            => 'unclaimed',
                    'unlocked_at'       => now(),
                ]);

                $unlocked[] = $voucher->load('reward');
            }
        }

        return $unlocked;
    }
}
