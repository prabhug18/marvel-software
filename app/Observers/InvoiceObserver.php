<?php
// app/Observers/InvoiceObserver.php
namespace App\Observers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        DB::table('invoice_logs')->insert([
            'invoice_id' => $invoice->id,
            'action' => 'created',
            'performed_by' => Auth::id(),
            'details' => json_encode($invoice->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updated(Invoice $invoice)
    {
        DB::table('invoice_logs')->insert([
            'invoice_id' => $invoice->id,
            'action' => 'updated',
            'performed_by' => Auth::id(),
            'details' => json_encode($invoice->getChanges()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function deleted(Invoice $invoice)
    {
        DB::table('invoice_logs')->insert([
            'invoice_id' => $invoice->id,
            'action' => 'deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($invoice->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
