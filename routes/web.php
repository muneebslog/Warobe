<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('wardrobe', 'pages.wardrobe')
    ->middleware(['auth', 'verified'])
    ->name('wardrobe');

Route::view('wardrobe/create', 'pages.wardrobe-create')
    ->middleware(['auth', 'verified'])
    ->name('wardrobe.create');

Route::view('outfits/suggest', 'pages.outfits-suggest')
    ->middleware(['auth', 'verified'])
    ->name('outfits.suggest');

Route::view('calendar', 'pages.calendar')
    ->middleware(['auth', 'verified'])
    ->name('calendar');

require __DIR__.'/settings.php';
