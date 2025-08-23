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
use Illuminate\Support\Collection;
use App\Models\StatisticsSnapshot;

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

        // Limit the number of words per study session
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
        $user = $request->user();

        $wordsQuery = $studySession->words()
            ->with(['tags'])
            ->with(['latestHistory' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->select('words.id', 'words.chinese_word', 'words.pinyin', 'words.translation', 'words.notes');

        if ($mode === 'auto') {
            $wordsQuery->inRandomOrder()->limit(20);
        } elseif ($mode === 'new') {
            $wordsQuery->whereDoesntHave('histories', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        } elseif ($mode === 'failed') {
            $wordsQuery->whereHas('histories', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('learning_status', 'Forgot');
            });
        }
        
        $wordsForSession = $wordsQuery->get()->shuffle()->values();

        return $this->transformAndRenderStudyWords($wordsForSession);
    }
    /**
     * Helper method to transform words and render the study Inertia page.
     * This avoids code duplication between autoReviewIndex and sessionReview.
     */
    private function transformAndRenderStudyWords(Collection $words): \Inertia\Response
    {
        $words->transform(function ($word) {
            $history = optional($word->latestHistory)->toArray();
            if ($history) {
                $history['learning_status'] = $history['learning_status'] ?? 'New';
            }
            return [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'tags' => $word->tags->pluck('name'),
                'notes' => $word->notes,
                'history' => $history,
            ];
        });

        return Inertia::render('Study/Index', [
            'wordsForSession' => $words,
            'allTags' => [],
        ]);
    }
}