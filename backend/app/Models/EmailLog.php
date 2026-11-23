<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'recipient_email',
        'recipient_name',
        'recipient_type',
        'subject',
        'email_type',
        'booking_id',
        'status',
        'sent_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('email_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRecipient($query, $email)
    {
        return $query->where('recipient_email', $email);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
