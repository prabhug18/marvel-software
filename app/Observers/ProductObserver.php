<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'created',
            'details' => $product->toJson(),
            'performed_by' => Auth::id(),
        ]);

        Log::info('Product created: ', $product->toArray());
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'updated',
            'details' => $product->toJson(),
            'performed_by' => auth()->id(),
        ]);

        Log::info('Product updated: ', $product->toArray());
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'deleted',
            'details' => $product->toJson(),
            'performed_by' => auth()->id(),
        ]);

        Log::info('Product deleted: ', $product->toArray());
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'restored',
            'data' => $product->toJson(),
            'performed_by' => auth()->id(),
        ]);

        Log::info('Product restored: ', $product->toArray());
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'forceDeleted',
            'data' => $product->toJson(),
            'performed_by' => auth()->id(),
        ]);

        Log::info('Product force deleted: ', $product->toArray());
    }
}
