<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudySessionController extends Controller
{
    /**
     * Display a listing of the study sessions.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $search = $request->input('search');

        $query = $user->studySessions();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy('name', 'asc');

        $studySessions = $query->withCount('words')->get()->map(fn ($session) => [
            'id' => $session->id,
            'name' => $session->name,
            'description' => $session->description,
            'words_count' => $session->words_count,
            'created_at' => $session->created_at->format('M d, Y'),
        ]);

        return Inertia::render('StudySessions/Index', [
            'studySessions' => $studySessions,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new study session.
     */
    public function create(Request $request)
    {
        $userWords = $request->user()->words()
            ->select('id', 'chinese_word', 'pinyin', 'translation')
            ->orderBy('pinyin')
            ->get();

        return Inertia::render('StudySessions/Create', [
            'userWords' => $userWords,
        ]);
    }

    /**
     * Store a newly created study session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'word_ids' => 'nullable|array',
            'word_ids.*' => 'exists:words,id', // Ensure each ID exists in the words table
        ]);

        $studySession = $request->user()->studySessions()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['word_ids'])) {
            $studySession->words()->attach($validated['word_ids']);
        }

        return redirect()->route('study-sessions.index')
                         ->with('success', 'Study session created successfully!');
    }

    /**
     * Show the form for editing the specified study session.
     */
    public function edit(Request $request, StudySession $studySession)
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        $studySession->load('words:id,chinese_word,pinyin,translation');

        $userWords = $request->user()->words()
            ->select('id', 'chinese_word', 'pinyin', 'translation')
            ->orderBy('pinyin')
            ->get()
            ->map(function($word) use ($studySession) {
                // Add a flag to indicate if the word is already attached to this session
                $word->is_attached = $studySession->words->contains($word->id);
                return $word;
            });

        return Inertia::render('StudySessions/Edit', [
            'studySession' => $studySession,
            'userWords' => $userWords,
        ]);
    }

    /**
     * Update the specified study session in storage.
     */
    public function update(Request $request, StudySession $studySession)
    {
        // Authorize: Ensure the authenticated user owns this session
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'word_ids' => 'nullable|array',
            'word_ids.*' => 'exists:words,id',
        ]);

        $studySession->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Sync words: This method intelligently attaches/detaches words
        // based on the provided array, ensuring only words in the array are attached.
        $studySession->words()->sync($validated['word_ids'] ?? []);

        return redirect()->route('study-sessions.index')
                         ->with('success', 'Study session updated successfully!');
    }

    /**
     * Remove the specified study session from storage.
     */
    public function destroy(Request $request, StudySession $studySession)
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        $studySession->delete();

        return redirect()->route('study-sessions.index')
                         ->with('success', 'Study session deleted successfully!');
    }
}