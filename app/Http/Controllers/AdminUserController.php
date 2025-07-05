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

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $query->withCount('words'); 

        // You might want to order by something here, e.g., created_at or name
        $users = $query->orderBy('username')->paginate(10);

        $users->through(fn ($user) => [
            'id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'country' => $user->country,
            'city' => $user->city,
            'chinese_level' => $user->chinese_level,
            'native_language' => $user->native_language,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'created_at' => $user->created_at->format('M d, Y'),
            'words_count' => $user->words_count,
        ]);

        $allRoles = ['admin', 'regular', 'premium'];

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
            'user' => $user->only([
                'id', 'first_name', 'last_name', 'username', 'email', 'country', 'city',
            ]) + [ // <--- Corrected: Removed ->toArray() here
                'current_roles' => $user->roles->pluck('name')->toArray(),
            ],
            'all_roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'country' => $request->country,
            'city' => $request->city,
        ]);

        if ($request->has('roles')) {
            $roleIds = Role::whereIn('name', $request->roles)->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}