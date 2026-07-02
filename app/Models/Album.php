<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    protected $fillable = ['artist', 'title', 'year', 'cover_path', 'folder_path'];

    protected $casts = [
        'year' => 'integer',
    ];

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
