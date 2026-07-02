<?php

namespace App\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class LibraryScanner
{
    private const AUDIO_EXTENSIONS = ['mp3', 'flac', 'wav', 'aac', 'm4a', 'ogg', 'wma', 'opus'];

    private const COVER_NAMES = ['cover', 'folder', 'albumart', 'front'];

    public function __construct(private MetadataReader $metadataReader)
    {
    }

    /**
     * @return array<string, array{
     *     artist: string,
     *     title: string,
     *     year: int|null,
     *     cover_path: string|null,
     *     folder_path: string,
     *     tracks: array<array{
     *         title: string,
     *         artist: string,
     *         album: string,
     *         year: int|null,
     *         track_number: int,
     *         duration: int,
     *         file_path: string,
     *         format: string,
     *         bitrate: int|null,
     *     }>
     * }>
     */
    public function scan(string $libraryPath): array
    {
        $albums = [];

        if (!is_dir($libraryPath)) {
            return $albums;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($libraryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (!$this->isAudioFile($fileInfo)) {
                continue;
            }

            $metadata = $this->metadataReader->read($fileInfo->getRealPath());
            $artist = $metadata['artist'];
            $albumTitle = $metadata['album'];
            $albumKey = "$artist/$albumTitle";

            if (!isset($albums[$albumKey])) {
                $albumPath = $this->findAlbumFolder($fileInfo->getRealPath(), $artist, $albumTitle);

                $albums[$albumKey] = [
                    'artist' => $artist,
                    'title' => $albumTitle,
                    'year' => $metadata['year'],
                    'cover_path' => $this->findCoverArt($albumPath ?? dirname($fileInfo->getRealPath())),
                    'folder_path' => $albumPath ?? dirname($fileInfo->getRealPath()),
                    'tracks' => [],
                ];
            }

            $albums[$albumKey]['tracks'][] = [
                'title' => $metadata['title'],
                'artist' => $metadata['artist'],
                'album' => $metadata['album'],
                'year' => $metadata['year'],
                'track_number' => $metadata['track_number'],
                'duration' => $metadata['duration'],
                'file_path' => $fileInfo->getRealPath(),
                'format' => $metadata['format'],
                'bitrate' => $metadata['bitrate'],
            ];
        }

        return $this->sortAlbums($albums);
    }

    private function isAudioFile(SplFileInfo $fileInfo): bool
    {
        $extension = strtolower($fileInfo->getExtension());

        return in_array($extension, self::AUDIO_EXTENSIONS, true);
    }

    private function findAlbumFolder(string $filePath, string $artist, string $album): ?string
    {
        $dir = dirname($filePath);

        while ($dir !== '/' && $dir !== dirname($dir)) {
            $basename = basename($dir);

            if (stripos($basename, $album) !== false || stripos($basename, $artist) !== false) {
                return $dir;
            }

            $dir = dirname($dir);
        }

        return null;
    }

    private function findCoverArt(string $albumPath): ?string
    {
        if (!is_dir($albumPath)) {
            return null;
        }

        $files = scandir($albumPath);

        foreach ($files as $file) {
            $lowercase = strtolower($file);

            foreach (self::COVER_NAMES as $coverName) {
                if (stripos($lowercase, $coverName) !== false && $this->isImageFile($file)) {
                    return "$albumPath/$file";
                }
            }
        }

        return null;
    }

    private function isImageFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private function sortAlbums(array $albums): array
    {
        usort($albums, function ($a, $b) {
            $artistCmp = strcasecmp($a['artist'], $b['artist']);

            return $artistCmp !== 0 ? $artistCmp : strcasecmp($a['title'], $b['title']);
        });

        foreach ($albums as &$album) {
            usort($album['tracks'], fn ($a, $b) => $a['track_number'] <=> $b['track_number']);
        }

        return array_values($albums);
    }
}
