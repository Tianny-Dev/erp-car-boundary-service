<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'franchise_id',
        'branch_id',
        'driver_id',
        'plate_number',
        'vin',
        'brand',
        'model',
        'year',
        'color',
    ];

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // relationship to driver, one to many
    public function driver(): BelongsTo
    {
        return $this->belongsTo(UserDriver::class, 'driver_id');
    }

    // relationship to franchise, one to many
    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    // relationship to branch, one to many
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // relationship to routes, one to many
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    // relationship to maintenances, one to many
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
