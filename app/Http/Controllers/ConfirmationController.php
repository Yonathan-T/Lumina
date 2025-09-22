<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plan;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ConfirmationController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('POLAR_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get('https://sandbox-api.polar.sh/v1/checkouts/custom/' . $request->query('checkout_id'));

            // Log the raw response for debugging
            \Log::debug('Polar API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->failed()) {
                $errorResponse = $response->json();
                $errorMessage = $errorResponse['message'] ?? 'Unknown error occurred';

                \Log::error('Polar API Error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'full_response' => $errorResponse
                ]);

                // Handle specific error cases
                if (str_contains(strtolower($errorMessage), 'already have an active subscription')) {
                    // Check if we can find the existing subscription
                    $email = $request->query('email') ?? $request->user()?->email;
                    if ($email) {
                        $user = User::where('email', $email)->first();
                        if ($user) {
                            return redirect()->route('login')
                                ->with('status', 'You already have an active subscription. Please log in to continue.');
                        } else {
                            // If user doesn't exist locally but has a subscription, we need to handle this case
                            return redirect()->route('register')
                                ->withInput($request->only('email'))
                                ->with('error', 'An account with this email already has an active subscription. Please register with a different email or contact support.');
                        }
                    }
                }

                return redirect('/')
                    ->with('error', 'Failed to process subscription: ' . $errorMessage);
            }

            $checkout = $response->json();

            // Log the parsed checkout data
            \Log::info('Checkout data received', [
                'status' => $checkout['status'] ?? null,
                'customer_email' => $checkout['customer_email'] ?? null,
                'customer_id' => $checkout['customer_id'] ?? null,
                'subscription_id' => $checkout['subscription_id'] ?? null,
                'product_name' => $checkout['product']['name'] ?? null,
                'checkout_id' => $checkout['id'] ?? null
            ]);

            $customerEmail = $checkout['customer_email'] ?? null;
            if (!$customerEmail) {
                // Log error or handle missing email
                \Log::error('No customer email found in checkout', ['checkout' => $checkout]);
                return redirect('/')->with('error', 'No email found in checkout details. Please contact support.');
            }

            // Get the product name from the checkout
            $productName = $checkout['product']['name'] ?? null;
            if (!$productName) {
                \Log::error('No product name found in checkout', ['checkout' => $checkout]);
                return redirect('/')->with('error', 'No product information found in checkout. Please contact support.');
            }

            // Find the corresponding plan in your database
            $plan = Plan::where('name', $productName)->first();

            if (!$plan) {
                \Log::error('Plan not found', ['product_name' => $productName]);
                return redirect('/')->with('error', 'Selected plan not found. Please contact support.');
            }

            // Get the customer name from checkout, or use the part before @ in email as a fallback
            $customerName = $checkout['customer_name'] ??
                $checkout['customer']['name'] ??
                $user->name ??
                explode('@', $customerEmail)[0];

            // Find existing user or create a new one
            $user = User::firstOrNew(['email' => $customerEmail]);
            $isNewUser = !$user->exists;

            // Update or set user details with the customer's name
            $user->name = $customerName;
            $user->plan_id = $plan->id;
            $user->polar_customer_id = $checkout['customer_id'];
            $user->subscription_id = $checkout['subscription_id'];

            // Set subscription end date
            $interval = $checkout['product']['recurring_interval'] ?? 'month';
            $user->subscription_ends_at = $interval === 'year'
                ? Carbon::now()->addYear()
                : Carbon::now()->addMonth();

            // For new users, generate a password reset token and send welcome email
            if ($isNewUser) {
                // Generate a random password that will be changed by the user
                $temporaryPassword = Str::random(32);
                $user->password = bcrypt($temporaryPassword);
                $user->email_verified_at = null; // Mark email as unverified
                $user->save();

                // Generate a password reset token using the database token repository
                $token = Password::getRepository()->create($user);

                // Queue the welcome email with password reset link
                Mail::to($user->email)->queue(new WelcomeEmail($user, $token));

                // Log the user in
                Auth::login($user);

                // Set session data
                $request->session()->put('show_email_verification_notice', true);

                // Log this for tracking
                \Log::info('New user signed up and welcome email sent', ['user_id' => $user->id]);

                // Redirect to the dashboard with a welcome message and modal flag
                return redirect()->route('dashboard')
                    ->with('status', 'Welcome to ' . config('app.name') . '! Please check your email to verify your account and set a password.')
                    ->with('show_welcome_modal', true);
            } else {
                $user->save();
                // Log the user in (in case they're not already)
                Auth::login($user);

                return redirect()->route('dashboard')
                    ->with('status', 'Your subscription has been updated successfully!');
            }
        } catch (\Exception $e) {
            \Log::error('Error in ConfirmationController: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'An error occurred while processing your request. Please try again or contact support.');
        }
    }
}