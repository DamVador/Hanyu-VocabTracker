<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;
use App\Http\Requests\StoreWordRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WordController extends Controller
{
    public function index2()
    {
        $words = Word::all();

        return inertia('Words/Index2', compact('words'));
    }
    
    public function index(Request $request)
    {
        $words = $request->user()->words()
                         ->latest()
                         ->paginate(10);

        return Inertia::render('Words/Index', [
            'words' => $words->through(fn ($word) => [
                'id' => $word->id,
                'chinese_character' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'tags' => $word->tags,
                'created_at' => $word->created_at->format('M d, Y'),
            ]),
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
        return Inertia::render('Words/Create');
    }

    public function save(StoreWordRequest $request)
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        $word = new Word();

        $word->chinese_word = $validatedData['chinese_word'];
        $word->pinyin = $validatedData['pinyin'];
        $word->translation = $validatedData['translation'];

        $existingWord = $user->words()
                            ->where('chinese_word', $word->chinese_word)
                            ->where('pinyin', $word->pinyin)
                             ->first();

        if ($existingWord) {
            return redirect()->back()->with('error', 'This word ("' . $validatedData['chinese_word'] . '") has already been added to your vocabulary!');
        }

        $word->user_id = Auth::id();

        $word->tags = $validatedData['tags'] ?? [];

        $word->save();

        return redirect()->route('dashboard')->with('success', 'Word added successfully!');

        // To redirect back to the form page:
        // return redirect()->back()->with('success', 'Word added successfully!');
    }

}
