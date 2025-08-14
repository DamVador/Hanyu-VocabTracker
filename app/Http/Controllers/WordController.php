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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config; 

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

        $query = Word::query();
        $query->select('words.*');

        $latestHistorySubquery = History::select('word_id', 'learning_status', 'created_at')
            ->where('user_id', $user->id)
            ->whereRaw('histories.id = (SELECT MAX(id) FROM histories WHERE word_id = histories.word_id AND user_id = ?)', [$user->id]);

        $query->leftJoinSub($latestHistorySubquery, 'latest_word_history', function ($join) {
            $join->on('words.id', '=', 'latest_word_history.word_id');
        })
        ->addSelect('latest_word_history.learning_status as current_learning_status')
        ->addSelect('latest_word_history.created_at as latest_revision_created_at'); // Ajout de la date pour le tri et l'affichage

        $query->where(function ($q) use ($user, $searchPinyin, $searchTranslation, $tag, $selectedLearningStatuses) {
            $q->where('words.user_id', $user->id);

            if ($searchPinyin) {
                $q->where('pinyin', 'like', '%' . $searchPinyin . '%');
            }
            if ($searchTranslation) {
                $q->where('translation', 'like', '%' . $searchTranslation . '%');
            }

            if ($tag) {
                $q->whereHas('tags', function ($tagQuery) use ($tag) {
                    $tagQuery->where('name', $tag);
                });
            }

            if (!empty($selectedLearningStatuses)) {
                $q->where(function ($statusQuery) use ($selectedLearningStatuses) {
                    $statusQuery->whereIn('latest_word_history.learning_status', $selectedLearningStatuses);

                    if (in_array('New', $selectedLearningStatuses)) {
                        $statusQuery->orWhereNull('latest_word_history.learning_status');
                    }
                });
            }
        });

        $query->withSum(['histories as failed_attempts' => function ($historyQuery) use ($user) {
            $historyQuery->where('user_id', $user->id);
        }], 'total_incorrect_revisions');

        $sortColumnMap = [
            'pinyin' => 'pinyin',
            'translation' => 'translation',
            'chinese_word' => 'chinese_word',
            'created_at' => 'created_at',
            'failed_attempts' => 'failed_attempts',
            'last_revision_date' => 'latest_revision_created_at',
            'learning_status' => 'current_learning_status',
        ];

        $dbSortColumn = $sortColumnMap[$sortBy] ?? 'pinyin';

        $query->orderBy($dbSortColumn, $sortDirection);

        $words = $query->with('tags')
                       ->paginate(10)
                       ->withQueryString();

        $words->through(fn ($word) => [
            'id' => $word->id,
            'chinese_word' => $word->chinese_word,
            'pinyin' => $word->pinyin,
            'translation' => $word->translation,
            'tags' => $word->tags->pluck('name')->toArray(),
            'created_at' => Carbon::parse($word->created_at)->format('M d, Y'),
            'failed_attempts' => $word->failed_attempts ?? 0,
            'last_revision_date' => $word->latest_revision_created_at ? Carbon::parse($word->latest_revision_created_at)->format('M d, Y') : 'Never',
            'learning_status' => $word->current_learning_status ?? 'New',
        ]);

        $allTags = Tag::select('tags.name')
                      ->join('tag_word', 'tags.id', '=', 'tag_word.tag_id')
                      ->join('words', 'tag_word.word_id', '=', 'words.id')
                      ->where('words.user_id', $user->id)
                      ->distinct()
                      ->orderBy('tags.name')
                      ->pluck('name')
                      ->all();

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
                'learning_statuses' => $selectedLearningStatuses,
            ],
            'allTags' => $allTags,
            'allLearningStatuses' => $allLearningStatuses,
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

            $longestStreak = 0;
            $studyDates = History::where('user_id', $user->id)
                                ->selectRaw('DATE(last_revision) as study_date')
                                ->distinct()
                                ->orderBy('study_date')
                                ->get()
                                ->pluck('study_date')
                                ->map(fn ($date) => Carbon::parse($date));

            $tempCurrentStreak = 0;
            $previousDate = null;

            foreach ($studyDates as $date) {
                if ($previousDate === null) {
                    $tempCurrentStreak = 1;
                } elseif ($date->eq($previousDate->copy()->addDay())) {
                    $tempCurrentStreak++;
                } else {
                    $tempCurrentStreak = 1;
                }
                $longestStreak = max($longestStreak, $tempCurrentStreak);
                $previousDate = $date;
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
                'longestStreak' => $longestStreak,
                // Remove for google safety reasons
                // 'features' => [
                //     'vocabListsEnabled' => Config::get('app.features.vocab_lists_enabled'),
                // ],
            ]);
    }

    public function create(Request $request)
    {
        $allTags = Tag::pluck('name')->toArray();
        $userStudySessions = $request->user()->studySessions()->select('id', 'name')->get();
        $redirectTo = $request->query('redirect_to');
        $prefillStudySessionId = $request->query('prefill_study_session_id');

        if ($prefillStudySessionId !== null) {
            $prefillStudySessionId = (int) $prefillStudySessionId;
        }

        return Inertia::render('Words/Create', [
            'allTags' => $allTags,
            'userStudySessions' => $userStudySessions,
            'redirect_to' => $redirectTo,
            'prefill_study_session_id' => $prefillStudySessionId,
        ]);
    }


    public function save(Request $request)
    {
        $validated = $request->validate([
            'chinese_word' => 'required|string|max:255',
            'pinyin' => 'required|string|max:255',
            'translation' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'study_session_ids' => 'nullable|array',
            'study_session_ids.*' => 'exists:study_sessions,id',
            '_redirect_to' => 'nullable|url',
        ]);

        $word = $request->user()->words()->create([
            'chinese_word' => $validated['chinese_word'],
            'pinyin' => $validated['pinyin'],
            'translation' => $validated['translation'],
            'notes' => $validated['notes'],
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

        $redirectTo = $validated['_redirect_to'] ?? null;

        if ($redirectTo) {
            return redirect($redirectTo)->with('success', 'Word created successfully!');
        } else {
            return redirect()->route('words.index')->with('success', 'Word created successfully!');
        }
    }

    public function edit(Request $request, Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $word->load('tags');
        $allTags = Tag::pluck('name')->toArray();
        $redirectTo = $request->query('redirect_to');

        $userStudySessions = $request->user()->studySessions()->select('id', 'name')->get();

        $attachedStudySessionIds = $word->studySessions()->pluck('study_sessions.id')->toArray();

        return Inertia::render('Words/Edit', [
            'word' => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'notes' => $word->notes
            ],
            'currentTags' => $word->tags->pluck('name')->toArray(),
            'allTags' => $allTags,
            'userStudySessions' => $userStudySessions,
            'attachedStudySessionIds' => $attachedStudySessionIds,
            'redirect_to' => $redirectTo,
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
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'study_session_ids' => 'nullable|array',
            'study_session_ids.*' => 'exists:study_sessions,id',
            '_redirect_to' => 'nullable|url',
        ]);

        $word->update([
            'chinese_word' => $validated['chinese_word'],
            'pinyin' => $validated['pinyin'],
            'translation' => $validated['translation'],
            'notes' => $validated['notes'],
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

        $redirectTo = $validated['_redirect_to'] ?? null;

        if ($redirectTo) {
            return redirect($redirectTo)->with('success', 'Word updated successfully!');
        } else {
            return redirect()->route('words.index')->with('success', 'Word updated successfully!');
        }
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

    public function saveNotes(Request $request, Word $word)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $word->notes = $request->input('notes');
        $word->save();

        return response()->json(['message' => 'Notes saved successfully!', 'notes' => $word->notes]);
    } 
}
