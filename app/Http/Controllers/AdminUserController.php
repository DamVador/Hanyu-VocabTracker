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
        $filterRole = $request->input('role'); // Role name from filter

        $usersQuery = User::with('roles')->orderBy('name');

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($filterRole) {
            $usersQuery->whereHas('roles', function ($query) use ($filterRole) {
                $query->where('name', $filterRole);
            });
        }

        $users = $usersQuery->paginate(10)->withQueryString();

        $allRoles = Role::select('name')->get()->pluck('name')->toArray();

        return Inertia::render('Admin/User/Index', [
            'users' => $users->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->format('M d, Y'),
            ]),
            'filters' => $request->only(['search', 'role']),
            'allRoles' => $allRoles,
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