<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTag extends Model
{
    protected $fillable = ['service_id', 'tag'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
