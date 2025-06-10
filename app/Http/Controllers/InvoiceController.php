<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Invoice;
use App\Exports\InvoiceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $heading = "Invoice View";
        $threeDaysAgo = now()->subDays(10)->startOfDay();
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            $invoices = \App\Models\Invoice::where('created_at', '>=', $threeDaysAgo)
                ->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $userWarehouseId = Auth::user()->warehouse_id ?? null;
            $invoices = \App\Models\Invoice::where('warehouse_id', $userWarehouseId)
                ->where('created_at', '>=', $threeDaysAgo)
                ->orderBy('created_at', 'desc')->paginate(10);
        }
        if ($request->ajax()) {
            return view('backend.modules.invoice.partials.invoice_rows', compact('invoices'))->render();
        }
        return view('backend.modules.invoice.index', compact('heading', 'invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $heading    =   "Invoice Creation";
        $customers   =   Customer::all();
        $state      =   State::where('country_id','101')->get();
        $warehouses = [];
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            $warehouses = Warehouse::all();
        }
        return view('backend.modules.invoice.create', compact('heading','customers', 'state', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input (add more rules as needed)
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'mobile_no' => 'required|string',
            'address' => 'required|string',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'products' => 'required|array|min:1',
        ]);

        // Find or create customer
        $customer = \App\Models\Customer::where('mobile_no', $request->mobile_no)
            ->orWhere('email', $request->email)
            ->first();
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'name' => $request->customer_name,
                'mobile_no' => $request->mobile_no,
                'email' => $request->email ?? null,
                'address' => $request->address,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'pincode' => $request->pincode,
                'gst_no' => $request->gst_number ?? null,
                'user_id' => Auth::id() ?? 1,
            ]);
        }

        // Calculate totals
        $cgst = $request->cgst ?? 0;
        $sgst = $request->sgst ?? 0;
        $igst = $request->igst ?? 0;
        $grand_total = $request->grand_total ?? 0;

        // Create invoice
        $warehouse_id = null;
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            // Admin: always use warehouse_id from the request (from invoice page)
            $warehouse_id = $request->warehouse_id;
        } else {
            // Non-admin: use user's warehouse_id
            $warehouse_id = Auth::user()->warehouse_id ?? null;
        }
        $invoice = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id() ?? 1,
            'customer_name' => $customer->name ?? $request->customer_name,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'dc_number' => $request->dc_number,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'grand_total' => $grand_total,
            'warehouse_id' => $warehouse_id,
        ]);

        // Automatically create a Payment entry for this invoice
        \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name ?? $request->customer_name,
            'user_id' => Auth::id() ?? 1,
            'paid_amount' => 0,
            'balance_amount' => $grand_total,
            'grand_total' => $grand_total,
            'payment_date' => $request->invoice_date,
            'warehouse_id' => $warehouse_id,
            'payment_mode' => null, // Default to Credit for new invoices
            'invoice_number' => $request->invoice_number,
        ]);

        // Create invoice items
        $stockIssues = [];
        foreach ($request->products as $item) {
            \App\Models\InvoiceItems::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id() ?? 1,
                'product_name' => $item['name'] ?? $item['product_name'] ?? '',
                'model' => $item['model'] ?? '',
                'qty' => $item['qty'],
                'tax_percentage' => $item['tax_percentage'] ?? 5,
                'tax_amount' => isset($item['tax_amount']) ? $item['tax_amount'] : 0,
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);

            // --- Debit stock from warehouse ---
            $warehouseId = $request->warehouse_id ?? (Auth::user()->warehouse_id ?? null);
            if ($warehouseId) {
                $product = null;
                if (isset($item['product_id'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                } elseif (!empty($item['model'])) {
                    $product = \App\Models\Product::where('model', $item['model'])->first();
                }
                if ($product) {
                    $stockQuery = \App\Models\Stock::where('warehouse_id', $warehouseId)
                        ->where('category_id', $product->category_id)
                        ->where('brand_id', $product->brand_id)
                        ->where('model', $product->model);
                    $stock = $stockQuery->first();
                    if ($stock) {
                        $oldQty = $stock->qty;
                        $stock->qty = max(0, $stock->qty - $item['qty']);
                        $stock->save();
                    } else {
                        $msg = 'Stock not found for product: ' . json_encode([
                            'warehouse_id' => $warehouseId,
                            'category_id' => $product->category_id,
                            'brand_id' => $product->brand_id,
                            'model' => $product->model,
                        ]);
                        $stockIssues[] = $msg;
                    }
                } else {
                    $msg = 'Product not found for stock debit: ' . json_encode($item);
                    $stockIssues[] = $msg;
                }
            }
        }
        if (!empty($stockIssues)) {
            return response()->json(['success' => true, 'invoice_id' => $invoice->id, 'stock_warnings' => $stockIssues]);
        }
        return response()->json(['success' => true, 'invoice_id' => $invoice->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Only allow admin
        if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
            abort(403, 'Unauthorized');
        }
        $heading    =   "Invoice Updation";
        $invoice = \App\Models\Invoice::with(['customer', 'customer.city', 'items'])->findOrFail($id);
        $states = \App\Models\State::where('country_id', '101')->get();
        $warehouses = \App\Models\Warehouse::all();
        return view('backend.modules.invoice.edit', compact('heading','invoice', 'states', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Only allow admin
        if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'mobile_no' => 'required|string',
            'address' => 'required|string',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
        ]);
        $invoice = \App\Models\Invoice::findOrFail($id);
        $customer = \App\Models\Customer::find($invoice->customer_id);
        if ($customer) {
            $customer->update([
                'name' => $request->customer_name,
                'mobile_no' => $request->mobile_no,
                'email' => $request->email,
                'address' => $request->address,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'pincode' => $request->pincode,
                'gst_no' => $request->gst_number,
            ]);
        }
        // --- Begin Transaction for Invoice and Items ---
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'customer_name' => $request->customer_name,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'dc_number' => $request->dc_number,
                'warehouse_id' => $request->warehouse_id,
                'cgst' => (float)($request->cgst ?? 0),
                'sgst' => (float)($request->sgst ?? 0),
                'igst' => (float)($request->igst ?? 0),
                'grand_total' => (float)($request->grand_total ?? 0),
            ]);
            // --- Update Invoice Items ---
            if ($request->has('products_json')) {
                $products = json_decode($request->products_json, true);
                if (is_array($products)) {
                    // Remove old items
                    \App\Models\InvoiceItems::where('invoice_id', $invoice->id)->delete();
                    // Insert new items
                    foreach ($products as $item) {
                        \App\Models\InvoiceItems::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => Auth::id() ?? 1,
                            'product_name' => $item['name'] ?? $item['product_name'] ?? '',
                            'model' => $item['model'] ?? '',
                            'qty' => $item['qty'] ?? 1,
                            'tax_percentage' => $item['tax_percentage'] ?? 5,
                            'tax_amount' => $item['tax_amount'] ?? 0,
                            'unit_price' => $item['unit_price'],
                            'total' => $item['total'] ?? 0,
                        ]);
                    }
                }
            }
        });
        return redirect()->route('invoice.index')->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->route('invoice.index')->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Generate next invoice number based on warehouse prefix and last invoice number
     */
    public function generateInvoiceNumber(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
        } else {
            $user = Auth::user();
            $warehouse = Warehouse::find($user->warehouse_id);
        }
        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse not found'], 404);
        }
        $prefix = $warehouse->prefix;
        // Find the last invoice number that starts with this prefix
        $pattern = $prefix . '/%';
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('id', 'desc')
            ->first();
        // Extract the last number after the last slash
        if ($lastInvoice && preg_match('/' . preg_quote($prefix, '/') . '\/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $invoiceNumber = $prefix . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return response()->json(['invoice_number' => $invoiceNumber]);
    }

    /**
     * Export invoices with optional date filter.
     */
    public function export(Request $request)
    {
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        $query = \App\Models\Invoice::query();
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            // Admin: can export all invoices
            // No warehouse filter
        } else {
            // Non-admin: only export invoices for their warehouse
            $userWarehouseId = Auth::user()->warehouse_id ?? null;
            $query->where('warehouse_id', $userWarehouseId);
        }
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        $count = $query->count();
        if ($count === 0) {
            return response('No invoices found for this date range.', 404);
        }
        // Pass warehouse_id to export for non-admins
        $warehouseId = (Auth::user() && !Auth::user()->hasRole('Admin')) ? (Auth::user()->warehouse_id ?? null) : null;
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InvoiceExport($from, $to, $warehouseId), 'invoices.xlsx');
    }
}

