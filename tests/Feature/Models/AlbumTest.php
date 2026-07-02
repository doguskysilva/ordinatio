<?php

use App\Models\Album;
use App\Models\Track;

test('album has many tracks', function () {
    $album = Album::factory()
        ->has(Track::factory()->count(3))
        ->create();

    expect($album->tracks)->toHaveCount(3)
        ->and($album->tracks->first())->toBeInstanceOf(Track::class)
        ->and($album->tracks->first()->album_id)->toBe($album->id);
});

test('album factory creates with valid attributes', function () {
    $album = Album::factory()->create();

    expect($album)->toBeInstanceOf(Album::class)
        ->and($album->artist)->toBeString()->not->toBeEmpty()
        ->and($album->title)->toBeString()->not->toBeEmpty()
        ->and($album->folder_path)->toBeString()->not->toBeEmpty();
});
