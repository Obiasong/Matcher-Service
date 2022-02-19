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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::apiResource('property', \App\Http\Controllers\PropertyController::class);
//Route::apiResource('search_profile', \App\Http\Controllers\SearchProfileController::class);


Route::get('match/{property_id}', [\App\Http\Controllers\MatchMakingController::class, 'getSearchProfiles']);
