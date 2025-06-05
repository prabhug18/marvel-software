<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $heading = "Invoice View";
        $invoices = \App\Models\Invoice::orderBy('created_at', 'desc')->get();
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
                'user_id' => auth()->id() ?? 1,
            ]);
        }

        // Calculate totals
        $cgst = $request->cgst ?? 0;
        $sgst = $request->sgst ?? 0;
        $igst = $request->igst ?? 0;
        $grand_total = $request->grand_total ?? 0;

        // Create invoice
        $invoice = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'dc_number' => $request->dc_number,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'grand_total' => $grand_total,
        ]);

        // Create invoice items
        $stockIssues = [];
        foreach ($request->products as $item) {
            \App\Models\InvoiceItems::create([
                'invoice_id' => $invoice->id,
                'product_name' => $item['name'],
                'model' => $item['model'] ?? '',
                'qty' => $item['qty'],
                'tax_percentage' => $item['tax_percentage'] ?? 5,
                'tax_amount' => isset($item['tax_amount']) ? $item['tax_amount'] : 0,
                'unit_price' => $item['price'],
                'total' => $item['total'],
            ]);

            // --- Debit stock from warehouse ---
            $warehouseId = $request->warehouse_id ?? (Auth::user()->warehouse_id ?? null);
            \Log::debug('Stock debit attempt', [
                'item' => $item,
                'warehouse_id' => $warehouseId
            ]);
            if ($warehouseId) {
                $product = null;
                if (isset($item['product_id'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                } elseif (!empty($item['model'])) {
                    $product = \App\Models\Product::where('model', $item['model'])->first();
                }
                \Log::debug('Product found for stock debit', ['product' => $product]);
                if ($product) {
                    $stockQuery = \App\Models\Stock::where('warehouse_id', $warehouseId)
                        ->where('category_id', $product->category_id)
                        ->where('brand_id', $product->brand_id)
                        ->where('model', $product->model);
                    \Log::debug('Stock query SQL', ['sql' => $stockQuery->toSql(), 'bindings' => $stockQuery->getBindings()]);
                    $stock = $stockQuery->first();
                    \Log::debug('Stock found', ['stock' => $stock]);
                    if ($stock) {
                        $oldQty = $stock->qty;
                        $stock->qty = max(0, $stock->qty - $item['qty']);
                        $stock->save();
                        \Log::info('Stock debited', [
                            'stock_id' => $stock->id,
                            'old_qty' => $oldQty,
                            'new_qty' => $stock->qty,
                            'debit' => $item['qty']
                        ]);
                    } else {
                        $msg = 'Stock not found for product: ' . json_encode([
                            'warehouse_id' => $warehouseId,
                            'category_id' => $product->category_id,
                            'brand_id' => $product->brand_id,
                            'model' => $product->model,
                        ]);
                        \Log::warning($msg);
                        $stockIssues[] = $msg;
                    }
                } else {
                    $msg = 'Product not found for stock debit: ' . json_encode($item);
                    \Log::warning($msg);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
}
