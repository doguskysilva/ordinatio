<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;
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

        return Inertia::render('Playlists/Show', [
            'playlist' => $playlist,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Playlist::create($validated);

        return redirect()->route('playlists.index');
    }

    public function destroy(Playlist $playlist): \Illuminate\Http\RedirectResponse
    {
        $playlist->delete();

        return redirect()->route('playlists.index');
    }

    public function addTrack(Request $request, Playlist $playlist): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
        ]);

        $maxPosition = $playlist->tracks()->max('position') ?? 0;

        $playlist->tracks()->attach($validated['track_id'], [
            'position' => $maxPosition + 1,
        ]);

        return redirect()->route('playlists.show', $playlist);
    }

    public function removeTrack(Playlist $playlist, Track $track): \Illuminate\Http\RedirectResponse
    {
        $playlist->tracks()->detach($track);

        return redirect()->route('playlists.show', $playlist);
    }

    public function reorderTrack(Request $request, Playlist $playlist, Track $track): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:1'],
        ]);

        $playlist->tracks()->updateExistingPivot($track, [
            'position' => $validated['position'],
        ]);

        return redirect()->route('playlists.show', $playlist);
    }
}
