<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['playlist_id', 'track_id', 'position'])]
class PlaylistTrack extends Model
{
    /** @use HasFactory<\Database\Factories\PlaylistTrackFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
