<?php

namespace App\Http\Controllers;

use App\Jobs\ScanLibrary;
use App\Models\Album;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function index(): Response
    {
        $albums = Album::withCount('tracks')
            ->orderBy('artist')
            ->orderBy('title')
            ->paginate(20);

        return Inertia::render('Library/Index', [
            'albums' => $albums,
        ]);
    }

    public function show(Album $album): Response
    {
        $album->load('tracks');

        return Inertia::render('Library/Show', [
            'album' => $album,
        ]);
    }

    public function scan(): \Illuminate\Http\RedirectResponse
    {
        ScanLibrary::dispatch();

        return back()->with('message', 'Library scan started. Check back in a moment.');
    }
}
