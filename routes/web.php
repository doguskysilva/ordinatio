<?php

use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::controller(LibraryController::class)->group(function () {
        Route::get('library', 'index')->name('library.index');
        Route::get('library/{album}', 'show')->name('library.show');
        Route::post('library/scan', 'scan')->name('library.scan');
    });
});

require __DIR__.'/settings.php';
