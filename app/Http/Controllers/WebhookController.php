<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Event;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    /**
     * Webhook Stripe.
     */
    public function handle(Request $request)
    {
        Log::info('Stripe Webhook: Request received.');

        $payload = @file_get_contents('php://input');
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
            Log::info('Stripe Webhook: Signature verified successfully.');
        } catch (UnexpectedValueException $e) {
            Log::error('Stripe Webhook Error: Invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe Webhook Error: Invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                if ($session->payment_status !== 'paid') {
                    Log::info('Stripe Webhook: Checkout session is not paid, ignoring event.');
                    return response('Not paid', 200);
                }

                $customerEmail = $session->customer_details->email ?? null;
                $stripeCustomerId = $session->customer ?? null;

                Log::info("--- DEBUG: checkout.session.completed ---");
                Log::info("Stripe Webhook: Customer Email from session: {$customerEmail}");
                Log::info("Stripe Webhook: Stripe Customer ID from session: {$stripeCustomerId}");

                if (!$customerEmail || !$stripeCustomerId) {
                    Log::error('Stripe Webhook Error: Missing customer email or ID in session data.');
                    return response('Missing data', 400);
                }
                
                $user = User::where('email', $customerEmail)->first();
                $premiumRole = Role::where('name', 'premium')->first();

                Log::info('Stripe Webhook: User found by email? ' . ($user ? 'Yes' : 'No'));
                Log::info('Stripe Webhook: Premium role found? ' . ($premiumRole ? 'Yes' : 'No'));

                if ($user && $premiumRole) {
                    try {
                        Log::info("Stripe Webhook: User's current Stripe ID: " . ($user->stripe_id ?? 'not set'));
                        Log::info("Stripe Webhook: Updating user's Stripe ID to: {$stripeCustomerId}");

                        $user->update([
                            'stripe_id' => $stripeCustomerId,
                        ]);

                        $user->roles()->sync([$premiumRole->id]);

                        Log::info("Stripe Webhook: Successfully updated Stripe ID and role for user: {$user->email}");
                    } catch (\Exception $e) {
                        Log::error("Stripe Webhook Error: Failed to update user '{$customerEmail}': " . $e->getMessage());
                        return response('Update failed', 500);
                    }
                } else {
                    Log::error("Stripe Webhook Error: Cannot update user. User or premium role not found during checkout.");
                    return response('User or role not found', 400);
                }
                break;
            case 'customer.subscription.created':
                $subscription = $event->data->object;
                $subscriptionId = $subscription->id;

                Log::info("--- DEBUG: customer.subscription.created ---");
                Log::info("Stripe Webhook: Subscription ID for new subscription: {$subscriptionId}");

                break;
            case 'customer.subscription.deleted': 
                $subscription = $event->data->object;
                $customerStripeId = $subscription->customer ?? null;
                
                Log::info("--- DEBUG: customer.subscription.deleted ---");
                Log::info("Stripe Webhook: Customer Stripe ID from subscription: {$customerStripeId}");

                if ($customerStripeId) {
                    $user = User::where('stripe_id', $customerStripeId)->first();
                    $basicRole = Role::where('name', 'regular')->first();

                    Log::info('Stripe Webhook: User found by Stripe ID? ' . ($user ? 'Yes' : 'No'));
                    Log::info('Stripe Webhook: Regular role found? ' . ($basicRole ? 'Yes' : 'No'));
                    
                    if ($user && $basicRole) {
                        try {
                            $user->roles()->sync([$basicRole->id]);
                            Log::info("Stripe Webhook: Successfully reverted user to regular role: {$user->email}");
                        } catch (\Exception $e) {
                            Log::error("Stripe Webhook Error: Failed to revert user to regular role '{$user->email}': " . $e->getMessage());
                            return response('Update failed', 500);
                        }
                    } else {
                        Log::error("Stripe Webhook Error: User or regular role not found for subscription deletion.");
                        return response('User or regular role not found', 404);
                    }
                } else {
                    Log::error('Stripe Webhook Error: Missing customer ID in subscription deleted event.');
                    return response('Missing data', 400);
                }
                break;
            default:
                Log::info("Stripe Webhook: Received unhandled event: {$event->type}");
                break;
        }

        return response('Webhook Handled', 200);
    }
}
