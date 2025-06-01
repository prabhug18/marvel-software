<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;


class GeneralController extends Controller
{
    //
    public function dashboard()
    {
        $heading    =   "Dashboard";
        $brandCount = Brand::count();
        $categoryCount = Category::count();
        $customerCount = Customer::count();
        return view('backend.general.dashboard', compact('heading', 'brandCount', 'categoryCount', 'customerCount'));
    }

    public static function logs()
    {
        
         // Select logs from UserLog
        $userLogs = \DB::table('user_logs')
            ->select(
                'id',
                \DB::raw("'user' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Select logs from CustomerLog
        $customerLogs = \DB::table('customer_logs')
            ->select(
                'id',
                \DB::raw("'customer' as log_type"),
                'action',
                'performed_by',
                'description as details',
                'created_at'
            );

        // Select logs from WarehouseLog
        $warehouseLogs = \DB::table('warehouse_logs')
            ->select(
                'id',
                \DB::raw("'warehouse' as log_type"),
                'action',
                'performed_by',
                'details',
                'created_at'
            );

        // Combine all logs and order by created_at descending
        $logs = $userLogs
            ->unionAll($customerLogs)
            ->unionAll($warehouseLogs)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $logs;
    }

}
