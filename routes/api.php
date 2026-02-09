<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Providers\ProviderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
    
});

Route::get('/providers', [ProviderController::class, 'index']);
Route::post('/providers', [ProviderController::class, 'store']);
Route::get('/providers/{id}', [ProviderController::class, 'show']);
Route::put('/providers/{id}', [ProviderController::class, 'update']);
Route::delete('/providers/{id}', [ProviderController::class, 'destroy']);

