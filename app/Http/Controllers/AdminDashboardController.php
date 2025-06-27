<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $totalUsers = User::count();
        $totalWordsInSystem = Word::count();
        // Add more admin-specific data as needed, e.g., recent registrations, unreviewed words (if applicable)

        return Inertia::render('Admin/Dashboard', [
            'totalUsers' => $totalUsers,
            'totalWordsInSystem' => $totalWordsInSystem,
        ]);
    }
}