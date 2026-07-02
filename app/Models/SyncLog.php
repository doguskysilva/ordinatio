<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    /** @use HasFactory<\Database\Factories\SyncLogFactory> */
    use HasFactory;

    protected $fillable = ['status', 'started_at', 'finished_at', 'summary'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'summary' => 'array',
    ];
}
