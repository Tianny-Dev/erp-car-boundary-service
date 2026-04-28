<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletionRequest extends Model
{
    protected $fillable = [
        'email',
        'user_id',
        'reason',
        'status',
        'processed_at',
    ];
}
