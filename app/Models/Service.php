<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
    'name',
    'description',
    'price',
    'duration_minutes',
    'image',
    'provider_id',
    'approved',
    'category_id',
];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }
    public function tags()
    {
        return $this->hasMany(\App\Models\ServiceTag::class);
    }
    public function getStatusAttribute()
    {
        return is_null($this->approved)
            ? 'pending'
            : ($this->approved ? 'approved' : 'rejected');
    }
public function category()
{
    return $this->belongsTo(Category::class);
}


}
