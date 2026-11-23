<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use Notifiable;

    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function recalls()
    {
        return $this->hasMany(CustomerRecall::class);
    }

    public function loyaltyCard()
    {
        return $this->hasOne(LoyaltyCard::class);
    }

    public function rewards()
    {
        return $this->hasMany(CustomerReward::class)->orderByDesc('id');
    }

    public function user()
{
    return $this->hasOne(\App\Models\User::class, 'customer_id', 'id');
}

}
