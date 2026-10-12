<?php

use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/location',[LocationController::class, 'createLocation']);
Route::get('/locations',[LocationController::class, 'readAllLocations']);
Route::get('/location/{id}',[LocationController::class, 'readLocation']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
