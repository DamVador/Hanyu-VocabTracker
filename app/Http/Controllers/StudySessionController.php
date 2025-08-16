<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;

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

        $studySessions = $query->withCount('words')
            ->with(['words' => function ($wordQuery) use ($user) {
                $wordQuery->select('words.id');

                $wordQuery->with(['latestHistory' => function ($historyQuery) use ($user) {
                    $historyQuery->where('user_id', $user->id);
                }]);
            }])
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->through(function ($session) use ($user) {
                $revisedWordsCount = $session->words->filter(function ($word) {
                    return optional($word->latestHistory)->learning_status === 'Revise';
                })->count();
                
                $completionPercentage = $session->words_count > 0 
                                      ? round(($revisedWordsCount / $session->words_count) * 100) 
                                      : 0;

                return [
                    'id' => $session->id,
                    'name' => $session->name,
                    'description' => $session->description,
                    'words_count' => $session->words_count,
                    'completion_percentage' => $completionPercentage,
                    'created_at' => $session->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('StudySessions/Index', [
            'studySessions' => $studySessions,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new study session.
     */
    public function create(Request $request)
    {
        $freeSessionLimit = 5;
        $user = auth()->user();
        $isPremium = $user && $user->hasRole('premium');
        $currentSessionCount = StudySession::where('user_id', $user->id)->count();

        if (!$isPremium && $currentSessionCount >= $freeSessionLimit) {
            return Redirect::route('subscription.index')->with('error', 'You have reached the study session limit for free users. Please upgrade to premium to create more sessions.');
        }

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
        $freeSessionLimit = 5;
        $user = auth()->user();
        $isPremium = $user && $user->hasRole('premium');
        $currentSessionCount = StudySession::where('user_id', $user->id)->count();

        if (!$isPremium && $currentSessionCount >= $freeSessionLimit) {
            return Redirect::route('subscription.index')->with('error', 'You have reached the study session limit for free users. Please upgrade to premium to create more sessions.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'word_ids' => 'nullable|array',
            'word_ids.*' => 'exists:words,id',
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

    /**
     * Helper method to generate CSV content and send it as a streamed response.
     *
     * @param  \Illuminate\Support\Collection  $sessions
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateCsvResponse($sessions, $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sessions) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility with Chinese characters
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Define the CSV header
            fputcsv($file, ['chinese_character', 'pinyin', 'translation', 'study_session_name', 'tags', 'notes']);

            foreach ($sessions as $session) {
                $session->loadMissing('words');

                foreach ($session->words as $word) {
                    $tags = $word->tags->pluck('name')->implode(', ');
                    fputcsv($file, [
                        $word->chinese_word,
                        $word->pinyin,
                        $word->translation,
                        $session->name,
                        $tags,
                        $word->notes,
                    ]);
                }
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Export a single study session and its words to a CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudySession  $studySession
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportSingleCsv(Request $request, StudySession $studySession): StreamedResponse
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $sessionsToExport = collect([$studySession]);

        $filename = 'hanyu_vocab_export_' . Str::slug($studySession->name) . '_' . now()->format('Ymd_His') . '.csv';

        return $this->generateCsvResponse($sessionsToExport, $filename);
    }

    /**
     * Display the study session review page.
     */
    public function review(Request $request, StudySession $studySession, string $mode = 'all')
    {
        if ($studySession->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to study session.');
        }

        $wordsQuery = $studySession->words();

        $wordsQuery->with('history');

        if ($mode === 'failed') {
            $wordsQuery->whereHas('history', function ($q) {
                $q->where('learning_status', 'Forgot');
            });
        } elseif ($mode === 'revise') {
            $wordsQuery->whereHas('history', function ($q) {
                $q->whereIn('learning_status', ['Revise', 'New']);
            });
        } elseif ($mode === 'mastered') {
            $wordsQuery->whereHas('history', function ($q) {
                $q->where('learning_status', 'Mastered');
            });
        }

        $wordsForSession = $wordsQuery->select('words.id', 'words.chinese_word', 'words.pinyin', 'words.translation', 'words.notes')->get();

        return Inertia::render('Study/Index', [
            'wordsForSession' => $wordsForSession,
            'allTags' => $request->user()->tags()->select('id', 'name')->get(),
            'mode' => $mode,
        ]);
    }
}
