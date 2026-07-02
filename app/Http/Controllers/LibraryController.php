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
        $albums = Album::with('tracks')
            ->orderBy('artist')
            ->orderBy('title')
            ->paginate(20);

        return Inertia::render('Library/Index', [
            'albums' => $albums,
        ]);
    }

    public function scan(): \Illuminate\Http\RedirectResponse
    {
        ScanLibrary::dispatch();

        return back()->with('message', 'Library scan started. Check back in a moment.');
    }
}
