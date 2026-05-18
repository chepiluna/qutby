<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/logout', function () {
    $role = Auth::user()?->role;
    
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    
    return match($role) {
        'finance' => redirect('/finance/login'),
        default   => redirect('/sales/login'),
    };
})->name('logout')->middleware('web');