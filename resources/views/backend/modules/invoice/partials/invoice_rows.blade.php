@if(count($invoices))
@foreach($invoices as $index => $invoice)
<tr>
    <td>{{ ($invoices->firstItem() ?? 0) + $index }}</td>
    <td>{{ $invoice->date ?? $invoice->created_at->format('d-m-Y') }}</td>
    <td>{{ $invoice->customer_name ?? ($invoice->customer->name ?? '-') }}</td>
    <td>{{ $invoice->invoice_number }}</td>
    <td>{{ $invoice->description ?? 'Product Added' }}</td>
    <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
    <td class="text-center">
        <a href="{{ url('payment/add-payment?invoice_id=' . $invoice->id) }}" title="Payment"><i class="fas fa-rupee-sign text-success mx-1"></i></a>
    </td>
</tr>
@endforeach
@endif
