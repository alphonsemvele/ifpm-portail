<?php

use Illuminate\Support\Facades\Route;
use Spatie\RouteDiscovery\Discovery\Discover;

Discover::controllers()->in(app_path('Http/Controllers'));

Route::view('/', 'index');
Route::view('/ism', 'ism')->name('ism');
Route::view('/ifpm', 'ifpm')->name('ifpm');


Route::post('/logout', ['App\Http\Controllers\AuthController', 'logout'])->name('logout');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

// require __DIR__.'/auth.php';
