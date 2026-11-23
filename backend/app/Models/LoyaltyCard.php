<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyCard extends Model
{
    protected $fillable = [
        'customer_id',
        'current_stamps',
        'lifetime_stamps',
        'card_cycle',
        'completed_at',
    ];

    protected $casts = [
        'current_stamps'  => 'integer',
        'lifetime_stamps' => 'integer',
        'card_cycle'      => 'integer',
        'completed_at'    => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LoyaltyStampLog::class)->orderByDesc('id');
    }
}
