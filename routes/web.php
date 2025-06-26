<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/dashboard', [WordController::class, 'dashboardHome'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/words', function () {
    return Inertia::render('Word');
})->name('word');

Route::get('/words', [WordController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('words.index');

Route::get('/words/create', [WordController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('words.create');

Route::post('/words', [WordController::class, 'save'])
    ->middleware(['auth', 'verified'])
    ->name('words.save');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('Profile.edit');
    Route::patch('/profile/information', [ProfileController::class, 'updateProfileInformation'])->name('Profile.update-information');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('Profile.update-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('Profile.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
