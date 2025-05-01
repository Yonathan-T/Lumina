<?php

use App\Http\Controllers\EntryController;
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

//entries need middleware ofc.
Route::get('/entries',[EntryController::class ,'create']);
Route::post('/entries',[EntryController::class ,'store'])->middleware('auth');

Route::get('/entry/edit',[EntryController::class , 'edit'])->middleware('auth');

