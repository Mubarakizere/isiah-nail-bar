<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    protected $fillable = ['name', 'fee', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'fee' => 'decimal:2',
    ];
}
