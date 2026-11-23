<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReward extends Model
{
    protected $fillable = [
        'customer_id',
        'loyalty_reward_id',
        'voucher_code',
        'status',
        'unlocked_at',
        'redeemed_at',
        'redeemed_by',
        'redeemed_at_booking_id',
        'notes',
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(LoyaltyReward::class, 'loyalty_reward_id');
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function redeemedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'redeemed_at_booking_id');
    }
}
