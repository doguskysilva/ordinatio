<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\Playlist;
use App\Models\SyncLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;

class SyncToCard implements ShouldQueue
{
    use Queueable;

    private string $cardPath = '/var/sdcard';
    private array $summary = ['added' => 0, 'removed' => 0, 'errors' => 0];

    public function handle(): void
    {
        $syncLog = SyncLog::create([
            'status' => 'running',
            'started_at' => now(),
            'summary' => [],
        ]);

        try {
            File::ensureDirectoryExists("{$this->cardPath}/Albums");
            File::ensureDirectoryExists("{$this->cardPath}/Playlists");

            $this->syncAlbums();
            $this->syncPlaylists();
            $this->cleanupOldFiles();

            $syncLog->update([
                'status' => 'completed',
                'finished_at' => now(),
                'summary' => $this->summary,
            ]);
        } catch (\Exception $e) {
            $this->summary['errors']++;
            $syncLog->update([
                'status' => 'failed',
                'finished_at' => now(),
                'summary' => array_merge($this->summary, ['error' => $e->getMessage()]),
            ]);

            throw $e;
        }
    }

    private function syncAlbums(): void
    {
        $albums = Album::with('tracks')->get();
        $albumsPath = "{$this->cardPath}/Albums";

        foreach ($albums as $album) {
            $folderName = "{$album->artist} - {$album->title}";
            $albumPath = "{$albumsPath}/{$folderName}";

            File::ensureDirectoryExists($albumPath);

            foreach ($album->tracks as $track) {
                if (File::exists($track->file_path)) {
                    $filename = basename($track->file_path);
                    $destination = "{$albumPath}/{$filename}";

                    try {
                        File::copy($track->file_path, $destination);
                        $this->summary['added']++;
                    } catch (\Exception $e) {
                        $this->summary['errors']++;
                    }
                }
            }
        }
    }

    private function syncPlaylists(): void
    {
        $playlists = Playlist::with('tracks')->get();
        $playlistsPath = "{$this->cardPath}/Playlists";

        foreach ($playlists as $playlist) {
            $playlistPath = "{$playlistsPath}/{$playlist->name}";
            File::ensureDirectoryExists($playlistPath);

            $position = 1;
            foreach ($playlist->tracks as $track) {
                if (File::exists($track->file_path)) {
                    $ext = pathinfo($track->file_path, PATHINFO_EXTENSION);
                    $filename = str_pad($position, 3, '0', STR_PAD_LEFT) . " - {$track->title}.{$ext}";
                    $destination = "{$playlistPath}/{$filename}";

                    try {
                        File::copy($track->file_path, $destination);
                        $this->summary['added']++;
                        $position++;
                    } catch (\Exception $e) {
                        $this->summary['errors']++;
                    }
                }
            }
        }
    }

    private function cleanupOldFiles(): void
    {
        $albumsPath = "{$this->cardPath}/Albums";
        $playlistsPath = "{$this->cardPath}/Playlists";

        if (File::isDirectory($albumsPath)) {
            $dirs = File::directories($albumsPath);
            foreach ($dirs as $dir) {
                $folderName = basename($dir);
                $exists = Album::where('artist', 'LIKE', "%{$folderName}%")->exists();

                if (!$exists) {
                    File::deleteDirectory($dir);
                    $this->summary['removed']++;
                }
            }
        }

        if (File::isDirectory($playlistsPath)) {
            $dirs = File::directories($playlistsPath);
            foreach ($dirs as $dir) {
                $playlistName = basename($dir);
                $exists = Playlist::where('name', $playlistName)->exists();

                if (!$exists) {
                    File::deleteDirectory($dir);
                    $this->summary['removed']++;
                }
            }
        }
    }
}
