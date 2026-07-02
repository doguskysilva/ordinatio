<?php

use App\Models\Album;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\File;

test('card index requires authentication', function () {
    $response = $this->get('/card');

    $response->assertRedirect('/login');
});

test('authenticated user can view card state', function () {
    $user = User::factory()->create();

    // Create test SD card structure
    File::makeDirectory('/tmp/sdcard/Albums/Michael Jackson - Thriller', 0755, true, true);
    File::put('/tmp/sdcard/Albums/Michael Jackson - Thriller/test.flac', 'test');

    $response = $this->actingAs($user)->get('/card');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Card/Index')
            ->has('card')
            ->has('diff')
        );

    // Cleanup
    File::deleteDirectory('/tmp/sdcard');
});

test('card page shows albums on card', function () {
    $user = User::factory()->create();

    // Create test SD card structure with multiple albums
    File::makeDirectory('/tmp/sdcard/Albums/Artist 1 - Album 1', 0755, true, true);
    File::makeDirectory('/tmp/sdcard/Albums/Artist 2 - Album 2', 0755, true, true);
    File::put('/tmp/sdcard/Albums/Artist 1 - Album 1/track1.flac', 'test');
    File::put('/tmp/sdcard/Albums/Artist 2 - Album 2/track2.flac', 'test');

    $response = $this->actingAs($user)->get('/card');

    $response->assertInertia(fn ($page) => $page
        ->has('card.albums', 2)
    );

    // Cleanup
    File::deleteDirectory('/tmp/sdcard');
});

test('card page shows playlists on card', function () {
    $user = User::factory()->create();

    // Create test SD card structure with playlists
    File::makeDirectory('/tmp/sdcard/Playlists/My Playlist', 0755, true, true);
    File::put('/tmp/sdcard/Playlists/My Playlist/track1.flac', 'test');

    $response = $this->actingAs($user)->get('/card');

    $response->assertInertia(fn ($page) => $page
        ->has('card.playlists', 1)
    );

    // Cleanup
    File::deleteDirectory('/tmp/sdcard');
});

test('card page shows missing albums', function () {
    $user = User::factory()->create();

    // Create album in database
    $album = Album::factory()->create(['artist' => 'Test Artist', 'title' => 'Test Album']);
    Track::factory()->for($album)->create();

    // SD card is empty
    File::makeDirectory('/tmp/sdcard', 0755, true, true);

    $response = $this->actingAs($user)->get('/card');

    $response->assertInertia(fn ($page) => $page
        ->has('diff.missing_albums')
    );

    // Cleanup
    File::deleteDirectory('/tmp/sdcard');
});
