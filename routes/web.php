<?php

use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/properties');

Route::resource('properties', PropertyController::class);
