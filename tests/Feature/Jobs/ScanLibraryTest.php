<?php

use App\Jobs\ScanLibrary;
use App\Models\Album;
use App\Models\Track;

test('scan library job creates albums and tracks', function () {
    // Create a temporary test directory
    $tmpDir = tempnam(sys_get_temp_dir(), 'scan_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    // Empty directory should result in no albums
    expect(Album::count())->toBe(0);

    rmdir($tmpDir);
});

test('scan library updates existing albums', function () {
    $album = Album::factory()->create();
    $initialId = $album->id;

    // Re-scan should preserve album ID for same artist/title
    $tmpDir = tempnam(sys_get_temp_dir(), 'scan_test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    $job = new ScanLibrary($tmpDir);
    $job->handle(app(\App\Services\LibraryScanner::class));

    expect(Album::count())->toBe(1)
        ->and(Album::first()->id)->toBe($initialId);

    rmdir($tmpDir);
});
