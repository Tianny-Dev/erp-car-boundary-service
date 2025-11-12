<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoundaryContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'franchise_id',
        'branch_id',
        'name',
        'coverage_area',
        'contract_terms',
        'start_date',
        'end_date',
        'renewal_terms',
        'amount',
        'due_date',
    ];

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
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
