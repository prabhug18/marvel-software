@if(count($invoices))
@foreach($invoices as $index => $invoice)
    @php
        $invoiceTemplateSelected = \App\Models\Setting::get('invoice_template')
            ?? (isset($invoiceTemplate) ? $invoiceTemplate : ($invoice->template ?? config('invoice.template') ?? null));

        if ($invoiceTemplateSelected && !in_array(strtolower($invoiceTemplateSelected), ['default', 'legacy', 'inline', ''])) {
            // For templates like 'gst' -> use /invoice/{id}/{template}
            $viewUrl = url("invoice/{$invoice->id}/" . strtolower($invoiceTemplateSelected));
        } else {
            // Legacy/default behaviour
            $viewUrl = url('invoice-view?invoice_id=' . $invoice->id);
        }
    @endphp
<tr @if($invoice->reconciliation_done) style="background-color: #e6ffe6;" title="Reconciliation Done" @endif data-invoice-view-url="{{ $viewUrl }}">
    <td><span class="mobile-value">{{ ($invoices->firstItem() ?? 0) + $index }}</span></td>
    <td><span class="mobile-value">{{ $invoice->date ?? $invoice->created_at->format('d-m-Y') }}</span></td>
    <td><span class="mobile-value">{{ $invoice->customer_name ?? ($invoice->customer->name ?? '-') }}</span></td>
    <td><span class="mobile-value">{{ $invoice->invoice_number }}</span></td>
    <td><span class="mobile-value">₹{{ number_format($invoice->grand_total, 2) }}</span></td>
    <td class="text-center"><span class="mobile-value">
        {{-- Only allow viewing when invoice is approved --}}
        @if(isset($invoice->status) && $invoice->status === 'approved')
            <a href="{{ $viewUrl }}" target="_blank" rel="noopener" title="View ({{ $invoiceTemplateSelected ?? 'default' }})"><i class="fas fa-eye text-primary mx-1"></i></a>
        @endif
        @if(auth()->user() && auth()->user()->hasRole('Admin'))
            <a href="{{ route('invoice.edit', $invoice->id) }}" title="Edit"><i class="fas fa-edit text-warning mx-1"></i></a>
            @if(!$invoice->reconciliation_done)
                <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $invoice->id) }}" title="Payment"><i class="fas fa-rupee-sign text-success mx-1"></i></a>
            @endif
            @if($invoice->reconciliation_done)
                <span class="ms-1"><i class="fas fa-check"></i></span>
            @endif
            {{-- Approval button/status --}}
            @if($invoice->status !== 'approved')
                <a href="#" class="invoice-approve-btn ms-2" data-id="{{ $invoice->id }}" title="Approve" role="button" aria-label="Approve invoice" tabindex="0">
                    <i class="fas fa-check-circle text-success" aria-hidden="true"></i>
                </a>
                <small class="d-block text-warning mt-1 invoice-status-text">Status: {{ ucfirst($invoice->status ?? 'pending') }}</small>
            @else
                <small class="d-block text-success mt-1 invoice-status-text">Status: Approved</small>
            @endif
        @endif
    </span></td>
</tr>
@endforeach
@endif
