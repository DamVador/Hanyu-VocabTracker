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
        // Get sessions with a count of attached words
        $studySessions = $user->studySessions()->withCount('words')->get();

        return Inertia::render('StudySessions/Index', [
            'studySessions' => $studySessions,
        ]);
    }

    /**
     * Show the form for creating a new study session.
     */
    public function create(Request $request)
    {
        // Pass all user's words so they can be selected for the session
        $userWords = $request->user()->words()
            ->select('id', 'chinese_word', 'pinyin', 'translation') // Optimize by selecting only necessary fields
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
            'word_ids' => 'nullable|array', // Array of word IDs to attach
            'word_ids.*' => 'exists:words,id', // Ensure each ID exists in the words table
        ]);

        $studySession = $request->user()->studySessions()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['word_ids'])) {
            // Attach the words to the newly created session
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
        // Authorize: Ensure the authenticated user owns this session
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load words already attached to this session
        $studySession->load('words:id,chinese_word,pinyin,translation');

        // Pass all user's words for selection, and mark which ones are already attached
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
        // Authorize: Ensure the authenticated user owns this session
        if ($studySession->user_id !== $request->user()->id) {
            abort(403);
        }

        $studySession->delete(); // This will also detach related words due to cascade delete

        return redirect()->route('study-sessions.index')
                         ->with('success', 'Study session deleted successfully!');
    }
}