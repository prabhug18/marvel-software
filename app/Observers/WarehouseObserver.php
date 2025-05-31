<?php

namespace App\Observers;

use App\Models\Warehouse;
use App\Models\WarehouseLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarehouseObserver
{

    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        WarehouseLog::create([
            'warehouse_id' => $warehouse->id,
            'action'      => 'created',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($warehouse->toArray()),
        ]);
    }

    /**
     * Handle the Warehouse "updated" event.
     */
    public function updated(Warehouse $warehouse): void
    {
        WarehouseLog::create([
            'warehouse_id' => $warehouse->id,
            'action'      => 'updated',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($warehouse->getChanges()),
        ]);
    }

    /**
     * Handle the Warehouse "deleted" event.
     */
    public function deleted(Warehouse $warehouse): void
    {
        Log::info('Warehouse deleted observer triggered', ['id' => $warehouse->id]);
        WarehouseLog::create([
            'warehouse_id' => $warehouse->id,
            'action'      => 'deleted',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($warehouse->toArray()),
        ]);
    }

    /**
     * Handle the Warehouse "restored" event.
     */
    public function restored(Warehouse $warehouse): void
    {
        WarehouseLog::create([
            'warehouse_id' => $warehouse->id,
            'action'      => 'restored',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($warehouse->toArray()),
        ]);
    }

    /**
     * Handle the Warehouse "force deleted" event.
     */
    public function forceDeleted(Warehouse $warehouse): void
    {
        WarehouseLog::create([
            'warehouse_id' => $warehouse->id,
            'action'      => 'force_deleted',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($warehouse->toArray()),
        ]);
    }
}
