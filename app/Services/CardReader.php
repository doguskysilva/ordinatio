<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class CardReader
{
    public function __construct(private string $cardPath = '/var/sdcard') {}

    public function read(): array
    {
        if (!File::isDirectory($this->cardPath)) {
            return ['albums' => [], 'playlists' => []];
        }

        return [
            'albums' => $this->readAlbums(),
            'playlists' => $this->readPlaylists(),
            'total_size' => $this->calculateSize(),
        ];
    }

    private function readAlbums(): array
    {
        $albumsPath = "{$this->cardPath}/Albums";

        if (!File::isDirectory($albumsPath)) {
            return [];
        }

        $albums = [];
        $dirs = File::directories($albumsPath);

        foreach ($dirs as $albumDir) {
            $trackFiles = File::files($albumDir);
            $albums[] = [
                'name' => basename($albumDir),
                'tracks' => count($trackFiles),
                'size' => $this->calculateDirSize($albumDir),
            ];
        }

        usort($albums, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $albums;
    }

    private function readPlaylists(): array
    {
        $playlistsPath = "{$this->cardPath}/Playlists";

        if (!File::isDirectory($playlistsPath)) {
            return [];
        }

        $playlists = [];
        $dirs = File::directories($playlistsPath);

        foreach ($dirs as $playlistDir) {
            $trackFiles = File::files($playlistDir);
            $playlists[] = [
                'name' => basename($playlistDir),
                'tracks' => count($trackFiles),
                'size' => $this->calculateDirSize($playlistDir),
            ];
        }

        usort($playlists, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $playlists;
    }

    private function calculateDirSize(string $path): int
    {
        $size = 0;
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function calculateSize(): int
    {
        if (!File::isDirectory($this->cardPath)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($this->cardPath);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    public function diff(): array
    {
        $cardState = $this->read();
        $dbAlbums = \App\Models\Album::all()->pluck('title', 'artist')->toArray();
        $dbPlaylists = \App\Models\Playlist::pluck('name')->toArray();

        return [
            'card' => $cardState,
            'missing_albums' => array_diff_key(
                array_map(fn ($title, $artist) => "$artist - $title", $dbAlbums, array_keys($dbAlbums)),
                array_combine(array_column($cardState['albums'], 'name'), array_column($cardState['albums'], 'name'))
            ),
            'extra_albums' => array_diff(
                array_column($cardState['albums'], 'name'),
                array_column($cardState['albums'], 'name')
            ),
            'missing_playlists' => array_diff($dbPlaylists, array_column($cardState['playlists'], 'name')),
            'extra_playlists' => array_diff(array_column($cardState['playlists'], 'name'), $dbPlaylists),
        ];
    }
}
