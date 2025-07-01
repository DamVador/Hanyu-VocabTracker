<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {

        if (! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized access.'); // TODO redirect to dashboard
        }

        $twoWeeksAgo = Carbon::now()->subWeeks(2);

        $activeUsersCount = User::whereNotNull('last_seen_at')
                                ->where('last_seen_at', '>=', $twoWeeksAgo)
                                ->count();

        $totalUsers = User::count();
        $totalWordsInSystem = Word::count();
        // Add more admin-specific data as needed, e.g., recent registrations, unreviewed words (if applicable)

        return Inertia::render('Admin/Dashboard', [
            'activeUsersCount' => $activeUsersCount,
            'totalUsers' => $totalUsers,
            'totalWordsInSystem' => $totalWordsInSystem,
        ]);
    }
}