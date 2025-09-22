<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductsController extends Controller
{
    public static function fetchProducts()
    {
        $apiKey = env('POLAR_ACCESS_TOKEN');

        if (!$apiKey) {

            return [];
        }

        $response = Http::withToken($apiKey)->get('https://sandbox-api.polar.sh/v1/products', [
            'is_archived' => false,
        ]);

        if ($response->ok()) {
            $data = $response->json();
            return $data['items'] ?? [];
        }

        return [];
    }

    public function handle(Request $request)
    {
        $products = self::fetchProducts();
        return view('pricing', ['products' => $products]);
    }
}