<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticsController; 

Route::middleware('auth')->group(function () {
    // API routes for statistics
    Route::get('/statistics/words-added-timeline', [StatisticsController::class, 'wordsAddedOverTime'])->name('api.statistics.words-added-timeline');
    Route::get('/statistics/words-reviewed-timeline', [StatisticsController::class, 'wordsReviewedOverTime'])->name('api.statistics.words-reviewed-timeline');
    Route::get('/statistics/learning-status-distribution', [StatisticsController::class, 'learningStatusDistribution'])->name('api.statistics.learning-status-distribution');
    Route::get('/statistics/accuracy-rate-timeline', [StatisticsController::class, 'accuracyRateOverTime'])->name('api.statistics.accuracy-rate-timeline');
    Route::get('/statistics/top-difficult-words', [StatisticsController::class, 'topDifficultWords'])->name('api.statistics.top-difficult-words');
});