<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    use HasUuids;

    protected $fillable = [
        'content',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
