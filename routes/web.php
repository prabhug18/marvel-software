<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PaymentController;
use App\Models\Payment;
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
    Route::resource('stocks', StockController::class);
    Route::get('invoice/export', [App\Http\Controllers\InvoiceController::class, 'export'])->name('invoice.export');
    Route::resource('invoice', InvoiceController::class); 
    Route::post('invoice/send-email', [InvoiceController::class, 'sendEmail'])->name('invoice.sendEmail');

    Route::get('logs', 'App\Http\Controllers\GeneralController@logs');
    Route::get('/get-city', [CustomerController::class, 'getCity']);
    Route::get('/generate-invoice-number', [InvoiceController::class, 'generateInvoiceNumber']);
});

// Invoice view page for a specific invoice
Route::get('invoice-view', [App\Http\Controllers\InvoiceController::class, 'viewInvoice'])->name('invoice.viewInvoice');

Route::get('/customer-search', [CustomerController::class, 'search']);
Route::get('/product-search', [ProductController::class, 'search']);

Route::get('/api/categories', function() {
    return response()->json(\App\Models\Category::all());
});

Route::get('/api/brands', function() {
    return response()->json(\App\Models\Brand::all());
});

Route::get('/api/products/search', function (\Illuminate\Http\Request $request) {
    $term = $request->get('q', '');

    return \App\Models\Product::with(['category:id,name', 'brand:id,name'])
        ->where('model', 'like', "%$term%")
        ->orWhereHas('category', function($q) use ($term) {
            $q->where('name', 'like', "%$term%");
        })
        ->orWhereHas('brand', function($q) use ($term) {
            $q->where('name', 'like', "%$term%");
        })
        ->orWhere('category_id', $term)
        ->orWhere('brand_id', $term)
        ->limit(10)
        ->get(['id', 'model', 'category_id', 'brand_id']);
});

Route::get('/api/warehouses', function() {
    return response()->json(\App\Models\Warehouse::all(['id', 'name']));
});

Route::get('/api/warehouse-stock', function (\Illuminate\Http\Request $request) {
    $stocks = \App\Models\Stock::with('warehouse')
        ->where('category_id', $request->category_id)
        ->where('brand_id', $request->brand_id)
        ->where('model', $request->model)
        ->get();

    return response()->json($stocks->map(function($stock) {
        return [
            'warehouse' => $stock->warehouse ? $stock->warehouse->name : 'N/A',
            'qty' => $stock->qty,
        ];
    }));
});
Route::get('/stock/export', [StockController::class, 'exportPage'])->name('stock.export');
Route::get('/export/stock', [StockController::class, 'export'])->name('export.stocks');
Route::post('/stocks/import', [StockController::class, 'import'])->name('stocks.import');

Route::get('/export/product', [ProductController::class, 'exportPage'])->name('export.product');
Route::get('/product/export', [ProductController::class, 'export'])->name('product.export');
Route::post('/product/import', [ProductController::class, 'import'])->name('product.import');
Route::get('/api/invoice-details', [PaymentController::class, 'invoiceDetailsWithTotal']);
Route::match(['get', 'post'], 'payment/add-payment', [PaymentController::class, 'addPayment']);
Route::post('payment/store', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/view', [PaymentController::class, 'view'])->name('payment.view');
Route::get('payment/create', [PaymentController::class, 'create'])->name('payment.create');
// AJAX endpoint for stock check (for invoice add product validation)
Route::get('/check-stock', [StockController::class, 'checkStock']);
// Add this route for AJAX balance fetch from PaymentController
Route::get('/customer-balance', [App\Http\Controllers\PaymentController::class, 'customerBalance']);
// Payment AJAX add route
Route::post('/payment/add', [App\Http\Controllers\PaymentController::class, 'addPayment']);
// AJAX payment store route
Route::post('/payment/ajax-store', [App\Http\Controllers\PaymentController::class, 'ajaxStore']);
// Payment Reconciliation page for a specific invoice
Route::get('payment/payment-reconciliation', [PaymentController::class, 'paymentReconciliation']);
// Mark reconciliation as done
Route::post('payment/mark-reconciliation', [PaymentController::class, 'markReconciliation']);
Route::post('payment/confirm', [PaymentController::class, 'confirmPayment'])->name('payment.confirm');
// Pending Reconciliation modal AJAX endpoint
Route::get('payment/pending-reconciliation', [App\Http\Controllers\PaymentController::class, 'pendingReconciliation'])->name('payment.pendingReconciliation');


