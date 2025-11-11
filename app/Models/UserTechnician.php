<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserTechnician extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'status_id',
        'expertise',
        'year_experience',
        'certificate_prc_no',
        'professional_license',
        'valid_id_type',
        'valid_id_number',
        'front_valid_id_picture',
        'back_valid_id_picture',
        'cv_attachment',
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
