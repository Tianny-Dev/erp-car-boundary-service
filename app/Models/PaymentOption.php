<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentOption extends Model
{
    use HasFactory;

    // protected $fillable = [];

    // relationship to drivers, one to many
    public function drivers(): HasMany
    {
        return $this->hasMany(UserDriver::class);
    }

    // relationship to passengers, one to many
    public function passengers(): HasMany
    {
        return $this->hasMany(UserPassenger::class);
    }

    // relationship to franchises, one to many
    public function franchises(): HasMany
    {
        return $this->hasMany(Franchise::class);
    }

    // relationship to branches, one to many
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
