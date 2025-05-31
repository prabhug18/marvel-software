<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');        
    }
    return view('auth.login');
});

Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function() {
    Route::get('dashboard', 'App\Http\Controllers\GeneralController@dashboard');
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class); 
    Route::resource('locations', WarehouseController::class);  
    Route::resource('customer', CustomerController::class); 
    Route::resource('brands', BrandController::class); 
    Route::resource('categories', CategoryController::class); 
    Route::resource('products', ProductController::class); 
    Route::get('logs', 'App\Http\Controllers\GeneralController@logs');
    Route::get('/get-city', [CustomerController::class, 'getCity']);
});

Route::get('/api/categories', function() {
    return response()->json(\App\Models\Category::all());
});

Route::get('/api/brands', function() {
    return response()->json(\App\Models\Brand::all());
});

