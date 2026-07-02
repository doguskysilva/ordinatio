<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'reference_id', 'folder_name', 'synced_at'])]
class CardState extends Model
{
    /** @use HasFactory<\Database\Factories\CardStateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'synced_at' => 'datetime',
        ];
    }
}
