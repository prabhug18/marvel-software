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
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * AJAX: Search for invoices and customers for auto-suggestion
     */
    public function ajaxSearch(Request $request)
    {
        $q = $request->input('q');
        $results = [];
        // Search customers
        $customers = \App\Models\Customer::where('name', 'like', "%$q%")
            ->orWhere('email', 'like', "%$q%")
            ->orWhere('mobile_no', 'like', "%$q%")
            ->limit(5)
            ->get();
        foreach ($customers as $customer) {
            $results[] = [
                'id' => 'customer_' . $customer->id,
                'type' => 'customer',
                'display' => $customer->name. ($customer->customer_type ? '[' . $customer->customer_type . '] ' : '') ,
                'subtext' =>  $customer->email . ' | ' . $customer->mobile_no,
            ];
        }
        // Search invoices
        $invoices = \App\Models\Invoice::where('invoice_number', 'like', "%$q%")
            ->orWhereHas('customer', function($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('mobile_no', 'like', "%$q%") ;
            })
            ->limit(5)
            ->get();
        foreach ($invoices as $invoice) {
            $results[] = [
                'id' => 'invoice_' . $invoice->id,
                'type' => 'invoice',
                'display' => $invoice->invoice_number,
                'subtext' => ($invoice->customer->name ?? '') . ' | ' . ($invoice->customer->email ?? '') . ' | ' . ($invoice->customer->mobile_no ?? ''),
            ];
        }
        return response()->json($results);
    }

    /**
     * AJAX: Check Battery Warranty (Vehicle-based logic)
     */
    public function checkWarranty(Request $request)
    {
        $serialNo = $request->input('serial_no');
        if (empty($serialNo)) {
            return response()->json(['status' => 'Error', 'message' => 'Please enter a serial number.']);
        }

        // Search for the serial number
        $item = \App\Models\InvoiceItems::with(['invoice', 'product'])
            ->where('serial_no', 'LIKE', '%'.$serialNo.'%')
            ->orderBy('id', 'desc')
            ->first();

        // Ensure exact match in case of comma separated
        if ($item && $item->serial_no) {
            $serials = array_map('trim', explode(',', $item->serial_no));
            if (!in_array(trim($serialNo), $serials)) {
                $item = null;
            }
        }

        if (!$item) {
            return response()->json(['status' => 'Not Sold', 'badge' => 'secondary', 'message' => 'Serial number not found in any invoices.']);
        }

        if (!$item->invoice || !$item->product) {
            return response()->json(['status' => 'Error', 'badge' => 'danger', 'message' => 'Invoice or Product details missing.']);
        }

        $invoiceDate = \Carbon\Carbon::parse($item->invoice->invoice_date);
        $monthsPassed = $invoiceDate->diffInMonths(\Carbon\Carbon::now());
        
        $focRaw = $item->product->foc_months ?? '0';
        $prorataRaw = $item->product->prorata_months ?? '0';
        
        // Extract only digits from strings like '24M'
        $focMonths = (int) preg_replace('/[^0-9]/', '', $focRaw);
        $prorataMonths = (int) preg_replace('/[^0-9]/', '', $prorataRaw);
        
        $totalWarranty = $focMonths + $prorataMonths;

        if ($totalWarranty == 0) {
            return response()->json([
                'status' => 'Unknown', 
                'badge' => 'secondary', 
                'message' => 'Warranty period not defined for this product.', 
                'invoice_date' => $invoiceDate->format('d/m/Y')
            ]);
        }

        if ($monthsPassed <= $focMonths) {
            $status = 'Inside FOC';
            $badge = 'success';
        } elseif ($monthsPassed <= $totalWarranty) {
            $status = 'Inside Pro-rata';
            $badge = 'warning';
        } else {
            $status = 'Out of Warranty';
            $badge = 'danger';
        }

        return response()->json([
            'status' => $status,
            'badge' => $badge,
            'invoice_date' => $invoiceDate->format('d/m/Y'),
            'months_passed' => $monthsPassed,
            'foc_months' => $focMonths,
            'prorata_months' => $prorataMonths,
            'customer' => $item->invoice->customer_name,
            'warranty_end' => $invoiceDate->copy()->addMonths($totalWarranty)->format('d/m/Y')
        ]);
    }

    /**
     * AJAX: Return details for modal popup (customer, invoice, payment, reconciliation)
     */
    public function ajaxDetails(Request $request)
    {
        $id = $request->input('id');
        $html = '';
        if (strpos($id, 'customer_') === 0) {
            $customerId = (int)str_replace('customer_', '', $id);
            $customer = \App\Models\Customer::with(['invoices', 'invoices.payments'])->find($customerId);
            if (!$customer) return '<div class="p-4 text-danger">Customer not found.</div>';
            $html .= view('backend.modules.invoice.partials.customer_details', compact('customer'))->render();
        } elseif (strpos($id, 'invoice_') === 0) {
            $invoiceId = (int)str_replace('invoice_', '', $id);
            $invoice = \App\Models\Invoice::with(['customer', 'items', 'payments'])->find($invoiceId);
            if (!$invoice) return '<div class="p-4 text-danger">Invoice not found.</div>';
            $html .= view('backend.modules.invoice.partials.invoice_details', compact('invoice'))->render();
        } else {
            $html = '<div class="p-4 text-danger">Invalid selection.</div>';
        }
        return $html;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $heading = "Invoice View";
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            $invoices = \App\Models\Invoice::orderBy('created_at', 'desc')->paginate(10);
        } else {
            $userWarehouseId = Auth::user()->warehouse_id ?? null;
            $invoices = \App\Models\Invoice::where('warehouse_id', $userWarehouseId)
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
            'address' => 'nullable',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'nullable',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'vehicle_type' => 'nullable|string',
            'vehicle_details' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.serial_no' => 'nullable|string',
        ]);

        // ...existing customer logic...
        // ...existing invoice creation logic...

        // (move this block after invoice is created)

        // Find or create customer
        $customer = \App\Models\Customer::where('mobile_no', $request->mobile_no)           
                    ->first();
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'name' => $request->customer_name,
                'mobile_no' => $request->mobile_no,
                'email' => $request->email ?? null,
                'address' => $request->address ?? null,
                'state_id' => $request->state ?? null,
                'city_id' => $request->city ?? null,
                'pincode' => $request->pincode ?? null,
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
            $warehouse_id = $request->warehouse_id;
        } else {
            $warehouse_id = Auth::user()->warehouse_id ?? null;
        }
        // Start Database Transaction for all inserts
        return DB::transaction(function () use ($request, $cgst, $sgst, $igst, $grand_total, $warehouse_id, $customer) {
            // --- Invoice Number Generation and Sequence Increment ---
            $warehouse = \App\Models\Warehouse::find($warehouse_id);
        $prefix = $warehouse->prefix;
            $sequence = \App\Models\WarehouseInvoiceSequence::firstOrCreate(
                ['warehouse_id' => $warehouse->id],
                ['current_number' => 1001]
            );
            // Lock the sequence row to prevent race conditions
            $sequence = \App\Models\WarehouseInvoiceSequence::where('warehouse_id', $warehouse->id)->lockForUpdate()->first();
            
            $nextNumber = $sequence->current_number;
            $invoiceNumber = $prefix . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            // Increment for next time (only here!)
            $sequence->current_number = $nextNumber + 1;
            $sequence->save();
            
            $invoice = \App\Models\Invoice::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id() ?? 1,
                'customer_name' => $customer->name ?? $request->customer_name,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $request->invoice_date,
                'dc_number' => $request->dc_number,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'grand_total' => $grand_total,
            'warehouse_id' => $warehouse_id,
            'vehicle_type' => $request->vehicle_type ?? null,
            'vehicle_details' => $request->vehicle_details ?? null,
        ]);

        // Delivery Address logic (after invoice is created)
        if ($request->input('delivery_address_option') === 'new' && isset($invoice)) {
            \App\Models\DeliveryAddress::create([
                'invoice_id' => $invoice->id,
                'address' => $request->input('delivery_address'),
                'state_id' => $request->input('delivery_state'),
                'city_id' => $request->input('delivery_city'),
                'pincode' => $request->input('delivery_pincode'),
            ]);
        }

        // Store dynamic payment fields (multiple payments)
        $paidAmounts = $request->input('paid_amount', []);
        $paymentModes = $request->input('payment_mode', []);
        $paymentDate = $request->invoice_date;
        $userId = Auth::id() ?? 1;
        $invoiceNumber = $request->invoice_number;
        $grandTotal = $grand_total;
        $totalPaid = 0;
        $hasValidPayment = false;
        // Accept both JSON and form-data
        if ($request->isJson()) {
            $data = $request->json()->all();
            $paidAmounts = $data['paid_amount'] ?? $paidAmounts;
            $paymentModes = $data['payment_mode'] ?? $paymentModes;
        }
        // Ensure both are arrays
        $paidAmounts = is_array($paidAmounts) ? $paidAmounts : [$paidAmounts];
        $paymentModes = is_array($paymentModes) ? $paymentModes : [$paymentModes];
        if (count($paidAmounts) > 0) {
            foreach ($paidAmounts as $i => $amt) {
                $mode = $paymentModes[$i] ?? null;
                $amt = is_numeric($amt) ? floatval($amt) : 0;
                if ($amt > 0 && $mode) {
                    $totalPaid += $amt;
                    $currentBalance = $grandTotal - $totalPaid;
                    $hasValidPayment = true;
                    
                    \App\Models\Payment::create([
                        'invoice_id' => $invoice->id,
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name ?? $request->customer_name,
                        'user_id' => $userId,
                        'paid_amount' => $amt,
                        'balance_amount' => $currentBalance,
                        'grand_total' => $grandTotal,
                        'payment_date' => $paymentDate,
                        'warehouse_id' => $warehouse_id,
                        'payment_mode' => $mode,
                        'invoice_number' => $invoiceNumber,
                        'description' => 'Invoice Payment',
                    ]);
                }
            }
        }
        if (!$hasValidPayment) {
            // Fallback: create a default payment entry with 0 paid (credit)
            \App\Models\Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name ?? $request->customer_name,
                'user_id' => $userId,
                'paid_amount' => 0,
                'balance_amount' => $grandTotal,
                'grand_total' => $grandTotal,
                'payment_date' => $paymentDate,
                'warehouse_id' => $warehouse_id,
                // payment_mode must not be null in DB; use 'NA' to indicate no payment yet
                'payment_mode' => 'NA',
                'invoice_number' => $invoiceNumber,
                'description' => 'Invoice Payment',
            ]);
        }

        // Create invoice items
        $stockIssues = [];
        foreach ($request->products as $item) {
            // Calculate CGST and SGST per item (assuming 50% of tax_amount each)
            $taxAmount = isset($item['tax_amount']) ? $item['tax_amount'] : 0;
            $cgstAmount = $sgstAmount = 0;
            if ($taxAmount > 0) {
                $cgstAmount = $sgstAmount = $taxAmount / 2;
            }
            // Defensive: resolve product_id if missing by model, then ensure product exists before inserting
            $productIdToSave = $item['product_id'] ?? null;
            if (!$productIdToSave && !empty($item['model'])) {
                $found = \App\Models\Product::where('model', $item['model'])->first();
                if ($found) {
                    $productIdToSave = $found->id;
                }
            }
            if ($productIdToSave) {
                $prodExists = \App\Models\Product::find($productIdToSave);
                if (!$prodExists) {
                    $productIdToSave = null; // avoid FK constraint failure
                    $stockIssues[] = 'Referenced product_id not found for invoice item: ' . json_encode($item);
                }
            }

            \App\Models\InvoiceItems::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id() ?? 1,
                'product_id' => $productIdToSave,
                'product_name' => $item['name'] ?? $item['product_name'] ?? '',
                'model' => $item['model'] ?? '',
                'model_no' => $item['model_no'] ?? null,
                'serial_no' => $item['serial_no'] ?? null,
                'qty' => $item['qty'],
                'tax_percentage' => $item['tax_percentage'] ?? 5,
                'tax_amount' => $taxAmount,
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
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
                        $stock->qty = $stock->qty - $item['qty'];
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
        }); // End DB Transaction
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
        $invoice = \App\Models\Invoice::with(['customer', 'customer.city', 'items', 'payments'])->findOrFail($id);
        $states = \App\Models\State::where('country_id', '101')->get();
        $warehouses = \App\Models\Warehouse::all();
        // Map payments to array with amount and mode for JS prefill
        $invoicePayments = $invoice->payments->map(function($p) {
            return [
                'amount' => $p->paid_amount ?? $p->amount ?? '',
                'mode' => $p->payment_mode ?? $p->mode ?? '',
            ];
        })->toArray();
        return view('backend.modules.invoice.edit', compact('heading','invoice', 'states', 'warehouses', 'invoicePayments'));
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

        // If AJAX/JSON request
        if ($request->isJson()) {
            $data = $request->json()->all();
            // Validate input
            $validator = \Validator::make($data, [
                'customer_name' => 'required|string',
                'mobile_no' => 'required|string',
                'address' => 'nullable|string',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required',
                'invoice_number' => 'required|string',
                'invoice_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*.serial_no' => 'nullable|string',
                'products.*.qty' => 'required|integer|min:1',
                'products.*.unit_price' => 'required|numeric|min:0',
                // payments are optional here; handled in Payment Reconciliation
                'paid_amount' => 'sometimes|array',
                'payment_mode' => 'sometimes|array',
                'cgst' => 'required|numeric',
                'sgst' => 'required|numeric',
                'igst' => 'required|numeric',
                'grand_total' => 'required|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            try {
                DB::transaction(function () use ($data, $id) {
                    $invoice = \App\Models\Invoice::findOrFail($id);
                    $customer = \App\Models\Customer::find($invoice->customer_id);
                    if ($customer) {
                        $customer->update([
                            'name' => $data['customer_name'],
                            'mobile_no' => $data['mobile_no'],
                            'email' => $data['email'] ?? null,
                            'address' => $data['address'],
                            'state_id' => $data['state'],
                            'city_id' => $data['city'],
                            'pincode' => $data['pincode'],
                            'gst_no' => $data['gst_number'] ?? null,
                        ]);
                    }
                    $invoice->update([
                        'customer_name' => $data['customer_name'],
                        'invoice_number' => $data['invoice_number'],
                        'invoice_date' => $data['invoice_date'],
                        'dc_number' => $data['dc_number'] ?? null,
                        'warehouse_id' => $data['warehouse_id'],
                        'vehicle_type' => $data['vehicle_type'] ?? null,
                        'vehicle_details' => $data['vehicle_details'] ?? null,
                        'cgst' => (float)($data['cgst'] ?? 0),
                        'sgst' => (float)($data['sgst'] ?? 0),
                        'igst' => (float)($data['igst'] ?? 0),
                        'grand_total' => (float)($data['grand_total'] ?? 0),
                    ]);
                    // --- Stock adjustment logic ---
                    // Fetch original items (before update)
                    $originalItems = \App\Models\InvoiceItems::where('invoice_id', $invoice->id)->get();
                    // Key by product_id + serial_no for uniqueness
                    $originalMap = collect($originalItems)->keyBy(function($item) {
                        $pid = $item->product_id ?? null;
                        $model = $item->model ?? '';
                        $serial = $item->serial_no ?? '';
                        return ($pid ? $pid : $model) . '-' . $serial;
                    });
                    $updatedMap = collect($data['products'])->keyBy(function($item) {
                        $pid = $item['product_id'] ?? null;
                        $model = $item['model'] ?? '';
                        $serial = $item['serial_no'] ?? '';
                        return ($pid ? $pid : $model) . '-' . $serial;
                    });
                    // Find new products (in updated, not in original)
                    $newProducts = $updatedMap->diffKeys($originalMap);
                    // Find removed products (in original, not in updated)
                    $removedProducts = $originalMap->diffKeys($updatedMap);
                    // Remove old items
                    \App\Models\InvoiceItems::where('invoice_id', $invoice->id)->delete();
                    // Insert new items
                    foreach ($data['products'] as $item) {
                        // Calculate CGST and SGST per item (assuming 50% of tax_amount each)
                        $taxAmount = $item['tax_amount'] ?? 0;
                        $cgstAmount = $sgstAmount = 0;
                        if ($taxAmount > 0) {
                            $cgstAmount = $sgstAmount = $taxAmount / 2;
                        }
                        // Defensive: resolve product_id if missing by model, then ensure product exists before inserting
                        $productIdToSave = $item['product_id'] ?? null;
                        if (!$productIdToSave && !empty($item['model'])) {
                            $found = \App\Models\Product::where('model', $item['model'])->first();
                            if ($found) {
                                $productIdToSave = $found->id;
                            }
                        }
                        if ($productIdToSave) {
                            $prodExists = \App\Models\Product::find($productIdToSave);
                            if (!$prodExists) {
                                $productIdToSave = null;
                                $removed = 'Referenced product_id not found for updated invoice item: ' . json_encode($item);
                                logger()->warning($removed);
                            }
                        }

                        \App\Models\InvoiceItems::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => Auth::id() ?? 1,
                            'product_id' => $productIdToSave,
                            'product_name' => $item['name'] ?? $item['product_name'] ?? '',
                            'model' => $item['model'] ?? '',
                            'model_no' => $item['model_no'] ?? null,
                            'serial_no' => $item['serial_no'] ?? null,
                            'qty' => $item['qty'],
                            'tax_percentage' => $item['tax_percentage'] ?? 5,
                            'tax_amount' => $taxAmount,
                            'unit_price' => $item['unit_price'],
                            'total' => $item['total'] ?? 0,
                            'cgst_amount' => $cgstAmount,
                            'sgst_amount' => $sgstAmount,
                        ]);
                    }
                    $warehouseId = $data['warehouse_id'] ?? (Auth::user()->warehouse_id ?? null);
                    // Debit stock for newly added products
                    foreach ($newProducts as $item) {
                        $product = null;
                        if (isset($item['product_id'])) {
                            $product = \App\Models\Product::find($item['product_id']);
                        } elseif (!empty($item['model'])) {
                            $product = \App\Models\Product::where('model', $item['model'])->first();
                        }
                        if ($product && $warehouseId) {
                            $stockQuery = \App\Models\Stock::where('warehouse_id', $warehouseId)
                                ->where('category_id', $product->category_id)
                                ->where('brand_id', $product->brand_id)
                                ->where('model', $product->model);
                            $stock = $stockQuery->first();
                            if ($stock) {
                                $stock->qty = max(0, $stock->qty - $item['qty']);
                                $stock->save();
                            }
                        }
                    }
                    // Increment stock for removed products
                    foreach ($removedProducts as $item) {
                        $product = null;
                        if (isset($item->product_id)) {
                            $product = \App\Models\Product::find($item->product_id);
                        } elseif (!empty($item->model)) {
                            $product = \App\Models\Product::where('model', $item->model)->first();
                        }
                        if ($product && $warehouseId) {
                            $stockQuery = \App\Models\Stock::where('warehouse_id', $warehouseId)
                                ->where('category_id', $product->category_id)
                                ->where('brand_id', $product->brand_id)
                                ->where('model', $product->model);
                            $stock = $stockQuery->first();
                            if ($stock) {
                                $stock->qty = $stock->qty + $item->qty;
                                $stock->save();
                            }
                        }
                    }
                    // Update payments
                    \App\Models\Payment::where('invoice_id', $invoice->id)->delete();
                    $paidAmounts = $data['paid_amount'] ?? [];
                    $paymentModes = $data['payment_mode'] ?? [];
                    $grandTotal = $data['grand_total'];
                    $totalPaid = 0;
                    for ($i = 0; $i < count($paidAmounts); $i++) {
                        $amt = is_numeric($paidAmounts[$i]) ? floatval($paidAmounts[$i]) : 0;
                        $mode = $paymentModes[$i] ?? null;
                        if ($amt > 0 && $mode) {
                            $totalPaid += $amt;
                            $currentBalance = $grandTotal - $totalPaid;
                            \App\Models\Payment::create([
                                'invoice_id' => $invoice->id,
                                'customer_id' => $customer->id,
                                'customer_name' => $customer->name ?? $data['customer_name'],
                                'user_id' => Auth::id() ?? 1,
                                'paid_amount' => $amt,
                                'balance_amount' => $currentBalance,
                                'grand_total' => $grandTotal,
                                'payment_date' => $data['invoice_date'],
                                'warehouse_id' => $data['warehouse_id'],
                                'payment_mode' => $mode,
                                'invoice_number' => $data['invoice_number'],
                                'description' => 'Invoice Payment',
                            ]);
                        }
                    }
                    if ($totalPaid == 0) {
                        // Fallback: create a default payment entry with 0 paid (credit)
                        \App\Models\Payment::create([
                            'invoice_id' => $invoice->id,
                            'customer_id' => $customer->id,
                            'customer_name' => $customer->name ?? $data['customer_name'],
                            'user_id' => Auth::id() ?? 1,
                            'paid_amount' => 0,
                            'balance_amount' => $grandTotal,
                            'grand_total' => $grandTotal,
                            'payment_date' => $data['invoice_date'],
                            'warehouse_id' => $data['warehouse_id'],
                            // payment_mode must not be null in DB; use 'NA' to indicate no payment yet
                            'payment_mode' => 'NA',
                            'invoice_number' => $data['invoice_number'],
                            'description' => 'Invoice Payment',
                        ]);
                    }
                });
                return response()->json(['success' => true, 'invoice_id' => $id]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // Fallback: normal form submission
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'mobile_no' => 'required|string',
            'address' => 'nullable|string',
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
                'vehicle_type' => $request->vehicle_type ?? null,
                'vehicle_details' => $request->vehicle_details ?? null,
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
                        // Calculate CGST and SGST per item (assuming 50% of tax_amount each)
                        $taxAmount = $item['tax_amount'] ?? 0;
                        $cgstAmount = $sgstAmount = 0;
                        if ($taxAmount > 0) {
                            $cgstAmount = $sgstAmount = $taxAmount / 2;
                        }
                        // Defensive: resolve product_id if missing by model, then ensure product exists before inserting
                        $productIdToSave = $item['product_id'] ?? null;
                        if (!$productIdToSave && !empty($item['model'])) {
                            $found = \App\Models\Product::where('model', $item['model'])->first();
                            if ($found) {
                                $productIdToSave = $found->id;
                            }
                        }
                        if ($productIdToSave) {
                            $prodExists = \App\Models\Product::find($productIdToSave);
                            if (!$prodExists) {
                                $productIdToSave = null;
                                logger()->warning('Referenced product_id not found for invoice item (form submit): ' . json_encode($item));
                            }
                        }

                        \App\Models\InvoiceItems::create([
                            'invoice_id' => $invoice->id,
                            'user_id' => Auth::id() ?? 1,
                                'product_id' => $productIdToSave,
                            'product_name' => $item['name'] ?? $item['product_name'] ?? '',
                            'model' => $item['model'] ?? '',
                            'model_no' => $item['model_no'] ?? null,
                            'serial_no' => $item['serial_no'] ?? null,
                            'qty' => $item['qty'] ?? 1,
                            'tax_percentage' => $item['tax_percentage'] ?? 5,
                            'tax_amount' => $taxAmount,
                            'unit_price' => $item['unit_price'],
                            'total' => $item['total'] ?? 0,
                            'cgst_amount' => $cgstAmount,
                            'sgst_amount' => $sgstAmount,
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
     * Generate next invoice number based on warehouse sequence table (PREVIEW ONLY, DO NOT INCREMENT)
     */
    public function generateInvoiceNumber(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        if ($warehouseId) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
        } else {
            $user = Auth::user();
            $warehouse = \App\Models\Warehouse::find($user->warehouse_id);
        }
        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse not found'], 404);
        }
        $prefix = $warehouse->prefix;
        $sequence = \App\Models\WarehouseInvoiceSequence::firstOrCreate(
            ['warehouse_id' => $warehouse->id],
            ['current_number' => 1001]
        );
        $nextNumber = $sequence->current_number;
        $invoiceNumber = $prefix . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        // DO NOT increment here!
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

    /**
     * Show a single invoice view page based on selected template setting
     */
    public function viewInvoice(Request $request)
    {
        $heading = "Invoice Details";
        $invoiceId = $request->invoice_id;
        
        // Load all relationships that might be needed by any template
        $invoice = \App\Models\Invoice::with([
            'customer.state',
            'items.product',
            'warehouse',
            'deliveryAddress',
            'payments'
        ])->find($invoiceId);

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        // Get the selected template from settings
        $template = \App\Models\Setting::get('invoice_template', 'default');
        
        // Map template setting to view file
        $viewMap = [
            'default' => 'backend.modules.invoice.invoice-view',
            'gst'     => 'backend.modules.invoice.invoice-view-two',
            'three'   => 'backend.modules.invoice.invoice-view-three',
            'four'    => 'backend.modules.invoice.invoice-view-four',
        ];

        $view = $viewMap[$template] ?? $viewMap['default'];

        // Consistent approval check for all templates
        if ($invoice->status !== 'approved') {
            if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
                abort(403, 'Invoice not approved for viewing details.');
            }
        }

        return view($view, compact('invoice', 'heading'));
    }

    /**
     * Handle AJAX request to send invoice PDF to customer email
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'email' => 'required|email',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $invoice = \App\Models\Invoice::with(['customer'])->findOrFail($request->invoice_id);

        if ($invoice->status !== 'approved') {
            if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
                return response()->json(['success' => false, 'message' => 'Invoice not approved for sending.'], 403);
            }
        }

        $customerEmail = $request->email;
        $pdfFile = $request->file('pdf');
        
        // Save PDF to a temporary directory in storage/app/attachments
        $subDir = 'attachments/invoices';
        $fileName = 'Invoice-' . ($invoice->invoice_number ? str_replace('/', '_', $invoice->invoice_number) : $invoice->id) . '-' . time() . '.pdf';
        
        // Ensure directory exists
        if (!\Illuminate\Support\Facades\Storage::exists($subDir)) {
            \Illuminate\Support\Facades\Storage::makeDirectory($subDir);
        }
        
        $path = $pdfFile->storeAs($subDir, $fileName);
        $fullPath = storage_path('app/' . $path);

        // Dispatch email sending after the response is sent to the user
        dispatch(function () use ($customerEmail, $invoice, $fullPath) {
            try {
                \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \App\Mail\InvoiceMail($invoice, $fullPath));
                // Optional: Delete physical file after success
                if (file_exists($fullPath)) { unlink($fullPath); }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send invoice email: ' . $e->getMessage());
            }
        })->afterResponse();

        return response()->json(['success' => true, 'message' => 'Invoice is being sent to ' . $customerEmail]);
    }

    /**
     * Show the GST invoice view page
     */
    public function gstInvoiceView($id)
    {
        $invoice = \App\Models\Invoice::with(['customer.state', 'items', 'warehouse.state', 'deliveryAddress'])->find($id);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }
        $heading = 'GST Invoice';
        // Only allow printing if invoice is approved
        if ($invoice->status !== 'approved') {
            // If user is admin allow viewing but still prevent print/send unless approved
            if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
                abort(403, 'Invoice not approved for printing.');
            }
        }
        return view('backend.modules.invoice.invoice-view-two', compact('invoice', 'heading'));
    }

    /**
     * Show the Format Three invoice view page (new)
     */
    public function threeInvoiceView($id)
    {
        $invoice = \App\Models\Invoice::with(['customer', 'items', 'warehouse', 'deliveryAddress'])->find($id);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }
        $heading = 'Invoice - Format Three';
        // Only allow printing if invoice is approved
        if ($invoice->status !== 'approved') {
            if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
                abort(403, 'Invoice not approved for printing.');
            }
        }
        return view('backend.modules.invoice.invoice-view-three', compact('invoice', 'heading'));
    }

    /**
     * Show the Format Four invoice view page (custom head-office / location split)
     */
    public function fourInvoiceView($id)
    {
        $invoice = \App\Models\Invoice::with(['customer', 'items', 'warehouse', 'deliveryAddress'])->find($id);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }
        $heading = 'Invoice - Format Four';
        // Only allow printing if invoice is approved
        if ($invoice->status !== 'approved') {
            if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
                abort(403, 'Invoice not approved for printing.');
            }
        }
        return view('backend.modules.invoice.invoice-view-four', compact('invoice', 'heading'));
    }

    public function approve(Request $request, $id)
    {
        if (!(auth()->user() && auth()->user()->hasRole('Admin'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $invoice = \App\Models\Invoice::find($id);
        if (!$invoice) return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
        $invoice->status = 'approved';
        $invoice->approved_by = auth()->id();
        $invoice->approved_at = now();
        $invoice->save();
        return response()->json(['success' => true, 'message' => 'Invoice approved']);
    }
}

