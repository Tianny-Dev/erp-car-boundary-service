<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory;

    // protected $fillable = [];

    // relationship to manager, one to many
    public function manager(): BelongsTo
    {
        return $this->belongsTo(UserManager::class, 'manager_id');
    }

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // relationship to payment option, one to many
    public function paymentOption(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class, 'payment_option_id');
    }

    // relationship to drivers, many to many (pivot table)
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(UserDriver::class);
    }

    // relationship to technicians, many to many (pivot table)
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(UserTechnician::class);
    }
}
