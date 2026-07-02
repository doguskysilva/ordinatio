<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistTrack extends Model
{
    /** @use HasFactory<\Database\Factories\PlaylistTrackFactory> */
    use HasFactory;

    protected $fillable = ['playlist_id', 'track_id', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];
}
