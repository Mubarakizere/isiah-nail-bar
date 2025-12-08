<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'bio', 'photo', 'user_id', 'active'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

public function services()
{
    return $this->belongsToMany(Service::class);
}

    public function user()
{
    return $this->belongsTo(User::class);
}
public function approve(Provider $provider)
{
    $provider->update(['active' => true]);
    return redirect()->back()->with('success', 'Provider approved successfully.');
}

public function decline(Provider $provider)
{
    $provider->update(['active' => false]);
    return redirect()->back()->with('success', 'Provider declined successfully.');
}
public function workingHours()
{
    return $this->hasMany(\App\Models\ProviderWorkingHour::class);
}
public function reviews()
{
    return $this->hasManyThrough(\App\Models\Review::class, \App\Models\Booking::class);
}

}
