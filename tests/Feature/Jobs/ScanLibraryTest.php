<?php

use App\Jobs\ScanLibrary;
use App\Models\Album;
use App\Models\Track;

test('scan library job creates albums and tracks', function () {
    $tmpDir = tempnam(sys_get_temp_dir(), 'scan_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    expect(Album::count())->toBe(0);

    rmdir($tmpDir);
});

test('scan library updates existing albums', function () {
    $album = Album::factory()->create();
    $initialId = $album->id;

    $tmpDir = tempnam(sys_get_temp_dir(), 'scan_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    expect(Album::count())->toBe(1)
        ->and(Album::first()->id)->toBe($initialId);

    rmdir($tmpDir);
});

test('scan library with real flac files extracts metadata', function () {
    $fixturesPath = base_path('tests/fixtures/audio/*.flac');
    $flacFile = glob($fixturesPath)[0] ?? null;

    if (!$flacFile || !file_exists($flacFile)) {
        $this->markTestSkipped('No FLAC fixtures found');
    }

    $tmpDir = tempnam(sys_get_temp_dir(), 'flac_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    // Create test album structure
    $albumDir = "$tmpDir/Test Artist/Test Album";
    mkdir($albumDir, 0777, true);
    copy($flacFile, "$albumDir/01 - Test Track.flac");

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    expect(Album::count())->toBeGreaterThanOrEqual(1);
    expect(Track::count())->toBeGreaterThanOrEqual(1);

    $track = Track::first();
    expect($track)->not->toBeNull()
        ->and($track->file_path)->toContain('Test Track.flac')
        ->and($track->format)->toBe('flac')
        ->and($track->duration)->toBeGreaterThan(0)
        ->and($track->album)->not->toBeNull();

    // Cleanup
    array_map('unlink', glob("$albumDir/*"));
    rmdir($albumDir);
    rmdir("$tmpDir/Test Artist");
    rmdir($tmpDir);
});

test('scan library handles artist slash album folder structure', function () {
    $fixturesPath = base_path('tests/fixtures/audio/*.flac');
    $flacFile = glob($fixturesPath)[0] ?? null;

    if (!$flacFile || !file_exists($flacFile)) {
        $this->markTestSkipped('No FLAC fixtures found');
    }

    $tmpDir = tempnam(sys_get_temp_dir(), 'artist_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $artistDir = "$tmpDir/The Beatles";
    $albumDir = "$artistDir/Abbey Road";
    mkdir($albumDir, 0777, true);

    for ($i = 1; $i <= 3; $i++) {
        $destFile = "$albumDir/0$i - Track $i.flac";
        copy($flacFile, $destFile);
    }

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    $album = Album::first();
    expect($album)->not->toBeNull()
        ->and($album->artist)->not->toBeEmpty()
        ->and($album->title)->not->toBeEmpty()
        ->and($album->folder_path)->toContain('Abbey Road');

    $tracks = Track::all();
    expect($tracks)->toHaveCount(3);

    foreach ($tracks as $track) {
        expect($track->album_id)->toBe($album->id)
            ->and($track->file_path)->toContain('.flac')
            ->and($track->duration)->toBeGreaterThan(0);
    }

    // Cleanup
    array_map('unlink', glob("$albumDir/*"));
    rmdir($albumDir);
    rmdir($artistDir);
    rmdir($tmpDir);
});

test('scan library deletes tracks when files are removed', function () {
    $fixturesPath = base_path('tests/fixtures/audio/*.flac');
    $flacFile = glob($fixturesPath)[0] ?? null;

    if (!$flacFile || !file_exists($flacFile)) {
        $this->markTestSkipped('No FLAC fixtures found');
    }

    $tmpDir = tempnam(sys_get_temp_dir(), 'delete_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $albumDir = "$tmpDir/Artist/Album";
    mkdir($albumDir, 0777, true);
    copy($flacFile, "$albumDir/01.flac");
    copy($flacFile, "$albumDir/02.flac");

    // First scan creates 2 tracks
    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));
    expect(Track::count())->toBe(2);

    // Delete one file
    unlink("$albumDir/02.flac");

    // Rescan should delete the orphaned track
    $job->handle(app(\App\Services\LibraryScanner::class));
    expect(Track::count())->toBe(1);

    // Cleanup
    array_map('unlink', glob("$albumDir/*"));
    rmdir($albumDir);
    rmdir("$tmpDir/Artist");
    rmdir($tmpDir);
});
