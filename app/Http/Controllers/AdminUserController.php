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
    public function index()
    {
        $users = User::with('roles')
                     ->orderBy('name')
                     ->paginate(10);

        return Inertia::render('Admin/User/Index', [
            'users' => $users->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->format('M d, Y'),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Get all available roles to display checkboxes
        $allRoles = Role::all()->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
        ]);

        return Inertia::render('Admin/User/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'current_roles' => $user->roles->pluck('name')->toArray(), // Array of current role names
            ],
            'all_roles' => $allRoles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validate user's general information
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => 'nullable|array', // 'roles' will be an array of selected role names
            'roles.*' => 'string|exists:roles,name', // Each role name must exist in the 'roles' table
        ]);

        // Update user's name and email
        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ])->save();

        // Handle role updates
        if (isset($validated['roles'])) {
            // Get the IDs of the roles based on their names
            $selectedRoleIds = Role::whereIn('name', $validated['roles'])->pluck('id');
            // Sync the user's roles: detach roles not in $selectedRoleIds, attach new ones
            $user->roles()->sync($selectedRoleIds);
        } else {
            // If no roles are selected (e.g., all checkboxes unchecked), detach all roles
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