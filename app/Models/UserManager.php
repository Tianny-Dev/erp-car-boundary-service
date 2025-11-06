<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserManager extends Model
{
    use HasFactory;

    // protected $fillable = [];

    // relationship to user, one to one
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // relationship to franchises, one to many
    public function franchises(): HasMany
    {
        return $this->hasMany(Franchise::class, 'manager_id');
    }

    // relationship to branches, one to many
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'manager_id');
    }
}
