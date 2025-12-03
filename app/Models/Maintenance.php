<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'status_id',
        'technician_id',
        'franchise_id',
        'branch_id',
        'expense_id',
        'maintenance_type',
        'description',
        'maintenance_date',
        'next_maintenance_date',
    ];

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // relationship to expense, one to many
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    // relationship to vehicle, one to many
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
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
}
