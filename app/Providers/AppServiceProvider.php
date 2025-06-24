<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Models\Customer;
use App\Observers\CustomerObserver;
use App\Models\Warehouse;
use App\Observers\WarehouseObserver;
use App\Models\Brand;
use App\Observers\BrandObserver;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Payment;
use App\Observers\CategoryObserver;
use App\Models\Product;
use App\Models\Stock;
use App\Observers\ProductObserver;
use App\Observers\StockObserver;
use App\Observers\InvoiceObserver;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        User::observe(UserObserver::class);
        Customer::observe(CustomerObserver::class);
        Warehouse::observe(WarehouseObserver::class);
        Brand::observe(BrandObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        Stock::observe(StockObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(\App\Observers\PaymentObserver::class);
    }
}
