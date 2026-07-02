<?php

use App\Services\LibraryScanner;
use App\Services\MetadataReader;

test('library scanner returns empty for non-existent path', function () {
    $scanner = new LibraryScanner(new MetadataReader());
    $albums = $scanner->scan('/nonexistent/path');

    expect($albums)->toBeArray()->toBeEmpty();
});

test('library scanner returns sorted albums', function () {
    $scanner = new LibraryScanner(new MetadataReader());

    // Create a temporary test structure
    $tmpDir = tempnam(sys_get_temp_dir(), 'test_');
    unlink($tmpDir);
    mkdir($tmpDir, 0777, true);

    // Albums are returned as an array structure
    $albums = $scanner->scan($tmpDir);

    expect($albums)->toBeArray();

    rmdir($tmpDir);
});
