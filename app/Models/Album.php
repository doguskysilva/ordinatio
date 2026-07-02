<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['artist', 'title', 'year', 'cover_path', 'folder_path'])]
class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
