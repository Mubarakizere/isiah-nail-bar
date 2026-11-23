<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyReward extends Model
{
    protected $fillable = [
        'stamps_required',
        'title',
        'description',
        'reward_type',
        'discount_percentage',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'stamps_required'     => 'integer',
        'discount_percentage' => 'integer',
        'is_active'           => 'boolean',
        'sort_order'          => 'integer',
    ];

    public function customerRewards(): HasMany
    {
        return $this->hasMany(CustomerReward::class);
    }
}
