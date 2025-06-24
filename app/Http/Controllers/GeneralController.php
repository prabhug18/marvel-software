<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    //
    public function dashboard()
    {
        $heading    =   "Dashboard";
        $brandCount = Brand::count();
        $categoryCount = Category::count();
        $customerCount = Customer::count();
        $stockCount = Stock::sum('qty');
        // Get current month invoice total
        $currentMonthTotal = \App\Models\Invoice::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');
        // Get daily invoice totals for current month
        $invoiceDailyTotals = \App\Models\Invoice::selectRaw('DATE(created_at) as date, SUM(grand_total) as total')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        // Prepare data for chart
        $chartLabels = $invoiceDailyTotals->pluck('date')->map(function($d) { return date('d M', strtotime($d)); });
        $chartData = $invoiceDailyTotals->pluck('total');
        $currentMonthTotal = $invoiceDailyTotals->sum('total');
        // Reconciled and pending totals
        $reconciledTotal = \App\Models\Invoice::where('reconciliation_done', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');
        $pendingTotal = \App\Models\Invoice::where('reconciliation_done', false)->sum('grand_total');
        $currentMonthInvoiceCount = \App\Models\Invoice::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        // Add this to your GeneralController and pass $currentMonthReconciled to the view
        $currentMonthReconciled = \App\Models\Payment::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('paid_amount');
        // Add this to your GeneralController and pass $pendingReconciliation to the view
        $pendingReconciliation = \App\Models\Invoice::where('reconciliation_done', false)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        // Date filter logic
        $from = request('from_date', now()->startOfMonth()->format('Y-m-d'));
        $to = request('to_date', now()->endOfMonth()->format('Y-m-d'));

        // Use filtered dates for all queries (except chart)
        $filteredMonthInvoiceCount = \App\Models\Invoice::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->count();
        $filteredMonthTotal = \App\Models\Invoice::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('grand_total');
        $filteredMonthReconciled = \App\Models\Invoice::where('reconciliation_done', true)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('grand_total');
        $filteredPendingReconciliation = \App\Models\Invoice::where('reconciliation_done', false)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('grand_total');

        // Pass both filtered and current month (for chart) to view
        return view('backend.general.dashboard', compact(
            'heading', 'brandCount', 'categoryCount', 'customerCount', 'stockCount',
            'currentMonthTotal', 'chartLabels', 'chartData', 'reconciledTotal', 'pendingTotal',
            'currentMonthInvoiceCount', 'currentMonthReconciled', 'pendingReconciliation',
            'filteredMonthInvoiceCount', 'filteredMonthTotal', 'filteredMonthReconciled', 'filteredPendingReconciliation',
            'from', 'to'
        ));
    }

    public static function logs()
    {        
         // Select logs from UserLog
        $userLogs = DB::table('user_logs')
            ->select(
                'id',
                DB::raw("'user' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Select logs from CustomerLog
        $customerLogs = DB::table('customer_logs')
            ->select(
                'id',
                DB::raw("'customer' as log_type"),
                'action',
                'performed_by',
                'description as details',
                'created_at'
            );

        // Select logs from WarehouseLog
        $warehouseLogs = DB::table('warehouse_logs')
            ->select(
                'id',
                DB::raw("'warehouse' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Select logs from CategoryLog
        $categoryLogs = DB::table('category_logs')
            ->select(
                'id',
                DB::raw("'category' as log_type"),
                'action',
                'performed_by',
                'details', // Select details as-is, replace IDs with names in Blade
                'created_at'
            );

        // Select logs from BrandLog
        $brandLogs = DB::table('brand_logs')
            ->select(
                'id',
                DB::raw("'brand' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Select logs from InvoiceLog
        $invoiceLogs = DB::table('invoice_logs')
            ->select(
                'id',
                DB::raw("'invoice' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Select logs from StockLog
        $stockLogs = DB::table('stock_logs')
            ->select(
                'id',
                DB::raw("'stock' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Combine all logs and order by created_at descending
        
        $logs = $userLogs
            ->unionAll($customerLogs)
            ->unionAll($warehouseLogs)
            ->unionAll($categoryLogs)
            ->unionAll($brandLogs)
            ->unionAll($invoiceLogs)
            ->unionAll($stockLogs)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Post-process customer logs to replace state/city IDs with names
        foreach ($logs as $log) {
            if ($log->log_type === 'customer' && !empty($log->details)) {
                $details = json_decode($log->details, true);
                if (is_array($details)) {
                    // Try both 'state' and 'state_id', 'city' and 'city_id'
                    $stateId = $details['state'] ?? $details['state_id'] ?? null;
                    $cityId = $details['city'] ?? $details['city_id'] ?? null;
                    if ($stateId && is_numeric($stateId)) {
                        $state = \App\Models\State::find($stateId);
                        if ($state) {
                            if (isset($details['state'])) $details['state'] = $state->name;
                            if (isset($details['state_id'])) $details['state_id'] = $state->name;
                        }
                    }
                    if ($cityId && is_numeric($cityId)) {
                        $city = \App\Models\City::find($cityId);
                        if ($city) {
                            if (isset($details['city'])) $details['city'] = $city->name;
                            if (isset($details['city_id'])) $details['city_id'] = $city->name;
                        }
                    }
                    $log->details = json_encode($details, JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return $logs;
    }
}
