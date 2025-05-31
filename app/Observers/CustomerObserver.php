<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\CustomerLog;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        CustomerLog::create([
            'customer_id' => $customer->id,
            'action'      => 'created',
            'performed_by'=> Auth::id(),
            'description'     => json_encode($customer->toArray()),
        ]);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        CustomerLog::create([
            'customer_id' => $customer->id,
            'action'      => 'updated',
            'performed_by'=> Auth::id(),
            'description'     => json_encode($customer->getChanges()),
        ]);
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        CustomerLog::create([
            'customer_id' => $customer->id,
            'action'      => 'deleted',
            'performed_by'=> Auth::id(),
            'description'     => json_encode($customer->toArray()),
        ]);
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        CustomerLog::create([
            'customer_id' => $customer->id,
            'action'      => 'restored',
            'performed_by'=> Auth::id(),
            'description'     => json_encode($customer->toArray()),
        ]);
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        CustomerLog::create([
            'customer_id' => $customer->id,
            'action'      => 'force_deleted',
            'performed_by'=> Auth::id(),
            'description'     => json_encode($customer->toArray()),
        ]);
    }
}
