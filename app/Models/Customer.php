<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function user()
{
    return $this->hasOne(\App\Models\User::class, 'customer_id', 'id');
}

}
