<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\Tag;
use App\Models\History;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class StudyController extends Controller
{
    /**
     * Display a listing of the words. (Consultation only)
     * This method remains unchanged for the general word list.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $searchPinyin = $request->input('search_pinyin');
        $searchTranslation = $request->input('search_translation');
        $filterTag = $request->input('tag');
        $sortBy = $request->input('sort_by', 'pinyin');
        $sortDirection = $request->input('sort_direction', 'asc');

        $wordsQuery = $user->words()->with('tags');

        if ($searchPinyin) {
            $wordsQuery->where('pinyin', 'like', '%' . $searchPinyin . '%');
        }

        if ($searchTranslation) {
            $wordsQuery->where('translation', 'like', '%' . $searchTranslation . '%');
        }

        if ($filterTag) {
            $wordsQuery->whereHas('tags', function ($query) use ($filterTag) {
                $query->where('name', $filterTag);
            });
        }

        $allowedSortBy = ['pinyin', 'translation', 'chinese_word', 'created_at'];
        if (!in_array($sortBy, $allowedSortBy)) { $sortBy = 'pinyin'; }
        if (!in_array($sortDirection, ['asc', 'desc'])) { $sortDirection = 'asc'; }

        $wordsQuery->orderBy($sortBy, $sortDirection);

        $words = $wordsQuery->paginate(10)->withQueryString();

        $words->through(fn ($word) => [
            'id' => $word->id,
            'chinese_word' => $word->chinese_word,
            'pinyin' => $word->pinyin,
            'translation' => $word->translation,
            'tags' => $word->tags->pluck('name')->toArray(),
            'created_at' => $word->created_at->format('M d, Y'),
        ]);

        $allTags = Tag::whereHas('words', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('name')->toArray();

        return Inertia::render('Words/Index', [
            'words' => $words,
            'filters' => $request->only(['search_pinyin', 'search_translation', 'tag', 'sort_by', 'sort_direction']),
            'allTags' => $allTags,
        ]);
    }

    /**
     * Record a study event for a specific word. (Remains here)
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
            $history->revision_interval = 0;
            $history->consecutive_correct_revisions = 0;
            $history->total_incorrect_revisions = 0;
            $history->learning_status = 'Revise';
        }

        // TODO - Rework this algo
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
                if ($history->revision_interval === 0) {
                     $history->revision_interval = 1; // Prevent 0 interval if somehow it gets there
                }
            }

            if ($history->consecutive_correct_revisions >= 5) { // Example threshold for 'Mastered'
                $history->learning_status = 'Mastered';
            } else {
                $history->learning_status = 'Revise';
            }

        } else { // User got it incorrect
            $history->consecutive_correct_revisions = 0; // Reset consecutive
            $history->total_incorrect_revisions++;
            $history->revision_interval = 1; // Reset interval to 0 or 1 day for immediate re-review
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

    /**
     * Display the automatic study session page with words due for review.
     * This is your original /study page.
     */
    public function autoReviewIndex(Request $request)
    {
        $user = $request->user();

        $wordsToReview = Word::query()
            ->where('user_id', $user->id)
            ->with(['tags', 'histories' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->select('id', 'chinese_word', 'pinyin', 'translation', 'notes')
            ->get()
            ->filter(function ($word) use ($user) {
                $history = $word->histories->first();
                return !$history || ($history->next_revision && ($history->next_revision->isPast() || $history->next_revision->isToday()));
            })
            ->shuffle()
            ->values();

        // Limit the number of words per study session (e.g., 20 words)
        $wordsForSession = $wordsToReview->take(20);

        return $this->transformAndRenderStudyWords($wordsForSession);
    }

    /**
     * Display a study session for a specific custom StudySession.
     * @param StudySession $studySession The session to review
     * @param string $mode 'all' or 'failed'
     */
    public function sessionReview(Request $request, StudySession $studySession)
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        $mode = $request->input('mode', 'all');

        $wordsQuery = $studySession->words()->with(['tags', 'histories' => function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        }])->select('words.id', 'words.chinese_word', 'words.pinyin', 'words.translation', 'words.notes'); // Added 'words.notes'

        if ($mode === 'failed') {
            // Simplified "failed" logic: only include words with history AND total_incorrect_revisions > 0
             $wordsQuery = $studySession->words()->with(['tags', 'histories' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
             }])->whereHas('histories', function ($query) use ($request) {
                 $query->where('user_id', $request->user()->id)
                       ->where('total_incorrect_revisions', '>', 0);
             })->select('words.id', 'words.chinese_word', 'words.pinyin', 'words.translation', 'words.notes'); // Added 'words.notes' here too
        }

        $wordsForSession = $wordsQuery->get()->shuffle()->values();

        return $this->transformAndRenderStudyWords($wordsForSession);
    }

    /**
     * Helper method to transform words and render the study Inertia page.
     * This avoids code duplication between autoReviewIndex and sessionReview.
     */
    protected function transformAndRenderStudyWords($wordsForSession)
    {
        $user = auth()->user();

        $transformedWords = $wordsForSession->map(function ($word) use ($user) {
            $history = $word->histories->firstWhere('user_id', $user->id);

            return [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'notes' => $word->notes,
                'tags' => $word->tags->pluck('name')->toArray(),
                'history' => $history ? [
                    'last_revision' => $history->last_revision?->format('M d, Y H:i'),
                    'next_revision' => $history->next_revision?->format('M d, Y H:i'),
                    'revision_interval' => $history->revision_interval,
                    'consecutive_correct_revisions' => $history->consecutive_correct_revisions,
                    'total_incorrect_revisions' => $history->total_incorrect_revisions,
                    'learning_status' => $history->learning_status,
                ] : null,
            ];
        })->toArray();

        return Inertia::render('Study/Index', [
            'wordsForSession' => $transformedWords,
            // 'allTags' => Tag::pluck('name')->toArray(),
        ]);
    }
}