<?php
// app/Http/Controllers/InsightsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entry;
use Carbon\Carbon;

class InsightController extends Controller
{
    public function index()
    {
        return view('SecViews.insights');
    }
}