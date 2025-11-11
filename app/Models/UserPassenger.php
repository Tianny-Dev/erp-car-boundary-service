<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'status_id',
        'payment_option_id',
        'preferred_language',
        'accessibility_option',
        'birth_date',
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

    // relationship to ratings, one to many
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
