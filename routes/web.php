<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Backend\ReportController;
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
    Route::resource('source', SourceController::class);
    Route::resource('terms', TermController::class)->middleware('role:Admin');
    Route::get('invoice/export', [App\Http\Controllers\InvoiceController::class, 'export'])->name('invoice.export');
    Route::get('invoice/search', [InvoiceController::class, 'ajaxSearch']);
    Route::get('invoice/details', [InvoiceController::class, 'ajaxDetails']);
    Route::resource('invoice', InvoiceController::class); 
    // GST Invoice View
    Route::get('invoice/{id}/gst', [App\Http\Controllers\InvoiceController::class, 'gstInvoiceView'])->name('invoice.gst');
    // Invoice View - Format Three (new)
    Route::get('invoice/{id}/three', [App\Http\Controllers\InvoiceController::class, 'threeInvoiceView'])->name('invoice.three');
        // Invoice View - Format Four (new)
        Route::get('invoice/{id}/four', [App\Http\Controllers\InvoiceController::class, 'fourInvoiceView'])->name('invoice.four');
    // Admin settings: invoice template
    Route::get('admin/settings/invoice-template', [App\Http\Controllers\Admin\SettingController::class, 'editInvoiceTemplate'])->name('admin.settings.invoice_template.edit');
    Route::post('admin/settings/invoice-template', [App\Http\Controllers\Admin\SettingController::class, 'updateInvoiceTemplate'])->name('admin.settings.invoice_template.update');
    
    Route::post('invoice/send-email', [InvoiceController::class, 'sendEmail'])->name('invoice.sendEmail');
    // Approve invoice (admin only)
    Route::post('invoice/{id}/approve', [InvoiceController::class, 'approve'])->name('invoice.approve');

    Route::get('logs', 'App\Http\Controllers\GeneralController@logs');
    Route::get('/get-city', [CustomerController::class, 'getCity']);
    Route::get('/check-warranty', [InvoiceController::class, 'checkWarranty']);
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
        ->orWhere('model_no', 'like', "%$term%")
        ->orWhereHas('category', function($q) use ($term) {
            $q->where('name', 'like', "%$term%");
        })
        ->orWhereHas('brand', function($q) use ($term) {
            $q->where('name', 'like', "%$term%");
        })
        ->limit(20)
        ->get(['id', 'model', 'model_no', 'category_id', 'brand_id'])
        ->values();
});

Route::get('/api/warehouses', function() {
    return response()->json(\App\Models\Warehouse::all(['id', 'name']));
});

// AJAX: Get warehouse-wise stock for a product
Route::get('/api/warehouse-stock', [StockController::class, 'warehouseStock']);

// AJAX: Update warehouse stock quantity
Route::post('/api/update-warehouse-stock', [StockController::class, 'updateWarehouseStock']);
Route::get('/stock/export', [StockController::class, 'exportPage'])->name('stock.export');
Route::get('/export/stock', [StockController::class, 'export'])->name('export.stocks');
Route::get('/export/stock-detailed', [StockController::class, 'exportDetailed'])->name('export.stocks.detailed');
Route::post('/stocks/import', [StockController::class, 'import'])->name('stocks.import');

Route::get('/export/product', [ProductController::class, 'exportPage'])->name('export.product');
Route::get('/product/export', [ProductController::class, 'export'])->name('product.export');
Route::post('/product/import', [ProductController::class, 'import'])->name('product.import');
Route::post('/product/import-preview', [ProductController::class, 'importPreview'])->name('product.import.preview');
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
// AJAX bulk payment store route (used by reconciliation page)
Route::post('/payment/bulk-store', [App\Http\Controllers\PaymentController::class, 'bulkStore']);
// Payment Reconciliation page for a specific invoice
Route::get('payment/payment-reconciliation', [PaymentController::class, 'paymentReconciliation']);
// Mark reconciliation as done
Route::post('payment/mark-reconciliation', [PaymentController::class, 'markReconciliation']);
Route::post('payment/confirm', [PaymentController::class, 'confirmPayment'])->name('payment.confirm');
// Pending Reconciliation modal AJAX endpoint
Route::get('payment/pending-reconciliation', [App\Http\Controllers\PaymentController::class, 'pendingReconciliation'])->name('payment.pendingReconciliation');

Route::get('/vendor-search', [VendorController::class, 'search']);
Route::resource('vendors', VendorController::class);

// Reports Module
Route::group(['middleware' => ['auth']], function () {
    Route::get('/reports/invoice', [ReportController::class, 'invoiceReport'])->name('reports.invoice');
    Route::get('/reports/payment', [ReportController::class, 'paymentReport'])->name('reports.payment');
    Route::get('/reports/customer-history', [ReportController::class, 'customerHistory'])->name('reports.customer_history');
    Route::get('/reports/warranty', [ReportController::class, 'warrantyCheck'])->name('reports.warranty_check');
});

// Temporary route to sync permissions on live - Visit /sync-permissions once and then remove this
Route::get('/sync-permissions', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'PermissionTableSeeder',
            '--force' => true
        ]);
        return "SUCCESS: Permissions synced! Roles updated with 'report' module. Please remove this from web.php now.";
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage();
    }
});


