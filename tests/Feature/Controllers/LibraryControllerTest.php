<?php

use App\Models\Album;
use App\Models\Track;
use App\Models\User;

test('library index requires authentication', function () {
    $response = $this->get('/library');

    $response->assertRedirect('/login');
});

test('authenticated user can view library index', function () {
    $user = User::factory()->create();
    Album::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/library');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Library/Index')
            ->has('albums.data', 5)
        );
});

test('library index displays albums with track count', function () {
    $user = User::factory()->create();
    $album = Album::factory()
        ->has(Track::factory()->count(3))
        ->create();

    $response = $this->actingAs($user)->get('/library');

    $response->assertInertia(fn ($page) => $page
        ->has('albums.data', 1)
    );
});

test('library index paginates albums', function () {
    $user = User::factory()->create();
    Album::factory()->count(25)->create();

    $response = $this->actingAs($user)->get('/library');

    $response->assertInertia(fn ($page) => $page
        ->has('albums.data', 20)
    );
});

test('album show requires authentication', function () {
    $album = Album::factory()->create();

    $response = $this->get("/library/{$album->id}");

    $response->assertRedirect('/login');
});

test('authenticated user can view album details', function () {
    $user = User::factory()->create();
    $album = Album::factory()
        ->has(Track::factory()->count(5))
        ->create();

    $response = $this->actingAs($user)->get("/library/{$album->id}");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Library/Show')
            ->has('album')
        );
});

test('album show loads tracks in order', function () {
    $user = User::factory()->create();
    $album = Album::factory()->create();

    for ($i = 1; $i <= 3; $i++) {
        Track::factory()
            ->for($album)
            ->create(['track_number' => $i]);
    }

    $response = $this->actingAs($user)->get("/library/{$album->id}");

    $response->assertInertia(fn ($page) => $page
        ->has('album.tracks', 3)
    );
});

test('scan library requires authentication', function () {
    $response = $this->post('/library/scan');

    $response->assertRedirect('/login');
});

test('authenticated user can trigger library scan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/library/scan');

    $response->assertRedirect()
        ->assertSessionHas('message');
});
