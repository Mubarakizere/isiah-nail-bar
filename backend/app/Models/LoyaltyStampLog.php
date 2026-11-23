<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyStampLog extends Model
{
    protected $fillable = [
        'loyalty_card_id',
        'booking_id',
        'action',
        'change',
        'reason',
        'awarded_by',
    ];

    protected $casts = [
        'change' => 'integer',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCard::class, 'loyalty_card_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
