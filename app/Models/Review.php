<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Review extends Model
{
    protected $fillable = [
        'service_id',
        'customer_id',
        'rating',
        'comment',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function booking()
{
    return $this->belongsTo(Booking::class);
}
public function provider()
{
    return $this->booking->provider ?? null;
}


}
