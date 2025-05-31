<?php

namespace App\Observers;

use App\Models\Brand;
use App\Models\BrandLog;
use Illuminate\Support\Facades\Auth;

class BrandObserver
{
    public function created(Brand $brand): void
    {
        BrandLog::create([
            'brand_id' => $brand->id,
            'action' => 'created',
            'performed_by' => Auth::id(),
            'details' => json_encode($brand->toArray()),
        ]);
    }

    public function updated(Brand $brand): void
    {
        BrandLog::create([
            'brand_id' => $brand->id,
            'action' => 'updated',
            'performed_by' => Auth::id(),
            'details' => json_encode($brand->getChanges()),
        ]);
    }

    public function deleted(Brand $brand): void
    {
        BrandLog::create([
            'brand_id' => $brand->id,
            'action' => 'deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($brand->toArray()),
        ]);
    }

    public function restored(Brand $brand): void
    {
        BrandLog::create([
            'brand_id' => $brand->id,
            'action' => 'restored',
            'performed_by' => Auth::id(),
            'details' => json_encode($brand->toArray()),
        ]);
    }

    public function forceDeleted(Brand $brand): void
    {
        BrandLog::create([
            'brand_id' => $brand->id,
            'action' => 'force_deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($brand->toArray()),
        ]);
    }
}
