@if(count($invoices))
@foreach($invoices as $index => $invoice)
<tr @if($invoice->reconciliation_done) style="background-color: #e6ffe6;" title="Reconciliation Done" @endif>
    <td><span class="mobile-value">{{ ($invoices->firstItem() ?? 0) + $index }}</span></td>
    <td><span class="mobile-value">{{ $invoice->date ?? $invoice->created_at->format('d-m-Y') }}</span></td>
    <td><span class="mobile-value">{{ $invoice->customer_name ?? ($invoice->customer->name ?? '-') }}</span></td>
    <td><span class="mobile-value">{{ $invoice->invoice_number }}</span></td>
    <td><span class="mobile-value">₹{{ number_format($invoice->grand_total, 2) }}</span></td>
    <td class="text-center"><span class="mobile-value">
        <a href="{{ url('invoice-view?invoice_id=' . $invoice->id) }}" target="blank" title="View"><i class="fas fa-eye text-primary mx-1"></i></a>
        @if(auth()->user() && auth()->user()->hasRole('Admin'))
            @if(!$invoice->reconciliation_done)
                <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $invoice->id) }}" title="Payment"><i class="fas fa-rupee-sign text-success mx-1"></i></a>
            @endif
            @if($invoice->reconciliation_done)
                <span class="ms-1"><i class="fas fa-check"></i></span>
            @endif
        @endif
    </span></td>
</tr>
@endforeach
@endif
