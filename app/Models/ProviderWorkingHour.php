<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderWorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
    'provider_id',
    'day_of_week',
    'start_time',
    'end_time',
    'break_start',
    'break_end',
    'is_day_off',
    'is_holiday',
];


    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
