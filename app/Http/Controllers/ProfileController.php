<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileInformationUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the user's profile editing form.
     */
    public function edit(Request $request)
    {
        $countriesJsonPath = base_path('resources/js/data/countriesList.json');
        $countriesData = [];

        if (File::exists($countriesJsonPath)) {
            $countriesData = json_decode(File::get($countriesJsonPath), true);
        }

        $languagesStudiedOptions = [
            ['value' => 'simplified_chinese', 'label' => 'Simplified Chinese'],
            ['value' => 'traditional_chinese', 'label' => 'Traditional Chinese'],
            ['value' => 'simplified_and_traditional_chinese', 'label' => 'Simplified and Traditional Chinese'],
        ];

        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only('username', 'first_name', 'last_name', 'country', 'city', 'email', 'native_language', 'chinese_level', 'languages_studied',),
            'countries' => $countriesData,
            'languagesStudiedOptions' => $languagesStudiedOptions,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfileInformation(ProfileInformationUpdateRequest $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'country' => ['required', 'string', 'max:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'native_language' => ['nullable', 'string', 'max:255'],
            'chinese_level' => ['nullable', 'string', 'max:255'],
            'languages_studied' => ['nullable', 'string', 'max:55'],
        ]);

        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('Profile.edit')->with('success', 'Profile information updated successfully!');
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

        Auth::guard('web')->logout();

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