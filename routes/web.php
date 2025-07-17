<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\StudySessionController; 

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
    Route::put('/words/{word}', [WordController::class, 'update'])->name('words.update');
    Route::delete('/words/{word}', [WordController::class, 'destroy'])->name('words.destroy');

    // This route is for recording study progress. It remains under words
    // because it's an action directly pertaining to a specific 'word' resource.
    Route::post('/words/{word}/record-study', [WordController::class, 'recordStudy'])->name('words.recordStudy');

    // Automatic study session
    Route::get('/study', [StudyController::class, 'autoReviewIndex'])->name('study.index'); // Renamed to autoReviewIndex

    // Study Session Management Routes
    Route::resource('study-sessions', StudySessionController::class);
    Route::get('/study-sessions/{study_session}/review', [StudyController::class, 'sessionReview'])->name('study.sessionReview');
    
    // CSV Import/export Route
    Route::post('/words/import-csv', [App\Http\Controllers\WordImportController::class, 'importCsv'])->name('words.importCsv');
    Route::get('/study-sessions/{study_session}/export-csv', [StudySessionController::class, 'exportSingleCsv'])->name('study-sessions.exportSingleCsv');
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
