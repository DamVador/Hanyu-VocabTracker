<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Word;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StatisticsSnapshot;

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

            $data = StatisticsSnapshot::where('user_id', $user->id)
                                ->whereBetween('snapshot_date', [$startDate->startOfDay(), $endDate->endOfDay()])
                                ->select('snapshot_date as date', 'words_reviewed as count')
                                ->orderBy('snapshot_date', 'asc')
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

            $words = Word::with(['latestHistory' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->where('user_id', $user->id)
            ->get();

            $statusCounts = [
                'new' => 0,
                'revise' => 0,
                'forgot' => 0,
                'mastered' => 0,
            ];

            foreach ($words as $word) {
                $history = $word->latestHistory;

                if (!$history) {
                    $statusCounts['new']++;
                } else {
                    $status = $history->learning_status;

                    if ($status === 'Forgot') {
                        $statusCounts['forgot']++;
                    } elseif ($status === 'Mastered') {
                        $statusCounts['mastered']++;
                    } elseif ($history->next_revision && $history->next_revision->isFuture()) {
                        $statusCounts['revise']++;
                    } elseif ($history->next_revision && ($history->next_revision->isPast() || $history->next_revision->isToday())) {
                        $statusCounts['revise']++;
                    } else {
                        $statusCounts['revise']++;
                    }
                }
            }

            return response()->json([
                'new' => $statusCounts['new'],
                'revise' => $statusCounts['revise'],
                'forgot' => $statusCounts['forgot'],
                'mastered' => $statusCounts['mastered'],
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

            $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonths(6)->toDateString()));
            $endDate = Carbon::parse($request->input('end_date', Carbon::now()->toDateString()));

            $data = StatisticsSnapshot::where('user_id', $user->id)
                                    ->whereBetween('snapshot_date', [$startDate->startOfDay(), $endDate->endOfDay()])
                                    ->select('snapshot_date as date', 'correct_answers', 'incorrect_answers')
                                    ->orderBy('snapshot_date', 'asc')
                                    ->get();
            
            $formattedData = $data->map(function ($day) {
                $totalAttempts = $day->correct_answers + $day->incorrect_answers;
                $accuracyPercentage = $totalAttempts > 0 ? round(($day->correct_answers / $totalAttempts) * 100, 2) : 0;
                
                return [
                    'date' => $day->date,
                    'accuracy_percentage' => $accuracyPercentage,
                ];
            });

            return response()->json($formattedData);

        } catch (\Exception $e) {
            Log::error('Error fetching accuracy rate over time from snapshots: ' . $e->getMessage(), ['exception' => $e]);
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
