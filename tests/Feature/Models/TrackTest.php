<?php

use App\Models\Album;
use App\Models\Playlist;
use App\Models\Track;

test('track belongs to album', function () {
    $album = Album::factory()->create();
    $track = Track::factory()->for($album)->create();

    expect($track->album->id)->toBe($album->id);
});

test('track belongs to many playlists', function () {
    $track = Track::factory()->create();
    $playlists = Playlist::factory()->count(2)->create();

    $track->playlists()->attach($playlists, ['position' => 1]);

    $track->refresh();
    expect($track->playlists)->toHaveCount(2)
        ->and($track->playlists->first())->toBeInstanceOf(Playlist::class);
});

test('track factory creates with valid attributes', function () {
    $track = Track::factory()->create();

    expect($track)->toBeInstanceOf(Track::class)
        ->and($track->album_id)->toBe($track->album->id)
        ->and($track->title)->toBeString()->not->toBeEmpty()
        ->and($track->track_number)->toBeInt()
        ->and($track->file_path)->toBeString()->not->toBeEmpty();
});
