<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

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

//Route::get('/user', function () {
//    \App\Models\User::factory(5)->create();
//});
Route::redirect('/', 'login');

//Route::resource('products', ProductController::class)->middleware(['auth']);

Route::middleware('auth')->group(function () {
   Route::get('products', [ProductController::class, 'index'])->name('products.index');

    Route::middleware('is_admin')->group(function () {
        Route::get('product/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('product/store', [ProductController::class, 'store'])->name('products.store');
        Route::get('product/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('product/{product}', [ProductController::class, 'update'])->name('products.update');

        Route::delete('product/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
