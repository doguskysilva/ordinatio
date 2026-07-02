<?php

use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;

test('playlists index requires authentication', function () {
    $response = $this->get('/playlists');

    $response->assertRedirect('/login');
});

test('authenticated user can view playlists', function () {
    $user = User::factory()->create();
    Playlist::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/playlists');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Playlists/Index')
            ->has('playlists', 3)
        );
});

test('user can create playlist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/playlists', [
        'name' => 'My Workout Mix',
    ]);

    $response->assertRedirect('/playlists');
    expect(Playlist::where('name', 'My Workout Mix')->exists())->toBeTrue();
});

test('user can view playlist details', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $tracks = Track::factory()->count(5)->create();
    $playlist->tracks()->attach($tracks, ['position' => 1]);

    $response = $this->actingAs($user)->get("/playlists/{$playlist->id}");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Playlists/Show')
            ->has('playlist')
        );
});

test('user can add track to playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $track = Track::factory()->create();

    $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/tracks", [
        'track_id' => $track->id,
    ]);

    $response->assertRedirect();
    expect($playlist->tracks()->where('track_id', $track->id)->exists())->toBeTrue();
});

test('user can remove track from playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $track = Track::factory()->create();
    $playlist->tracks()->attach($track, ['position' => 1]);

    $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}/tracks/{$track->id}");

    $response->assertRedirect();
    expect($playlist->tracks()->where('track_id', $track->id)->exists())->toBeFalse();
});

test('user can delete playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();

    $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}");

    $response->assertRedirect();
    expect(Playlist::find($playlist->id))->toBeNull();
});
