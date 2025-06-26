<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/dashboard', [WordController::class, 'dashboardHome'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile management routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('Profile.edit');
    Route::patch('/profile/information', [ProfileController::class, 'updateProfileInformation'])->name('Profile.update-information');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('Profile.update-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('Profile.destroy');

    // Word management routes
    Route::get('/words', [WordController::class, 'index'])->name('words.index');
    Route::get('/words/create', [WordController::class, 'create'])
        ->middleware(['auth', 'verified'])
        ->name('words.create');
    Route::post('/words', [WordController::class, 'save'])->name('words.save');

    // If you plan to implement edit/delete later, you might use:
    // Route::get('/words/{word}/edit', [WordController::class, 'edit'])->name('words.edit');
    // Route::patch('/words/{word}', [WordController::class, 'update'])->name('words.update');
    // Route::delete('/words/{word}', [WordController::class, 'destroy'])->name('words.destroy');
    // OR simply use Route::resource('words', WordController::class); which generates all of them
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
