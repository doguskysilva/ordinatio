<?php

use App\Models\Playlist;
use App\Models\Track;

test('playlist has many tracks', function () {
    $playlist = Playlist::factory()->create();
    $tracks = Track::factory()->count(3)->create();

    $playlist->tracks()->attach($tracks->pluck('id'), ['position' => 1]);

    expect($playlist->tracks)->toHaveCount(3)
        ->and($playlist->tracks->first())->toBeInstanceOf(Track::class);
});

test('playlist factory creates with valid attributes', function () {
    $playlist = Playlist::factory()->create();

    expect($playlist)->toBeInstanceOf(Playlist::class)
        ->and($playlist->name)->toBeString()->not->toBeEmpty();
});
