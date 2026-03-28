<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxiMetric extends Model
{
    protected $fillable = [
        'flag',
        'per_minute',
        'per_km',
    ];
}
