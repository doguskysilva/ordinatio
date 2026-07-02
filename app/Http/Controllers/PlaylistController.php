<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTrackToPlaylistRequest;
use App\Http\Requests\StorePlaylistRequest;
use App\Models\Playlist;
use App\Models\Track;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistController extends Controller
{
    public function index(): Response
    {
        $playlists = Playlist::withCount('tracks')
            ->orderBy('name')
            ->get();

        return Inertia::render('Playlists/Index', [
            'playlists' => $playlists,
        ]);
    }

    public function show(Playlist $playlist): Response
    {
        $playlist->load('tracks');
        $availableTracks = Track::all();

        return Inertia::render('Playlists/Show', [
            'playlist' => $playlist,
            'availableTracks' => $availableTracks,
        ]);
    }

    public function store(StorePlaylistRequest $request): \Illuminate\Http\RedirectResponse
    {
        Playlist::create($request->validated());

        return redirect()->route('playlists.index');
    }

    public function destroy(Playlist $playlist): \Illuminate\Http\RedirectResponse
    {
        $playlist->delete();

        return redirect()->route('playlists.index');
    }

    public function addTrack(AddTrackToPlaylistRequest $request, Playlist $playlist): \Illuminate\Http\RedirectResponse
    {
        $maxPosition = $playlist->tracks()->max('position') ?? 0;

        $playlist->tracks()->attach($request->validated('track_id'), [
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('playlists.show', $playlist);
    }

    public function removeTrack(Playlist $playlist, Track $track): \Illuminate\Http\RedirectResponse
    {
        $playlist->tracks()->detach($track);

        return redirect()->route('playlists.show', $playlist);
    }

    public function reorderTrack(Playlist $playlist, Track $track): \Illuminate\Http\RedirectResponse
    {
        $playlist->tracks()->updateExistingPivot($track, [
            'position' => 1,
        ]);

        return redirect()->route('playlists.show', $playlist);
    }
}
