<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        //
    ];

    // relations to maintenances, one to many
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
