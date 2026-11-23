<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function provider()
{
    return $this->hasOne(Provider::class);
}
public function customer()
{
    return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
}
public function reviews()
{
    return $this->hasMany(\App\Models\Review::class);
}
public function hasBookedService($serviceId)
{
    return $this->customer?->bookings()
        ->where('service_id', $serviceId)
        ->where('status', 'completed')
        ->exists();
}


}
