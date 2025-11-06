<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserDriver extends Model
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
}
