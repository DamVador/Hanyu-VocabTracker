<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
