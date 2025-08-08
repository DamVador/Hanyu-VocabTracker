<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\StudySessionController;
use App\Http\Controllers\ResourcesListController;
use App\Http\Controllers\WordImportController;
use App\Http\Controllers\StudySessionWordController;
use App\Http\Controllers\PostController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
    Route::post('/words/{word}/save-notes', [WordController::class, 'saveNotes'])->name('words.saveNotes');

    // Recording study progress
    Route::post('/words/{word}/record-study', [WordController::class, 'recordStudy'])->name('words.recordStudy');

    // Automatic study session
    Route::get('/study', [StudyController::class, 'autoReviewIndex'])->name('study.index'); // Renamed to autoReviewIndex

    // Study Session Management Routes
    Route::resource('study-sessions', StudySessionController::class);
    Route::get('/study-sessions/{study_session}/review', [StudyController::class, 'sessionReview'])->name('study.sessionReview');
    Route::get('/session-studies/{study_session}/words', [StudySessionWordController::class, 'index'])
        ->name('session-studies.words.index');
    Route::delete('/study-sessions/{study_session}/words/{word}/detach', [StudySessionWordController::class, 'detachWord'])
        ->name('session-studies.words.detach');

    // CSV Import/export Route
    Route::post('/words/import-csv', [WordImportController::class, 'importCsv'])->name('words.importCsv');
    Route::get('/study-sessions/{study_session}/export-csv', [StudySessionController::class, 'exportSingleCsv'])->name('study-sessions.exportSingleCsv');

    // Remove for google safety reasons
    // Route::get('/resources-lists', [ResourcesListController::class, 'index'])->name('resources.lists');
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

// blog
Route::prefix('blog')->name('blog.')->controller(PostController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('create', 'create')->name('create')->middleware(['auth', 'role:admin']);
    Route::post('/', 'store')->name('store')->middleware(['auth']);
    Route::get('{post:slug}', 'show')->name('show');
    Route::get('/blog/{post}/edit', [PostController::class, 'edit'])->name('edit')->middleware(['auth', 'role:admin']);
    Route::put('/blog/{post}', [PostController::class, 'update'])->name('update');
});

Route::get('/debug-noindex', function (Request $request) {
    $currentHost = $request->getHost();
    $canonicalHost = parse_url(Config::get('app.production_url'), PHP_URL_HOST);

    return "
        <h1>Debug Noindex</h1>
        <p>Current Host: <strong>" . htmlspecialchars($currentHost) . "</strong></p>
        <p>Canonical Host (from .env): <strong>" . htmlspecialchars($canonicalHost) . "</strong></p>
        <p>Are they equal? " . (($currentHost === $canonicalHost) ? '<strong>YES</strong>' : '<strong>NO</strong>') . "</p>
        <p>If 'NO', the noindex tag will be present.</p>
        <p>Remember to remove this route after debugging!</p>
    ";
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
