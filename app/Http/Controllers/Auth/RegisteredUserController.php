<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\File;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $countriesJsonPath = base_path('resources/js/data/countriesList.json');
        $countriesData = [];

        if (File::exists($countriesJsonPath)) {
            $countriesData = json_decode(File::get($countriesJsonPath), true);
        }

        return Inertia::render('auth/Register', [
            'countries' => $countriesData,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'country' => ['required', 'string', 'max:2'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'country' => $request->country,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // TODO - create an observer for users and register it in service provider app/Providers/AppServiceProvider.php
        $regularRole = Role::firstOrCreate(['name' => 'regular']);
        $user->roles()->attach($regularRole->id);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
