<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

use Stripe\StripeClient;
use Stripe\Stripe;
use Stripe\BillingPortal\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        Log::info('Page d\'abonnement chargée.');

        $isPremium = false;

        if ($user) {
            $user->load('roles');

            $isPremium = $user->roles->contains(function ($role) {
                return $role->name === 'premium';
            });
        }

        return Inertia::render('Subscription/Create', [
            'stripeKey' => config('cashier.key'),
            'isPremium' => $isPremium,
        ]);
    }

    public function success(Request $request)
    {
        $user = $request->user();
        $premiumRole = Role::where('name', 'premium')->first();

        if ($user && $premiumRole) {
            $user->roles()->sync([$premiumRole->id]);
        }

        return Inertia::render('Subscription/Success', [
            'auth' => [
                'user' => array_merge(Auth::user()->toArray(), [
                    'is_premium' => true,
                ])
            ]
        ]);
    }

    public function cancel()
    {
        return Inertia::render('Subscription/Cancel');
    }

    public function billingPortal(Request $request)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $user = $request->user();

        if (!$user->stripe_id) {
            return redirect()->route('subscription.index')->with('error', 'Unable to find your Stripe customer ID.');
        }

        $session = Session::create([
            'customer' => $user->stripe_id,
            'return_url' => route('subscription.index'),
        ]);

        return redirect()->to($session->url);
    }
}