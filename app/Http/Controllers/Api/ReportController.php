<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    //
    public function dashboard(Request $request)
    {
        return response()->json(['message' => 'API is working']);
    }

    
    public function transactionDetails(Request $request)
    {
        $user = Auth::user();
        $warehouseId = $user->warehouse_id ?? null;

        // Debug: Return user and warehouse info if missing
        if (!$warehouseId) {
            return response()->json([
                'error' => 'Authenticated user does not have a warehouse_id',
                'user' => $user,
            ], 400);
        }

        $from = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        // Fetch invoices for this warehouse and date range, eager load warehouse
        $invoices = Invoice::with('warehouse')
            ->where('warehouse_id', $warehouseId)
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->get();

        // Debug: Return count and sample invoice if empty
        if ($invoices->isEmpty()) {
            return response()->json([
                'message' => 'No invoices found',
                'warehouse_id' => $warehouseId,
                'from' => $from,
                'to' => $to,
                'invoice_count' => 0,
            ]);
        }

        $data = $invoices->map(function ($invoice) {
            // Get only the string before "/" in warehouse prefix
            $locationCode = $invoice->warehouse->prefix ?? '01';
            if (strpos($locationCode, '/') !== false) {
                $locationCode = explode('/', $locationCode)[0];
            }
            // TAX_AMT: (cgst + sgst) or igst
            $taxAmt = 0;
            if (isset($invoice->igst) && $invoice->igst > 0) {
                $taxAmt = $invoice->igst;
            } elseif (isset($invoice->cgst) && isset($invoice->sgst)) {
                $taxAmt = $invoice->cgst + $invoice->sgst;
            } elseif (isset($invoice->tax_amount)) {
                $taxAmt = $invoice->tax_amount;
            }

            // Fetch payment details (assuming relation: payments)
            $payments = $invoice->payments ?? [];
            $paymentDetails = collect($payments)->map(function ($payment) {
                return [
                    'PAYMENT_NAME'    => $payment->payment_mode ?? 'CASH',
                    'CURRENCY_CODE'   => $payment->currency_code ?? 'INR',
                    'EXCHANGE_RATE'   => $payment->exchange_rate ?? 1,
                    'TENDER_AMOUNT'   => $payment->paid_amount ?? $payment->tender_amount ?? $payment->amount ?? 0,
                    'OP_CUR'          => $payment->operational_currency ?? 'INR',
                    'BC_EXCH'         => $payment->bc_exchange_rate ?? 1,
                    'PAYMENT_STATUS'  => $payment->payment_status ?? 'SALES',
                ];
            });
            if ($paymentDetails->isEmpty()) {
                // fallback to invoice fields if no payments found
                $paymentDetails = [[
                    'PAYMENT_NAME'    => $invoice->payment_mode ?? 'CASH',
                    'CURRENCY_CODE'   => $invoice->currency_code ?? 'INR',
                    'EXCHANGE_RATE'   => $invoice->exchange_rate ?? 1,
                    'TENDER_AMOUNT'   => $invoice->paid_amount ?? $invoice->tender_amount ?? $invoice->grand_total,
                    'OP_CUR'          => $invoice->operational_currency ?? 'INR',
                    'BC_EXCH'         => $invoice->bc_exchange_rate ?? 1,
                    'PAYMENT_STATUS'  => $invoice->payment_status ?? 'SALES',
                ]];
            }

            return [
                'LOCATION_CODE'   => $locationCode,
                'TERMINAL_ID'     => $invoice->terminal_id ?? '01',
                'SHIFT_NO'        => $invoice->shift_no ?? '01',
                'RCPT_NUM'        => $invoice->invoice_number,
                'RCPT_DT'         => $invoice->created_at->format('Ymd'),
                'BUSINESS_DT'     => $invoice->business_date ? $invoice->business_date->format('Ymd') : $invoice->created_at->format('Ymd'),
                'RCPT_TM'         => $invoice->created_at->format('His'),
                'INV_AMT'         => $invoice->grand_total,
                'TAX_AMT'         => $taxAmt,
                'RET_AMT'         => $invoice->return_amount ?? 0,
                'payment_details' => $paymentDetails,
            ];
        });

        return response()->json([
            'warehouse_id' => $warehouseId,
            'from' => $from,
            'to' => $to,
            'invoice_count' => $invoices->count(),
            'transactions' => $data,
        ]);
    }
    
}
