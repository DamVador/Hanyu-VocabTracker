<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Word;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticsController extends Controller
{
    /**
     * Display the main statistics page.
     */
    public function index(Request $request)
    {
        return Inertia::render('Statistics/Index');
    }

    /**
     * Get data for words added over time.
     */
    public function wordsAddedOverTime(Request $request)
    {
        try {
            $user = $request->user();

            // Default date range: last 6 months
            $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonths(6)->toDateString()));
            $endDate = Carbon::parse($request->input('end_date', Carbon::now()->toDateString()));

            $data = Word::where('user_id', $user->id)
                        ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                        ->selectRaw('DATE(created_at) as date, count(*) as count')
                        ->groupBy('date')
                        ->orderBy('date', 'asc')
                        ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching words added data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch words added data.'], 500);
        }
    }

    /**
     * Get data for words reviewed over time (distinct words).
     */
    public function wordsReviewedOverTime(Request $request)
    {
        try {
            $user = $request->user();

            $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonths(6)->toDateString()));
            $endDate = Carbon::parse($request->input('end_date', Carbon::now()->toDateString()));

            $data = History::where('user_id', $user->id)
                           ->whereBetween('last_revision', [$startDate->startOfDay(), $endDate->endOfDay()])
                           ->selectRaw('DATE(last_revision) as date, count(DISTINCT word_id) as count')
                           ->groupBy('date')
                           ->orderBy('date', 'asc')
                           ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching words reviewed data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch words reviewed data.'], 500);
        }
    }

    /**
     * Get data for learning status distribution (Pie Chart).
     */
    public function learningStatusDistribution(Request $request)
    {
        try {
            $user = $request->user();

            $newWords = 0;
            $wordsWithNoHistoryCount = Word::where('user_id', $user->id)
                ->whereDoesntHave('histories', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->count();
            
            $newWords += $wordsWithNoHistoryCount;

            $wordsDueForReview = History::where('user_id', $user->id)
                                        ->where('next_revision', '<=', Carbon::now())
                                        ->count();
            $wordsForgotten = History::where('user_id', $user->id)
                                        ->where('learning_status', 'Forgot')
                                        ->count();

            return response()->json([
                'new' => $newWords,
                'revise' => $wordsDueForReview,
                'forgot' => $wordsForgotten,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching learning status distribution: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch learning status data.'], 500);
        }
    }

    /**
     * Get data for accuracy rate over time (Line Chart).
     */
    public function accuracyRateOverTime(Request $request)
    {
        try {
            $user = $request->user();

            // Default date range: last 6 months
            $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonths(6)->toDateString()));
            $endDate = Carbon::parse($request->input('end_date', Carbon::now()->toDateString()));

            // Aggregate correct and incorrect revisions per day
            $dailyAccuracy = History::where('user_id', $user->id)
                                    ->whereBetween('last_revision', [$startDate->startOfDay(), $endDate->endOfDay()])
                                    ->selectRaw('DATE(last_revision) as date, SUM(consecutive_correct_revisions) as total_correct, SUM(total_incorrect_revisions) as total_incorrect')
                                    ->groupBy('date')
                                    ->orderBy('date', 'asc')
                                    ->get();

            $data = $dailyAccuracy->map(function ($day) {
                $totalAttempts = $day->total_correct + $day->total_incorrect;
                $accuracyPercentage = $totalAttempts > 0 ? round(($day->total_correct / $totalAttempts) * 100, 2) : 0;
                return [
                    'date' => $day->date,
                    'accuracy_percentage' => $accuracyPercentage,
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching accuracy rate over time: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch accuracy data.'], 500);
        }
    }

    /**
     * Get data for top most difficult words (Horizontal Bar Chart).
     */
    public function topDifficultWords(Request $request)
    {
        try {
            $user = $request->user();

            $topWords = History::where('user_id', $user->id)
                               ->select('word_id', DB::raw('SUM(total_incorrect_revisions) as incorrect_attempts'))
                               ->groupBy('word_id')
                               ->orderByDesc('incorrect_attempts')
                               ->having('incorrect_attempts', '>', 0)
                               ->limit(15)
                               ->with('word:id,chinese_word,pinyin')
                               ->get();

            $data = $topWords->map(function ($item) {
                return [
                    'chinese_word' => $item->word->chinese_word,
                    'pinyin' => $item->word->pinyin,
                    'incorrect_attempts' => $item->incorrect_attempts,
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching top difficult words: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch difficult words data.'], 500);
        }
    }
}
