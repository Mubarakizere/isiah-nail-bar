<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRecall extends Model
{
    protected $fillable = [
        'customer_id',
        'last_booking_id',
        'status',
        'notes',
        'called_by',
        'called_at',
        'email_reminder_sent_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'email_reminder_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lastBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'last_booking_id');
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }
}
