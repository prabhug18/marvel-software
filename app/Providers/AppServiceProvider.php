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
use App\Observers\CategoryObserver;
use App\Models\Product;
use App\Observers\ProductObserver;

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

    }
}
