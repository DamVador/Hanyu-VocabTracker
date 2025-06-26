<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileInformationUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the user's profile editing form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only('name', 'email'),
            'status' => session('status'), // For password update success messages
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfileInformation(ProfileInformationUpdateRequest $request)
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => __('The provided password does not match your current password.'),
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        Auth::guard('web')->logout(); // Log user out after password change for security

        return redirect('/login')->with('status', 'Your password has been updated. Please log in again.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted successfully!');
    }
}