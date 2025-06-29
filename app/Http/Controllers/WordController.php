<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Models\Tag;
use App\Models\History;
use App\Models\StudySession;
use App\Http\Requests\StoreWordRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class WordController extends Controller
{
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
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'pinyin';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        $wordsQuery->orderBy($sortBy, $sortDirection);

        $words = $wordsQuery->paginate(10)->withQueryString();

        $allTags = Tag::whereHas('words', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('name')->toArray();

        return Inertia::render('Words/Index', [
            'words' => $words->through(fn ($word) => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'tags' => $word->tags->pluck('name')->toArray(),
                'created_at' => $word->created_at->format('M d, Y'),
            ]),
            'filters' => $request->only(['search_pinyin', 'search_translation', 'tag', 'sort_by', 'sort_direction']),
            'allTags' => $allTags,
        ]);
    }

    public function dashboardHome()
    {
        $user = Auth::user();
        $totalWords = $user->words()->count();
        $recentWords = $user->words()->latest()->take(5)->get();

        // Placeholder for words due for review
        // TODO Review system
        // $wordsDueForReview = $user->words()->where('next_review_date', '<=', now())->count();
        $wordsDueForReview = 0; // Defaulting to 0 for now

        return Inertia::render('Dashboard', [
            'totalWords' => $totalWords,
            'recentWords' => $recentWords,
            'wordsDueForReview' => $wordsDueForReview,
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
            // && !auth()->user()->hasRole('admin')
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

        } else { // User got it incorrect
            $history->consecutive_correct_revisions = 0; // Reset consecutive
            $history->total_incorrect_revisions++;
            $history->revision_interval = 0; // Reset interval to 0 or 1 day for immediate re-review
            $history->learning_status = 'Forgot';
        }

        $history->last_revision = Carbon::now();
        $history->next_revision = Carbon::now()->addDays($history->revision_interval);

        $history->save();

        return response()->json([
            'message' => 'Study record updated successfully!',
            'history' => $history->toArray(), // Return the updated history data
        ]);
    }
}
