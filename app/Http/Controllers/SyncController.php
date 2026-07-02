<?php

namespace App\Http\Controllers;

use App\Jobs\SyncToCard;
use App\Models\SyncLog;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SyncController extends Controller
{
    public function store(): RedirectResponse
    {
        SyncToCard::dispatch();

        return redirect()->route('card.index')->with('message', 'Sync started. Check back in a moment.');
    }

    public function show(SyncLog $syncLog): Response
    {
        return Inertia::render('Sync/Show', [
            'syncLog' => $syncLog,
        ]);
    }
}
