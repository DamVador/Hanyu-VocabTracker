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

class StudySessionWordController extends Controller
{
    public function index(Request $request, StudySession $study_session): Response
    {
        if ($study_session->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $query = Word::query();

        $query->join('study_session_word', 'words.id', '=', 'study_session_word.word_id')
              ->where('study_session_word.study_session_id', $study_session->id);

        $query->with('tags');
        $query->with(['histories' => function($q) {
            $q->where('user_id', auth()->id())->latest();
        }]);

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

        $baseSelectColumns = [
            'words.id',
            'words.chinese_word',
            'words.pinyin',
            'words.translation',
            'words.created_at',
            'words.updated_at',
        ];

        if ($sortBy === 'failure_count') {
            $query->leftJoin('histories', function ($join) use ($request) {
                $join->on('words.id', '=', 'histories.word_id')
                     ->where('histories.user_id', '=', $request->user()->id);
            })
            ->select($baseSelectColumns)
            ->addSelect(DB::raw('SUM(histories.total_incorrect_revisions) as total_failures'))
            ->groupBy(array_merge($baseSelectColumns, [
                'study_session_word.study_session_id',
                'study_session_word.word_id'
            ]))
            ->orderBy('total_failures', $sortDirection);

        } elseif ($sortBy === 'learning_status') {
            $query->select($baseSelectColumns)
                ->addSelect('latest_history.learning_status as latest_history_status_learning_status')
                ->leftJoinSub(
                    DB::table('histories')
                        ->select('word_id', DB::raw('MAX(created_at) as max_created_at'))
                        ->where('user_id', $request->user()->id)
                        ->groupBy('word_id'),
                    'latest_history_max_date',
                    function ($join) {
                        $join->on('words.id', '=', 'latest_history_max_date.word_id');
                    }
                )
                ->leftJoin('histories as latest_history', function ($join) use ($request) {
                    $join->on('latest_history.word_id', '=', 'words.id')
                        ->on('latest_history.created_at', '=', 'latest_history_max_date.max_created_at')
                        ->where('latest_history.user_id', '=', $request->user()->id);
                })
                ->groupBy(array_merge($baseSelectColumns, [
                    'study_session_word.study_session_id',
                    'study_session_word.word_id',
                    'latest_history.learning_status'
                ]))
                ->orderBy('latest_history_status_learning_status', $sortDirection);
        } else {
            $query->select(array_merge($baseSelectColumns, [
                'study_session_word.study_session_id',
                'study_session_word.word_id'
            ]));
            $query->orderBy('words.' . $sortBy, $sortDirection);
        }

        $sessionWords = $query->paginate(10);

        return Inertia::render('StudySessions/Words', [
            'studySession' => $study_session->only('id', 'name'),
            'sessionWords' => $sessionWords->through(fn ($word) => [
                'id' => $word->id,
                'chinese_word' => $word->chinese_word,
                'pinyin' => $word->pinyin,
                'translation' => $word->translation,
                'tags' => $word->tags->pluck('name')->toArray(),
                'failure_count' => $word->total_failures ?? $word->histories->where('user_id', auth()->id())->sum('total_incorrect_revisions'),
                'learning_status' => $word->latest_history_status_learning_status ?? ($word->histories->where('user_id', auth()->id())->sortByDesc('created_at')->first()->learning_status ?? 'New'),
            ]),
            'filters' => array_merge(
                $request->only(['pinyin', 'translation']),
                compact('sortBy', 'sortDirection')
            ),
            'allStatuses' => ['New', 'Failed', 'Revise', 'Mastered'],
        ]);
    }
}