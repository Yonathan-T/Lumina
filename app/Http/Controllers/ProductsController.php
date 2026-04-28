<?php

namespace App\Http\Controllers;

use App\Services\PolarBillingService;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public static function fetchProducts()
    {
        return app(PolarBillingService::class)->fetchProducts();
    }

    public function handle(Request $request)
    {
        $products = self::fetchProducts();

        return view('pricing', ['products' => $products]);
    }
}
