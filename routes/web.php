<?php

use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuickCreateController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'module-access'])
    ->name('dashboard');

Route::middleware(['auth', 'admin'])->resource('users', UserController::class)
    ->except(['show']);

Route::middleware(['auth', 'module-access'])->group(function () {

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/adjustments/create', [InventoryController::class, 'createAdjustment'])
        ->name('inventory.adjustments.create');
    Route::post('/inventory/adjustments', [InventoryController::class, 'storeAdjustment'])
        ->name('inventory.adjustments.store');
    Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');

    Route::controller(UnitController::class)->group(function () {
        Route::get('/units', 'index')->name('units.index');
        Route::get('/units/create', 'create')->name('units.create');
        Route::post('/units', 'store')->name('units.store');
        Route::get('/units/{id}/edit', 'edit')->name('units.edit');
        Route::put('/units/{id}', 'update')->name('units.update');
        Route::delete('/units/{id}', 'destroy')->name('units.destroy');
    });
    Route::resource('brands', BrandController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::resource('/products', ProductController::class);

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/quick-create', [QuickCreateController::class, 'store'])->name('quick-create.store');

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'edit', 'update', 'destroy', 'store', 'show']);

    Route::get('sales/pos', [SaleController::class, 'pos'])
        ->name('sales.pos');
    Route::resource('sales', SaleController::class)->only(['index', 'edit', 'update', 'destroy', 'create', 'store', 'show']);

    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'edit', 'update', 'destroy', 'store', 'show']);

    Route::post('/customers/ajax-store', [CustomerController::class, 'ajaxStore'])
        ->name('customers.ajax-store');

    Route::get('/customers/ajax-options', [CustomerController::class, 'ajaxOptions'])
        ->name('customers.ajax-options');

});

Route::middleware(['auth', 'module-access'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/products', [ReportsController::class, 'products'])->name('products');
    Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
    Route::get('/purchases', [ReportsController::class, 'purchases'])->name('purchases');
    Route::get('/stock', [ReportsController::class, 'stock'])->name('stock');
    Route::get('/profit-loss', [ReportsController::class, 'profitLoss'])->name('profit_loss');
});

Route::middleware(['auth', 'module-access'])->group(function () {
    Route::resource('/posts', PostController::class);
});

Route::middleware(['auth', 'module-access'])->group(function () {
    Route::resource('sale-returns', SaleReturnController::class)
        ->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);
    Route::resource('purchase-returns', PurchaseReturnController::class)
        ->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);
});
Route::get('/auth/google', [SocialController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::get('/auth/facebook', [SocialController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialController::class, 'handleFacebookCallback']);

// Route::middleware('auth')->controller(CategoryController::class)->group(function () {
//     Route::get('categories', 'index')->name('category.index');
//     Route::get('categories/create', 'create')->name('category.create');
//     Route::post('categories', 'store')->name('category.store');
//     Route::get('categories/{id}/edit', 'edit')->name('category.edit');
// });

require __DIR__.'/auth.php';
