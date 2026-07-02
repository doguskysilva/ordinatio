<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\Track;
use App\Services\LibraryScanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScanLibrary implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $libraryPath = '/var/music')
    {
    }

    public function handle(LibraryScanner $scanner): void
    {
        $albums = $scanner->scan($this->libraryPath);

        foreach ($albums as $albumData) {
            $album = Album::updateOrCreate(
                ['artist' => $albumData['artist'], 'title' => $albumData['title']],
                [
                    'year' => $albumData['year'],
                    'cover_path' => $albumData['cover_path'],
                    'folder_path' => $albumData['folder_path'],
                ]
            );

            $existingTrackIds = $album->tracks->pluck('file_path')->toArray();
            $newTrackPaths = array_column($albumData['tracks'], 'file_path');
            $tracksToDelete = array_diff($existingTrackIds, $newTrackPaths);

            if (!empty($tracksToDelete)) {
                Track::where('album_id', $album->id)
                    ->whereIn('file_path', $tracksToDelete)
                    ->delete();
            }

            foreach ($albumData['tracks'] as $trackData) {
                Track::updateOrCreate(
                    ['file_path' => $trackData['file_path']],
                    [
                        'album_id' => $album->id,
                        'title' => $trackData['title'],
                        'track_number' => $trackData['track_number'],
                        'duration' => $trackData['duration'],
                        'format' => $trackData['format'],
                        'bitrate' => $trackData['bitrate'],
                    ]
                );
            }
        }
    }
}
