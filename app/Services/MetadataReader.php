<?php

namespace App\Services;

use getID3;

class MetadataReader
{
    private getID3 $getID3;

    public function __construct()
    {
        $this->getID3 = new getID3();
    }

    /**
     * @return array{
     *     title: string,
     *     artist: string,
     *     album: string,
     *     year: int|null,
     *     track_number: int,
     *     duration: int,
     *     format: string,
     *     bitrate: int|null,
     * }
     */
    public function read(string $filePath): array
    {
        $this->getID3->setOption(['encoding' => 'UTF-8']);
        $info = $this->getID3->analyze($filePath);

        $tags = $info['tags'] ?? [];
        $combined = [];

        foreach ($tags as $tagset) {
            $combined = array_merge($combined, $tagset);
        }

        return [
            'title' => $this->extractString($combined, 'title', basename($filePath)),
            'artist' => $this->extractString($combined, 'artist', 'Unknown Artist'),
            'album' => $this->extractString($combined, 'album', 'Unknown Album'),
            'year' => $this->extractYear($combined),
            'track_number' => $this->extractTrackNumber($combined),
            'duration' => (int) ($info['playtime_seconds'] ?? 0),
            'format' => strtolower($info['fileformat'] ?? 'unknown'),
            'bitrate' => isset($info['bitrate']) ? (int) ($info['bitrate'] / 1000) : null,
        ];
    }

    private function extractString(array $tags, string $key, string $default): string
    {
        if (isset($tags[$key])) {
            $value = $tags[$key];
            if (is_array($value)) {
                $value = $value[0] ?? $default;
            }

            return trim((string) $value) ?: $default;
        }

        return $default;
    }

    private function extractYear(array $tags): ?int
    {
        $year = $this->extractString($tags, 'year', '');

        if (empty($year)) {
            $year = $this->extractString($tags, 'date', '');
        }

        if (empty($year)) {
            return null;
        }

        $year = (int) preg_replace('/\D/', '', $year);

        return $year > 0 ? $year : null;
    }

    private function extractTrackNumber(array $tags): int
    {
        $track = $this->extractString($tags, 'track_number', '0');

        if (empty($track)) {
            $track = $this->extractString($tags, 'track', '0');
        }

        preg_match('/\d+/', $track, $matches);

        return isset($matches[0]) ? (int) $matches[0] : 0;
    }
}
