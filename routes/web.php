<?php

use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/properties');

Route::get('/geocode/search', [GeocodeController::class, 'search'])->name('geocode.search');
Route::get('/geocode/reverse', [GeocodeController::class, 'reverse'])->name('geocode.reverse');

Route::resource('properties', PropertyController::class);

Route::resource('simulations', SimulationController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'destroy',
]);
