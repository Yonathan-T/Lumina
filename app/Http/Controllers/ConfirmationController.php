<?php

namespace App\Http\Controllers;

use App\Services\PolarBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConfirmationController extends Controller
{
    public function handle(Request $request, PolarBillingService $billing)
    {
        // 1. Get the checkout_id from the URL.
        $checkoutId = $request->query('checkout_id');
        if (! $checkoutId) {
            return redirect('/')->with('error', 'Invalid checkout.');
        }

        try {
            // 2. Make an API call to Polar to fetch the checkout details.
            // This is primarily for displaying some info on the confirmation page.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('POLAR_ACCESS_TOKEN'),
                'Accept' => 'application/json',
            ])->get('https://sandbox-api.polar.sh/v1/checkouts/'.$checkoutId);

            // 3. Handle a failed response, as something went wrong with the API call.
            if ($response->failed()) {
                Log::error('Polar API Error on confirmation', ['checkout_id' => $checkoutId, 'response' => $response->body()]);

                return redirect('/')->with('error', 'An error occurred. Please contact support.');
            }

            $checkout = $response->json();

            if ($request->user()) {
                $billing->syncCheckoutForUser($request->user(), $checkoutId);
            }

            // 4. Redirect the user to a simple "congratulations" page.
            // This is the correct, safe, and reliable way to handle the user's return.
            return view('congratulations', [
                'status' => 'Your payment was successful! Your plan has been refreshed.',
                'checkout' => $checkout,
            ]);

        } catch (\Exception $e) {
            // Log any unexpected exceptions.
            Log::error('Error in ConfirmationController', ['message' => $e->getMessage()]);

            return redirect('/')->with('error', 'An unexpected error occurred. Please contact support.');
        }
    }
}
