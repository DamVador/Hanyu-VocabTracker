<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Tag;
use App\Models\History;
use App\Models\StudySession;
use App\Models\User;
use App\Http\Requests\StoreWordRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // For subqueries
use Illuminate\Support\Facades\Schema;

class WordController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $searchPinyin = $request->input('search_pinyin');
        $searchTranslation = $request->input('search_translation');
        $tag = $request->input('tag');
        $sortBy = $request->input('sort_by', 'pinyin');
        $sortDirection = $request->input('sort_direction', 'asc');
        $selectedLearningStatuses = $request->input('learning_statuses', []);

        $query = Word::query()->where('user_id', $user->id);

        // Apply search filters
        if ($searchPinyin) {
            $query->where('pinyin', 'like', '%' . $searchPinyin . '%');
        }
        if ($searchTranslation) {
            $query->where('translation', 'like', '%' . $searchTranslation . '%');
        }

        // Apply tag filter
        if ($tag) {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        // Add 'failed_attempts' using withSum on 'total_incorrect_revisions'
        $query->withSum(['histories as failed_attempts' => function ($historyQuery) use ($user) {
            $historyQuery->where('user_id', $user->id);
        }], 'total_incorrect_revisions');

        $query->withMax(['histories as last_revision_date' => function ($historyQuery) use ($user) {
            $historyQuery->where('user_id', $user->id);
        }], 'created_at');

        // To get the latest learning_status
        $latestStatusPerWord = DB::table('histories as h1')
            ->select('h1.word_id', 'h1.learning_status')
            ->join(
                DB::raw('(SELECT word_id, MAX(created_at) as max_created_at FROM histories WHERE user_id = ' . $user->id . ' GROUP BY word_id) as h2'),
                function($join) {
                    $join->on('h1.word_id', '=', 'h2.word_id')
                         ->on('h1.created_at', '=', 'h2.max_created_at');
                }
            )
            ->where('h1.user_id', $user->id);

        $query->leftJoinSub($latestStatusPerWord, 'latest_word_history', function ($join) {
            $join->on('words.id', '=', 'latest_word_history.word_id');
        })
        ->addSelect('latest_word_history.learning_status as current_learning_status');

        // New: Apply learning_status filter
        if (!empty($selectedLearningStatuses)) {
            // Filter words based on their current_learning_status.
             $query->whereIn('latest_word_history.learning_status', $selectedLearningStatuses)
                   ->orWhere(function ($q) use ($selectedLearningStatuses) {
                       // If 'New' is selected and there's no history, include these words.
                       // This handles words that don't appear in the history table yet.
                       if (in_array('New', $selectedLearningStatuses)) {
                           $q->whereNull('latest_word_history.learning_status');
                       }
                   });
        }

        $sortColumnMap = [
            'pinyin' => 'pinyin',
            'translation' => 'translation',
            'chinese_word' => 'chinese_word',
            'created_at' => 'created_at',
            'failed_attempts' => 'failed_attempts',
            'last_revision_date' => 'last_revision_date',
            'learning_status' => 'current_learning_status',
        ];

        $dbSortColumn = $sortColumnMap[$sortBy] ?? 'pinyin';

        $query->orderBy($dbSortColumn, $sortDirection);


        // Paginate the results and append query string for filters
        $words = $query->with('tags')
                       ->paginate(10)
                       ->withQueryString();

        // Map the collection for Inertia
        $words->through(fn ($word) => [
            'id' => $word->id,
            'chinese_word' => $word->chinese_word,
            'pinyin' => $word->pinyin,
            'translation' => $word->translation,
            'tags' => $word->tags->pluck('name')->toArray(),
            'created_at' => Carbon::parse($word->created_at)->format('M d, Y'),
            'failed_attempts' => $word->failed_attempts ?? 0,
            'last_revision_date' => $word->last_revision_date ? Carbon::parse($word->last_revision_date)->format('M d, Y') : 'Never',
            'learning_status' => $word->current_learning_status ?? 'New',
        ]);

        // Get all unique tags for the filter dropdown
        $allTags = Tag::pluck('name')->unique()->sort()->values()->all();

        $allLearningStatuses = History::select('learning_status')
                                ->where('user_id', $user->id)
                                ->distinct()
                                ->pluck('learning_status')
                                ->toArray();

        if (!in_array('New', $allLearningStatuses)) {
            array_unshift($allLearningStatuses, 'New');
        }
        sort($allLearningStatuses);


        return Inertia::render('Words/Index', [
            'words' => $words,
            'filters' => [
                'search_pinyin' => $searchPinyin,
                'search_translation' => $searchTranslation,
                'tag' => $tag,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'learning_statuses' => $selectedLearningStatuses, // Pass back selected statuses
            ],
            'allTags' => $allTags,
            'allLearningStatuses' => $allLearningStatuses, // Pass distinct statuses to frontend
        ]);
    }

    public function dashboardHome(Request $request)
    {
            $user = $request->user();
    
            // 1. Words Added Statistics
            $wordsAddedThisWeek = Word::where('user_id', $user->id)
                                      ->where('created_at', '>=', Carbon::now()->startOfWeek())
                                      ->count();
            $wordsAddedThisMonth = Word::where('user_id', $user->id)
                                       ->where('created_at', '>=', Carbon::now()->startOfMonth())
                                       ->count();
    
            // 2. Words Reviewed Statistics
            $wordsReviewedToday = History::where('user_id', $user->id)
                                         ->whereDate('last_revision', Carbon::today())
                                         ->distinct('word_id')
                                         ->count('word_id'); // Count distinct word IDs
    
            $wordsReviewedThisWeek = History::where('user_id', $user->id)
                                            ->where('last_revision', '>=', Carbon::now()->startOfWeek())
                                            ->distinct('word_id')
                                            ->count('word_id'); // Count distinct word IDs
    
            // 3. Average Study Session Length -- 
            // TODO - update the DB to add a history for study_sessions with start_date and end_date

            // $averageSessionLength = null;
            // if (Schema::hasTable('study_sessions')) { // Check if table exists
            //     $totalDurationSeconds = StudySession::where('user_id', $user->id)
            //                                         ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_seconds')
            //                                         ->value('total_seconds');
    
            //     $totalSessions = StudySession::where('user_id', $user->id)->count();
    
            //     if ($totalSessions > 0) {
            //         $averageDurationInMinutes = round(($totalDurationSeconds / $totalSessions) / 60, 1);
            //         $averageSessionLength = $averageDurationInMinutes . ' mins';
            //     } else {
            //         $averageSessionLength = 'N/A';
            //     }
            // } else {
            //      $averageSessionLength = 'N/A (No study_sessions table)';
            // }
    
    
            // 4. Streak System
            $streak = 0;
            $today = Carbon::today();
            $lastStudyDate = History::where('user_id', $user->id)
                                    ->latest('last_revision')
                                    ->value('last_revision');
    
            if ($lastStudyDate) {
                $lastStudyDay = $lastStudyDate->startOfDay();
    
                if ($lastStudyDay->eq($today)) {
                    $streak = 1; // At least today counts as 1 if studied
                } elseif ($lastStudyDay->diffInDays($today) === 1 && $lastStudyDay->lt($today)) {
                    // Studied yesterday, start counting backwards
                    $streak = 1; // Start with 1 for yesterday
                } else {
                    // Not studied today or yesterday, streak is 0
                    $streak = 0;
                }
    
                // Iterate backwards from the day before last study to count consecutive days
                // Ensure we don't count today twice if it's already considered
                $currentDay = $lastStudyDay->copy();
                if ($lastStudyDay->eq($today)) {
                    $currentDay->subDay(); // Start counting from yesterday if today was studied
                }
    
                while ($currentDay->greaterThanOrEqualTo($user->created_at->startOfDay())) {
                    $hasStudiedOnDay = History::where('user_id', $user->id)
                                              ->whereDate('last_revision', $currentDay)
                                              ->exists();
                    if ($hasStudiedOnDay) {
                        $streak++;
                    } else {
                        break;
                    }
                    $currentDay->subDay();
                }
            }
    
            $totalWords = Word::where('user_id', $user->id)->count();
    
            $wordsDueForReview = History::where('user_id', $user->id)
                                        ->where('next_revision', '<=', Carbon::now())
                                        ->count();
    
    
            return Inertia::render('Dashboard', [
                'totalWords' => $totalWords,
                'recentWords' => Word::where('user_id', $user->id)
                                     ->with('tags')
                                     ->latest()
                                     ->take(5)
                                     ->get()
                                     ->map(fn ($word) => [
                                         'id' => $word->id,
                                         'chinese_word' => $word->chinese_word,
                                         'pinyin' => $word->pinyin,
                                         'translation' => $word->translation,
                                         'tags' => $word->tags->pluck('name')->toArray(),
                                     ]),
                'wordsDueForReview' => $wordsDueForReview,
                'wordsAddedThisWeek' => $wordsAddedThisWeek,
                'wordsAddedThisMonth' => $wordsAddedThisMonth,
                'wordsReviewedToday' => $wordsReviewedToday,
                'wordsReviewedThisWeek' => $wordsReviewedThisWeek,
                // 'averageSessionLength' => $averageSessionLength,
                'currentStreak' => $streak,
            ]);
    }

    public function create(Request $request)
    {
        $allTags = Tag::pluck('name')->toArray();
        $userStudySessions = $request->user()->studySessions()->select('id', 'name')->get();
        return Inertia::render('Words/Create', [
            'allTags' => $allTags,
            'userStudySessions' => $userStudySessions,
        ]);
    }


    public function save(Request $request)
    {
        $validated = $request->validate([
            'chinese_word' => 'required|string|max:255',
            'pinyin' => 'required|string|max:255',
            'translation' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'study_session_ids' => 'nullable|array',
            'study_session_ids.*' => 'exists:study_sessions,id',
        ]);

        $word = $request->user()->words()->create([
            'chinese_word' => $validated['chinese_word'],
            'pinyin' => $validated['pinyin'],
            'translation' => $validated['translation'],
        ]);

        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $word->tags()->attach($tagIds);
        }

        if (isset($validated['study_session_ids'])) {
            $word->studySessions()->attach($validated['study_session_ids']);
        }

        return redirect()->route('words.index')->with('success', 'Word created successfully!');
    }

    public function edit(Request $request, Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $word->load('tags');
        $allTags = Tag::pluck('name')->toArray();

        $userStudySessions = $request->user()->studySessions()->select('id', 'name')->get();

        $attachedStudySessionIds = $word->studySessions()->pluck('study_sessions.id')->toArray();

        return Inertia::render('Words/Edit', [
            'word' => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
            ],
            'currentTags' => $word->tags->pluck('name')->toArray(),
            'allTags' => $allTags,
            'userStudySessions' => $userStudySessions,
            'attachedStudySessionIds' => $attachedStudySessionIds,
        ]);
    }

    /**
     * Update the specified word in storage.
     */
    public function update(Request $request, Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'chinese_word' => 'required|string|max:255',
            'pinyin' => 'required|string|max:255',
            'translation' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'study_session_ids' => 'nullable|array',
            'study_session_ids.*' => 'exists:study_sessions,id',
        ]);

        $word->update([
            'chinese_word' => $validated['chinese_word'],
            'pinyin' => $validated['pinyin'],
            'translation' => $validated['translation'],
        ]);

        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $word->tags()->sync($tagIds);
        } else {
            $word->tags()->detach();
        }

        $word->studySessions()->sync($validated['study_session_ids'] ?? []);

        return redirect()->route('words.index')->with('success', 'Word updated successfully!');
    }

    public function destroy(Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action. You do not own this word.');
        }

        $word->delete();

        return redirect()->route('words.index')->with('success', 'Word deleted successfully!');
    }

    /**
     * Record a study event for a specific word. (This method remains here)
     */
    public function recordStudy(Request $request, Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action. You do not own this word.');
        }

        $validated = $request->validate([
            'correct' => 'required|boolean',
        ]);

        $isCorrect = $validated['correct'];
        $user = $request->user();

        $history = History::firstOrNew([
            'word_id' => $word->id,
            'user_id' => $user->id,
        ]);

        if (!$history->exists) {
            $history->revision_interval = 0; // Days
            $history->consecutive_correct_revisions = 0;
            $history->total_incorrect_revisions = 0;
            $history->learning_status = 'Revise'; // TODO - Default status for new words or other status ?
        }

        // TODO  ||--------> UPDATE ALGO, consider the new study session logic <--------
        //       \/
        // --- SRS Logic (Simplified Anki-like algorithm) ---
        if ($isCorrect) {
            $history->consecutive_correct_revisions++;
            $history->total_incorrect_revisions = 0; // Reset if correct

            if ($history->consecutive_correct_revisions === 1) {
                $history->revision_interval = 1; // 1 day
            } elseif ($history->consecutive_correct_revisions === 2) {
                $history->revision_interval = 3; // 3 days
            } else {
                // Exponential increase for consecutive correct answers
                $history->revision_interval = $history->revision_interval * 2;
                if ($history->revision_interval === 0) { // Catch case if initial was 0
                     $history->revision_interval = 1; // Prevent 0 interval if somehow it gets there
                }
            }

            if ($history->consecutive_correct_revisions >= 5) { // Example threshold for 'Mastered'
                $history->learning_status = 'Mastered';
            } else {
                $history->learning_status = 'Revise';
            }

        } else {
            $history->consecutive_correct_revisions = 0;
            $history->total_incorrect_revisions++;
            $history->revision_interval = 0;
            $history->learning_status = 'Forgot';
        }

        $history->last_revision = Carbon::now();
        $history->next_revision = Carbon::now()->addDays($history->revision_interval);

        $history->save();

        return response()->json([
            'message' => 'Study record updated successfully!',
            'history' => $history->toArray(),
        ]);
    }
}
