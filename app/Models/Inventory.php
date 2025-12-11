<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_no',
        'name',
        'category',
        'specification',
        'quantity',
        'unit_price',
        'notes'
    ];

    // relations to maintenances, one to many
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
