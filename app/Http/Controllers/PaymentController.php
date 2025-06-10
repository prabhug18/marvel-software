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
            $currentPaid = $request->paid_amount;
            $grandTotal = $request->grand_total;
            $balance = $grandTotal - ($totalPaid + $currentPaid);
            $validated['balance_amount'] = $balance;
            $validated['user_id'] = Auth::id() ?? 1;

            \App\Models\Payment::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'Payment added successfully!');
        }
        return view('backend.modules.payment.add-payment', compact('heading'));
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
}
