<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    // protected $fillable = [];

    // relationship to drivers, one to many
    public function drivers(): HasMany
    {
        return $this->hasMany(UserDriver::class, 'status_id');
    }

    // relationship to owners, one to many
    public function owners(): HasMany
    {
        return $this->hasMany(UserOwner::class, 'status_id');
    }

    // relationship to passengers, one to many
    public function passengers(): HasMany
    {
        return $this->hasMany(UserPassenger::class, 'status_id');
    }

    // relationship to managers, one to many
    public function managers(): HasMany
    {
        return $this->hasMany(UserManager::class, 'status_id');
    }

    // relationship to technicians, one to many
    public function technicians(): HasMany
    {
        return $this->hasMany(UserTechnician::class, 'status_id');
    }

    // relationship to franchises, one to many
    public function franchises(): HasMany
    {
        return $this->hasMany(Franchise::class, 'status_id');
    }

    // relationship to branches, one to many
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'status_id');
    }

}
