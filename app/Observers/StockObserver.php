<?php
// app/Observers/StockObserver.php
namespace App\Observers;

use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StockObserver
{
    public function created(Stock $stock)
    {
        DB::table('stock_logs')->insert([
            'stock_id' => $stock->id,
            'action' => 'created',
            'performed_by' => Auth::id(),
            'details' => json_encode($stock->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updated(Stock $stock)
    {
        DB::table('stock_logs')->insert([
            'stock_id' => $stock->id,
            'action' => 'updated',
            'performed_by' => Auth::id(),
            'details' => json_encode($stock->getChanges()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function deleted(Stock $stock)
    {
        DB::table('stock_logs')->insert([
            'stock_id' => $stock->id,
            'action' => 'deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($stock->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
