<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'provider_id',
        'date',
        'time',
        'payment_option',
        'status',
        'notes',
        'deposit_amount',
        'reference',
    ];

    protected $casts = [
        'is_fully_paid' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_service');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payments()
{
    return $this->hasMany(\App\Models\Payment::class);
}


    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function review()
    {
        return $this->hasOne(\App\Models\Review::class);
    }
}

