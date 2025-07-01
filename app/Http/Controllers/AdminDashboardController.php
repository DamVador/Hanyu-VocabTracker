<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Word;
use App\Models\History;
use App\Models\Tag; // Assuming you have a Tag model
use App\Models\StudySession; // Assuming you have a StudySession model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // For raw queries and aggregations

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Existing props
        $totalUsers = User::count();
        $activeUsersCount = User::where('last_seen_at', '>=', Carbon::now()->subWeeks(2))->count();

        // 1. Number of new users for the last 2 weeks
        $newUsersLastTwoWeeks = User::where('created_at', '>=', Carbon::now()->subWeeks(2))->count();

        // 2. Total of study sessions
        // Only count sessions that have an end_time, meaning they were completed
        $totalStudySessions = StudySession::count();

        // 3. Most popular tags (Top 5 for example)
        // This assumes a many-to-many relationship between words and tags
        $mostPopularTags = Tag::select('tags.name', DB::raw('count(tag_word.tag_id) as word_count'))
                               ->join('tag_word', 'tags.id', '=', 'tag_word.tag_id')
                               ->groupBy('tags.id', 'tags.name') // Group by ID and name
                               ->orderByDesc('word_count')
                               ->take(5)
                               ->get();

        // 4. Average words per user
        // Calculate total words, then total users, and divide. Handle division by zero.
        $totalWordsInApp = Word::count();
        $averageWordsPerUser = ($totalUsers > 0) ? round($totalWordsInApp / $totalUsers, 1) : 0;


        // 5. Retention Rate (users active after 1 week/1 month)
        // This is a more complex calculation often done via cohort analysis.
        // For simplicity, let's calculate for users created in a specific month
        // and see how many were active 1 week/1 month later.

        // Example: Users created 1 month ago (July 2024 cohort for a look back from July 2025)
        // Adjust these dates as needed for your analysis period.
        $cohortStartDate = Carbon::now()->subMonths(1)->startOfMonth(); // June 1, 2025
        $cohortEndDate = Carbon::now()->subMonths(1)->endOfMonth();   // June 30, 2025

        $cohortUsers = User::whereBetween('created_at', [$cohortStartDate, $cohortEndDate])->count();

        $retention1Week = 0;
        $retention1Month = 0;

        if ($cohortUsers > 0) {
            // Users from the cohort who were active between 1 week and 2 weeks after their creation
            $retained1Week = User::whereBetween('created_at', [$cohortStartDate, $cohortEndDate])
                                 ->where('last_seen_at', '>=', $cohortStartDate->copy()->addWeek())
                                 ->where('last_seen_at', '<', $cohortStartDate->copy()->addWeeks(2))
                                 ->count();
            $retention1Week = round(($retained1Week / $cohortUsers) * 100, 1);

            // Users from the cohort who were active between 1 month and 2 months after their creation
            $retained1Month = User::whereBetween('created_at', [$cohortStartDate, $cohortEndDate])
                                  ->where('last_seen_at', '>=', $cohortStartDate->copy()->addMonth())
                                  ->where('last_seen_at', '<', $cohortStartDate->copy()->addMonths(2))
                                  ->count();
            $retention1Month = round(($retained1Month / $cohortUsers) * 100, 1);
        }

        // 6. Top 15 Most Failed Words
        // This assumes 'total_incorrect_revisions' is a column in your 'histories' table.
        // Or you can sum up 'correct' = 0 entries for each word.
        $topFailedWords = History::select('words.chinese_word', 'words.pinyin', DB::raw('SUM(histories.total_incorrect_revisions) as incorrect_attempts_count'))
                            ->join('words', 'histories.word_id', '=', 'words.id')
                            ->groupBy('words.id', 'words.chinese_word', 'words.pinyin')
                            ->orderByDesc('incorrect_attempts_count')
                            ->take(15)
                            ->get();

        return Inertia::render('Admin/Dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsersCount' => $activeUsersCount,
            'newUsersLastTwoWeeks' => $newUsersLastTwoWeeks,
            'totalStudySessions' => $totalStudySessions,
            'mostPopularTags' => $mostPopularTags,
            'averageWordsPerUser' => $averageWordsPerUser,
            'retention1Week' => $retention1Week,
            'retention1Month' => $retention1Month,
            'topFailedWords' => $topFailedWords,
            // ... any other existing props
        ]);
    }
}