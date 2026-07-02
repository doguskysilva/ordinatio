<?php

use App\Models\Album;
use App\Models\SyncLog;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\File;

test('sync store requires authentication', function () {
    $response = $this->post('/sync');

    $response->assertRedirect('/login');
});

test('authenticated user can trigger sync', function () {
    $user = User::factory()->create();
    Album::factory()->create();

    $response = $this->actingAs($user)->post('/sync');

    $response->assertRedirect('/card');
    expect(SyncLog::count())->toBeGreaterThan(0);
});

test('sync creates albums directory structure on card', function () {
    // Cleanup
    File::deleteDirectory('/tmp/sdcard', true);
    File::ensureDirectoryExists('/tmp/sdcard');

    $user = User::factory()->create();
    $album = Album::factory()
        ->has(Track::factory()->count(2))
        ->create();

    $this->actingAs($user)->post('/sync');

    // Wait for queue to process
    sleep(1);

    $albumPath = "/tmp/sdcard/Albums/{$album->artist} - {$album->title}";
    expect(File::isDirectory($albumPath))->toBeTrue();

    File::deleteDirectory('/tmp/sdcard', true);
});

test('sync shows sync log details', function () {
    $user = User::factory()->create();
    $syncLog = SyncLog::create([
        'status' => 'completed',
        'started_at' => now(),
        'finished_at' => now()->addMinutes(5),
        'summary' => ['added' => 5, 'removed' => 0, 'errors' => 0],
    ]);

    $response = $this->actingAs($user)->get("/sync/{$syncLog->id}");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Sync/Show')
            ->has('syncLog')
        );
});
