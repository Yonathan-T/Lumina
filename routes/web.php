<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing-page');
});
Route::get('/dashboard', function () {
    return view('entries.index');
});
Route::get('/register',[RegisteredUserController::class ,'create']);
Route::post('/register',[RegisteredUserController::class ,'store']);

Route::get('/login',[SessionController::class ,'create'])->name('login');
Route::post('/login',[SessionController::class ,'store']);
