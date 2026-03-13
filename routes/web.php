<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\PurchaseOrder\MilStdController;
use App\Models\OrderDetails;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});
Route::get('/purchases', function () {
    return Inertia::render('Views/PurchaseOrder');
})->name('View.Purchases');

Route::get('/providers', function () {
    return Inertia::render('Providers/Home');
})->name('providers.Home');

Route::get('/purchases/puview', function () {
    return Inertia::render('Views/PUView');
})->name('purchases.View');


Route::get('/check/test', function () {
    return Inertia::render('Views/Test');
})->name('test');


Route::middleware('auth')->group(function () {
    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::get('/order-products/{id}', [TestController::class, 'orderProducts']);    
    Route::get('/purchase-order/{purchase_order}/test', [PurchaseOrderController::class, 'test'])
        ->name('purchase-order.test');  
});

// RUTAS PARA MIL-STD
Route::middleware('auth')->group(function () {
    Route::get('/mil-std/api/{id_purchase_order}/products', [MilStdController::class, 'orderProducts']);
    Route::get('/mil-std/{id_purchase_order}', [MilStdController::class, 'show'])->name('mil-std.index');
    Route::get('/localM/{id_purchase_order}/{id_product}/inspection', [MilStdController::class, 'inspection'])->name('mil-std.inspection');
    Route::post('/localM/{id_purchase_order}/{id_product}/inspection', [MilStdController::class, 'addMilStd'])->name('mil-std.add');
    Route::post('/inspection/{id_purchase_order}/{id_product}',[MilStdController::class, 'saveInspection'])->name('inspection.save');    
    Route::post('/localM/{id_purchase_order}/{id_product}/local-sampling', [MilStdController::class, 'localSampling'])->name('mil-std.local-sampling');
        
});
require __DIR__.'/auth.php';