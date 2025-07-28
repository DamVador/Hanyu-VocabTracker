<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Models\History;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class StudySessionWordController extends Controller
{
    public function index(Request $request, StudySession $study_session): Response
    {
        if ($study_session->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $user = $request->user();

        $query = Word::query()
            ->join('study_session_word', 'words.id', '=', 'study_session_word.word_id')
            ->where('study_session_word.study_session_id', $study_session->id)
            ->select('words.*');

        if ($request->has('pinyin') && $request->input('pinyin')) {
            $query->where('words.pinyin', 'like', '%' . $request->input('pinyin') . '%');
        }

        if ($request->has('translation') && $request->input('translation')) {
            $query->where('words.translation', 'like', '%' . $request->input('translation') . '%');
        }

        $sortBy = $request->input('sort_by', 'failure_count');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSortBy = ['pinyin', 'translation', 'chinese_word', 'failure_count', 'created_at', 'learning_status'];
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'failure_count';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $totalFailuresSubquery = History::select(DB::raw('SUM(total_incorrect_revisions)'))
            ->whereColumn('word_id', 'words.id')
            ->where('user_id', $user->id);

        $query->addSelect(['total_failures' => $totalFailuresSubquery]);

        $latestLearningStatusSubquery = History::select('learning_status')
            ->whereColumn('word_id', 'words.id')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(1);

        $query->addSelect(['current_learning_status' => $latestLearningStatusSubquery]);

        $lastRevisionDateSubquery = History::select('created_at')
            ->whereColumn('word_id', 'words.id')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(1);

        $query->addSelect(['last_revision_date_raw' => $lastRevisionDateSubquery]);

        if ($sortBy === 'failure_count') {
            $query->orderByRaw('(' . $totalFailuresSubquery->toSql() . ') ' . $sortDirection, $totalFailuresSubquery->getBindings());
        } elseif ($sortBy === 'learning_status') {
            $query->orderByRaw('(' . $latestLearningStatusSubquery->toSql() . ') ' . $sortDirection, $latestLearningStatusSubquery->getBindings());
        } elseif (in_array($sortBy, ['chinese_word', 'pinyin', 'translation', 'created_at'])) {
            $query->orderBy('words.' . $sortBy, $sortDirection);
        }

        $sessionWords = $query->with('tags')->paginate(10)->withQueryString();

        return Inertia::render('StudySessions/Words', [
            'studySession' => $study_session->only('id', 'name'),
            'sessionWords' => $sessionWords->through(fn ($word) => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'tags' => $word->tags->pluck('name')->toArray(),
                'failure_count' => $word->total_failures ?? 0,
                'learning_status' => $word->current_learning_status ?? 'New',
                'last_revision_date' => $word->last_revision_date_raw ? Carbon::parse($word->last_revision_date_raw)->format('M d, Y') : 'Never',
            ]),
            'filters' => array_merge(
                $request->only(['pinyin', 'translation']),
                [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ]
            ),
            'allStatuses' => ['New', 'Failed', 'Revise', 'Mastered'],
        ]);
    }

    public function detachWord(StudySession $study_session, Word $word): RedirectResponse
    {
        if ($study_session->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $study_session->words()->detach($word->id);

        return back()->with('success', 'Word successfully removed from this session.');
    }
}