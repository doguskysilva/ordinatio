<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::controller(LibraryController::class)->group(function () {
        Route::get('library', 'index')->name('library.index');
        Route::get('library/{album}', 'show')->name('library.show');
        Route::post('library/scan', 'scan')->name('library.scan');
        Route::post('library/{album}/add-to-card', 'addToCard')->name('library.add-to-card');
    });

    Route::controller(PlaylistController::class)->group(function () {
        Route::get('playlists', 'index')->name('playlists.index');
        Route::post('playlists', 'store')->name('playlists.store');
        Route::get('playlists/{playlist}', 'show')->name('playlists.show');
        Route::delete('playlists/{playlist}', 'destroy')->name('playlists.destroy');
        Route::post('playlists/{playlist}/tracks', 'addTrack')->name('playlists.add-track');
        Route::delete('playlists/{playlist}/tracks/{track}', 'removeTrack')->name('playlists.remove-track');
        Route::patch('playlists/{playlist}/tracks/{track}', 'reorderTrack')->name('playlists.reorder-track');
    });

    Route::get('card', [CardController::class, 'index'])->name('card.index');

    Route::post('sync', [SyncController::class, 'store'])->name('sync.store');
    Route::get('sync/{syncLog}', [SyncController::class, 'show'])->name('sync.show');
});

require __DIR__.'/settings.php';
