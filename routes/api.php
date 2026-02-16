<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Providers\ProviderController;
use App\Http\Controllers\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\Products\ProductsController;

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

Route::get('/purchase-order', [PurchaseOrderController::class, 'index']);
Route::post('/purchase-order', [PurchaseOrderController::class, 'store']);
Route::get('/purchase-order/{id}', [PurchaseOrderController::class, 'show']);
Route::put('/purchase-order/{id}', [PurchaseOrderController::class, 'update']);
Route::delete('/purchase-order/{id}', [PurchaseOrderController::class, 'destroy']);

Route::apiResource('purchase-orders', PurchaseOrderController::class);
Route::get('/products', [ProductsController::class, 'index']);
Route::get('/products/{id}', [ProductsController::class, 'show']);