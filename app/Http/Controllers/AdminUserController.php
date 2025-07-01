<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a paginated listing of all users for admin.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Apply role filter (assuming you have a 'roles' relationship or a column)
        // This example assumes you have a many-to-many 'roles' relationship setup
        // If you're using a package like Spatie/Laravel-Permission, this might look slightly different
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Add the count of words for each user
        // Assuming your User model has a 'words' relationship (hasMany(Word::class))
        $query->withCount('words'); // This will add a `words_count` attribute to each User model

        // You might want to order by something here, e.g., created_at or name
        $users = $query->orderBy('name')->paginate(10); // Paginate the results

        // Map the users data for Inertia to pluck roles and format dates
        $users->through(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            // Assuming your user model has a 'roles' relationship that you can pluck names from
            'roles' => $user->roles->pluck('name')->toArray(),
            'created_at' => $user->created_at->format('M d, Y'),
            'words_count' => $user->words_count, // Include the new words_count
        ]);

        // Get all distinct roles for the filter dropdown (if applicable)
        // This assumes you have a Role model or a way to get distinct role names.
        // If using Spatie/Laravel-Permission:
        // $allRoles = \Spatie\Permission\Models\Role::pluck('name')->toArray();
        // Otherwise, you might need to query your users or a dedicated roles table
        $allRoles = ['admin', 'user']; // Placeholder: Replace with actual logic to fetch roles


        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
            'allRoles' => $allRoles, // Ensure this is passed to the frontend
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $allRoles = Role::all()->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
        ]);

        return Inertia::render('Admin/User/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_roles' => $user->roles->pluck('name')->toArray(),
            ],
            'all_roles' => $allRoles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ])->save();

        if (isset($validated['roles'])) {
            // Get the IDs of the roles based on their names
            $selectedRoleIds = Role::whereIn('name', $validated['roles'])->pluck('id');
            // Sync the user's roles: detach roles not in $selectedRoleIds, attach new ones
            $user->roles()->sync($selectedRoleIds);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}