<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'reference',
        'amount',
        'phone',
        'method',
        'actual_method_used',
        'status',
        'transaction_id',
        'provider_transaction_id',
        'payment_ref',
        'webhook_payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'webhook_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Relationship with booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
