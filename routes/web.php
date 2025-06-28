<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;

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
    Route::get('/words/{word}/edit', [WordController::class, 'edit'])->name('words.edit');
    Route::patch('/words/{word}', [WordController::class, 'update'])->name('words.update');
    Route::delete('/words/{word}', [WordController::class, 'destroy'])->name('words.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Admin User Management
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
