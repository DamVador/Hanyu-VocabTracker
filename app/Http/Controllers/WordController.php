<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Http\Requests\StoreWordRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Tag;
use Illuminate\Validation\Rule;

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

    public function create()
    {
        $allTags = Tag::pluck('name')->toArray();
        return Inertia::render('Words/Create', [
            'allTags' => $allTags,
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

        return redirect()->route('words.index')->with('success', 'Word created successfully!');
    }

    public function edit(Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $allTags = Tag::pluck('name')->toArray();

        return Inertia::render('Words/Edit', [
            'word' => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'current_tags' => $word->tags->pluck('name')->toArray(),
            ],
            'allTags' => $allTags,
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

        return redirect()->route('words.index')->with('success', 'Word updated successfully!');
    }

    public function destroy(Word $word)
    {
        if ($word->user_id !== auth()->id()) {
            // If you have roles/permissions, you might add:
            // && !auth()->user()->hasRole('admin')
            abort(403, 'Unauthorized action. You do not own this word.');
        }

        $word->delete();

        return redirect()->route('words.index')->with('success', 'Word deleted successfully!');
    }
}
