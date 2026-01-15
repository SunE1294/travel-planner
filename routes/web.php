<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/generate-plan', [TravelController::class, 'getPlan']);
