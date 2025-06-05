<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

            \App\Models\Payment::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->back()->with('success', 'Payment added successfully!');
        }
        return view('backend.modules.payment.add-payment', compact('heading'));
    }
}
