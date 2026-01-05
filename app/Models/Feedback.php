<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_type_id',
        'avatar',
        'rating',
        'description',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function getStarsAttribute()
    {
        return (float) $this->rating;
    }

    public function scopeTopRated($query)
    {
        return $query->where('rating', '>=', 4.5);
    }
}
