<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/locations/{centralLocation}/distance', [\App\Http\Controllers\LocationController::class, 'calculateDistance']);

Route::get('/locations', [\App\Http\Controllers\LocationController::class, 'index']);

Route::post('/locations/upload', [\App\Http\Controllers\LocationController::class, 'upload']);