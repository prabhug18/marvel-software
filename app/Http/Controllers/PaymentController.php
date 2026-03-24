<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function invoiceDetailsWithTotal(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = \App\Models\Invoice::with('customer')->find($invoiceId);
        if (!$invoice) {
            return response()->json(['success' => false]);
        }
        $totalPaid = \App\Models\Payment::where('invoice_id', $invoiceId)->sum('paid_amount');
        return response()->json([
            'success' => true,
            'customer_id' => $invoice->customer ? $invoice->customer->id : null,
            'customer_name' => $invoice->customer ? $invoice->customer->name : '',
            'invoice_number' => $invoice->invoice_number,
            'grand_total' => $invoice->grand_total,
            'total_paid' => $totalPaid,
            'balance_amount' => ($invoice->grand_total - $totalPaid),
        ]);
    }

    public function addPayment(Request $request)
    {
        $heading = "Add Payment";
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'customer_id' => 'nullable|integer',
                'customer_name' => 'required|string',
                'invoice_number' => 'required|string',
                'invoice_id' => 'nullable|integer',
                'grand_total' => 'required|numeric',
                'paid_amount' => 'required|numeric',
                'payment_mode' => 'required|string',
                'payment_date' => 'required|date',
                'description' => 'nullable|string',
            ]);

            // Calculate total paid so far for this invoice
            $totalPaid = \App\Models\Payment::where('invoice_id', $request->invoice_id)->sum('paid_amount');
            // Prevent overpaying: ensure this paid_amount doesn't push totalPaid over grand_total
            $invoice = null;
            if (!empty($request->invoice_id)) {
                $invoice = \App\Models\Invoice::find($request->invoice_id);
            }
            if ($invoice) {
                $remaining = $invoice->grand_total - $totalPaid;
                if (floatval($request->paid_amount) > floatval($remaining) + 0.0001) {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Paid amount exceeds remaining invoice balance.'], 422);
                    }
                    return redirect()->back()->with('error', 'Paid amount exceeds remaining invoice balance.');
                }
            }
            $currentPaid = $request->paid_amount;
            $grandTotal = $request->grand_total;
            $balance = $grandTotal - ($totalPaid + $currentPaid);
            $validated['balance_amount'] = $balance;
            $validated['user_id'] = Auth::id() ?? 1;
            // Default description for invoice payments when not provided
            if (empty($validated['description'])) {
                $validated['description'] = 'Invoice Payment';
            }

            // Ensure customer_id is set when invoice_id is present
            if (empty($validated['customer_id']) && !empty($validated['invoice_id'])) {
                $invoice = \App\Models\Invoice::find($validated['invoice_id']);
                if ($invoice && $invoice->customer_id) {
                    $validated['customer_id'] = $invoice->customer_id;
                }
            }

            \App\Models\Payment::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'Payment added successfully!');
        }
        return view('backend.modules.payment.add-payment', compact('heading'));
    }

    /**
     * Store multiple payments in bulk (AJAX)
     * Expected payload: invoice_id, payments: [{paid_amount, payment_mode, payment_date, description}]
     */
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'payments' => 'required|array|min:1',
            'payments.*.paid_amount' => 'required|numeric|min:0',
            'payments.*.payment_mode' => 'required|string',
            'payments.*.payment_date' => 'required|date',
            'payments.*.description' => 'nullable|string',
        ]);

        $invoice = \App\Models\Invoice::find($data['invoice_id']);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $customerId = $invoice->customer_id;
        $grandTotal = $invoice->grand_total;

        \DB::beginTransaction();
        try {
            foreach ($data['payments'] as $p) {
                // total paid so far (includes payments we just created in this transaction)
                $totalPaid = \App\Models\Payment::where('invoice_id', $invoice->id)->sum('paid_amount');
                $currentPaid = $p['paid_amount'];
                // Prevent overpay in bulk as well: ensure cumulative doesn't exceed grand total
                if (floatval($currentPaid) < 0) {
                    throw new \Exception('Invalid payment amount');
                }
                if (floatval($totalPaid) + floatval($currentPaid) > floatval($grandTotal) + 0.0001) {
                    throw new \Exception('One or more payments would exceed invoice total.');
                }
                $balance = $grandTotal - ($totalPaid + $currentPaid);

                \App\Models\Payment::create([
                    'customer_id' => $customerId,
                    'customer_name' => $invoice->customer ? $invoice->customer->name : null,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_id' => $invoice->id,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $currentPaid,
                    'payment_mode' => $p['payment_mode'],
                    'payment_date' => $p['payment_date'],
                    'description' => isset($p['description']) && $p['description'] !== '' ? $p['description'] : 'Invoice Payment',
                    'balance_amount' => $balance,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }
            \DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('bulkStore payments error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save payments.'], 500);
        }
    }

    public function create()
    {
        $heading = "Add Payment";
        return view('backend.modules.payment.create', compact('heading'));
    }

    public function view(Request $request)
    {
        $heading = "Payment List";
        $query = \App\Models\Payment::with('customer');

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        // Filter by from_date
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        // Filter by to_date
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        // Fix: If both from_date and to_date are present, use whereBetween for accuracy
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        }
        $payments = $query->orderBy('payment_date', 'desc')->get();
        return view('backend.modules.payment.index', compact('heading', 'payments'));
    }

    /**
     * AJAX: Get total balance for a customer (sum all balance_amount - sum all paid_amount)
     */
    public function customerBalance(Request $request)
    {
        $name = $request->input('name');
        $mobile = $request->input('mobile');
        $email = $request->input('email');
        // Find customer by name, mobile, or email
        $customer = \App\Models\Customer::where('name', $name)
            ->orWhere('mobile_no', $mobile)
            ->orWhere('email', $email)
            ->first();
        if (!$customer) {
            return response()->json(['balance' => 0]);
        }
        // Sum all balance_amount and paid_amount for this customer
        $payments = \App\Models\Payment::where('customer_id', $customer->id)->get();
        $totalBalance = $payments->sum('grand_total');
        $totalPaid = $payments->sum('paid_amount');
        $balance = $totalBalance - $totalPaid;
        return response()->json(['balance' => $balance]);
    }

    /**
     * Store payment via AJAX (for create.blade.php form)
     */
    public function ajaxStore(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'balance_amount' => 'nullable|numeric',
            'paid_amount' => 'required|numeric',
            'payment_mode' => 'required|string',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        // Find customer_id by name (or mobile/email if needed)
        $customer = \App\Models\Customer::where('name', $request->customer_name)
            ->orWhere('mobile_no', $request->customer_name)
            ->orWhere('email', $request->customer_name)
            ->first();
        $validated['customer_id'] = $customer ? $customer->id : null;
        if (!$validated['customer_id']) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 422);
        }
        $validated['grand_total'] = null;
        $validated['balance_amount'] = null;
        $validated['user_id'] = Auth::id() ?? 1;
        $payment = \App\Models\Payment::create($validated);
        return response()->json(['success' => true, 'payment' => $payment]);
    }

    /**
     * Show payment reconciliation for a specific invoice
     */
    public function paymentReconciliation(Request $request)
    {
        $heading = "Payment Reconciliation";
        $invoiceId = $request->invoice_id;
        return view('backend.modules.payment.payment-reconciliation', compact('heading', 'invoiceId'));
    }

    /**
     * Confirm a payment (set is_confirmed = true)
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
        ]);
        $payment = \App\Models\Payment::find($request->payment_id);
        if (!$payment) {
            return redirect()->back()->with('error', 'Payment not found.');
        }
        $payment->is_confirmed = true;
        $payment->save();
        return redirect()->back()->with('success', 'Payment confirmed successfully!');
    }

    /**
     * Mark reconciliation as done for an invoice (only if all payments are confirmed)
     */
    public function markReconciliation(Request $request)
    {
        $invoice = \App\Models\Invoice::find($request->invoice_id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.')->with('redirect_invoice', true);
        }
        $payments = \App\Models\Payment::where('invoice_id', $invoice->id)->get();
        if ($payments->count() == 0 || $payments->where('is_confirmed', false)->count() > 0) {
            return redirect()->back()->with('error', 'All payments must be confirmed before reconciliation.')->with('redirect_invoice', false);
        }
        $invoice->reconciliation_done = true;
        $invoice->save();
        return redirect()->back()->with('success', 'Reconciliation marked as done. You will be redirected to the invoice page shortly.')->with('redirect_invoice', true);
    }

    /**
     * Show all pending reconciliation invoices for the dashboard modal (AJAX)
     */
    public function pendingReconciliation()
    {
        // Debug: Log user info
        \Log::info('pendingReconciliation called', [
            'user_id' => auth()->id(),
            'is_authenticated' => auth()->check(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        if (!auth()->check()) {
            if (request()->ajax()) {
                return response('Unauthenticated (session/cookie issue)', 401);
            }
            abort(401, 'Unauthenticated');
        }
        $invoices = \App\Models\Invoice::with(['customer', 'payments' => function($q) {
            $q->orderBy('payment_date');
        }])
        ->where('reconciliation_done', false)
        ->orderBy('created_at', 'desc')
        ->get();
        return view('backend.modules.payment.pending-reconciliation-modal', compact('invoices'))->render();
    }
}