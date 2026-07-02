<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardState extends Model
{
    /** @use HasFactory<\Database\Factories\CardStateFactory> */
    use HasFactory;

    protected $fillable = ['type', 'reference_id', 'folder_name', 'synced_at'];

    protected $casts = [
        'reference_id' => 'integer',
        'synced_at' => 'datetime',
    ];
}
