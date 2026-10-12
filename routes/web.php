<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReverseGeocodeController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/properties');

Route::get('/geocode/reverse', ReverseGeocodeController::class)->name('geocode.reverse');

Route::resource('properties', PropertyController::class);

Route::resource('simulations', SimulationController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'destroy',
]);
