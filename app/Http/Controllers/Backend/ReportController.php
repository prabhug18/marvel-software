<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\InvoiceItems;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $heading = "Reports Center";
        return view('backend.modules.reports.index', compact('heading'));
    }

    public function invoiceReport(Request $request)
    {
        $heading = "Invoice Report";
        
        $range = $request->get('range', 'today');
        $from = $request->get('from_date');
        $to = $request->get('to_date');
        $customer_id = $request->get('customer_id');

        $query = Invoice::query()->with(['customer', 'warehouse']);

        // Date Range Logic
        if ($range == 'today') {
            $query->whereDate('invoice_date', Carbon::today());
        } elseif ($range == '7days') {
            $query->whereDate('invoice_date', '>=', Carbon::today()->subDays(7));
        } elseif ($range == '30days') {
            $query->whereDate('invoice_date', '>=', Carbon::today()->subDays(30));
        } elseif ($range == '90days') {
            $query->whereDate('invoice_date', '>=', Carbon::today()->subDays(90));
        } elseif ($range == 'custom') {
            if ($from) {
                $query->whereDate('invoice_date', '>=', $from);
            }
            if ($to) {
                $query->whereDate('invoice_date', '<=', $to);
            }
        }

        if ($customer_id) {
            $query->where('customer_id', $customer_id);
        }

        if ($request->get('export') == 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InvoiceExport($from, $to, null), 'invoice_report.xlsx');
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(50);
        
        // Summaries
        $summary = [
            'total_invoiced' => $query->sum('grand_total'),
            'total_cgst' => $query->sum('cgst'),
            'total_sgst' => $query->sum('sgst'),
            'total_igst' => $query->sum('igst'),
        ];

        $customers = Customer::orderBy('name')->get();

        return view('backend.modules.reports.invoice', compact('invoices', 'summary', 'customers', 'heading'));
    }

    public function paymentReport(Request $request)
    {
        $heading = "Payment Report";
        
        $range = $request->get('range', 'today');
        $from = $request->get('from_date');
        $to = $request->get('to_date');
        $mode = $request->get('payment_mode');

        // Table Query
        $query = Payment::query()->with('customer');

        if ($range == 'today') {
            $query->whereDate('payment_date', Carbon::today());
        } elseif ($range == '7days') {
            $query->whereDate('payment_date', '>=', Carbon::today()->subDays(7));
        } elseif ($range == '30days') {
            $query->whereDate('payment_date', '>=', Carbon::today()->subDays(30));
        } elseif ($range == '90days') {
            $query->whereDate('payment_date', '>=', Carbon::today()->subDays(90));
        } elseif ($range == 'custom') {
            if ($from) {
                $query->whereDate('payment_date', '>=', $from);
            }
            if ($to) {
                $query->whereDate('payment_date', '<=', $to);
            }
        }

        if ($mode) {
            $query->where('payment_mode', $mode);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(50);

        // Summaries (Lifetime - No date limit)
        $summary = [
            'total_collection' => Payment::sum('paid_amount'),
            'confirmed_collection' => Payment::where('is_confirmed', 1)->sum('paid_amount'),
            'pending_collection' => Payment::where('is_confirmed', 0)->sum('paid_amount'),
        ];

        return view('backend.modules.reports.payment', compact('payments', 'summary', 'heading'));
    }

    public function customerHistory(Request $request)
    {
        $heading = "Customer Purchase History";
        $search = $request->get('q');
        
        $customers = [];
        if ($search) {
            $customers = Customer::where('name', 'LIKE', "%$search%")
                ->orWhere('mobile_no', 'LIKE', "%$search%")
                ->orWhere('gst_no', 'LIKE', "%$search%")
                ->orWhere('remarks', 'LIKE', "%$search%")
                ->with('invoices')
                ->get();
        }

        return view('backend.modules.reports.customer_history', compact('customers', 'heading'));
    }

    public function warrantyCheck(Request $request)
    {
        $heading = "Product Warranty Check";
        $serial = $request->get('serial_no');
        
        $item = null;
        $warrantyStatus = null;

        if ($serial) {
            $item = InvoiceItems::where('serial_no', $serial)
                ->with(['invoice', 'product'])
                ->first();

            if ($item && $item->invoice && $item->product) {
                $saleDate = Carbon::parse($item->invoice->invoice_date);
                $focMonths = (int) $item->product->foc_months;
                $expiryDate = $saleDate->addMonths($focMonths);
                
                $item->warranty_expiry = $expiryDate;
                $warrantyStatus = Carbon::now()->gt($expiryDate) ? 'Expired' : 'Active';
            }
        }

        return view('backend.modules.reports.warranty', compact('item', 'warrantyStatus', 'heading'));
    }
}
