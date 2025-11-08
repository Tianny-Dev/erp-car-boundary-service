<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'status_id',
        'payment_option_id',
        'license_number',
        'is_verified',
        'license_expiry',
        'front_license_picture',
        'back_license_picture',
        'nbi_clearance',
        'selfie_picture',
        'hire_date',
    ];


    // relationship to user, one to one
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // relationship to payment option, one to many
    public function paymentOption(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class);
    }

    // relationship to franchises, many to many (pivot table)
    public function franchises(): BelongsToMany
    {
        return $this->belongsToMany(Franchise::class);
    }

    // relationship to branches, many to many (pivot table)
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    // relationship to vehicles, one to many
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // relationship to routes, one to many
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    // relationship to violations, one to many
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    // relationship to ratings, one to many
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
