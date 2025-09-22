<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function handle(Request $request)
    {
        $priceId = $request->query('priceId', '');
        $confirmationUrl = $request->getSchemeAndHttpHost() . '/confirmation?checkout_id={CHECKOUT_ID}';

        $payload = [
            'product_price_id' => $priceId,
            'success_url' => $confirmationUrl,
            'payment_processor' => 'stripe',
        ];

        if (auth()->check()) {
            $payload['customer_email'] = auth()->user()->email;
        }

        $result = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('POLAR_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://sandbox-api.polar.sh/v1/checkouts/custom/', $payload);

        $data = $result->json();

        return redirect($data['url']);
    }
}