{{-- Modal content for Pending Reconciliation --}}
@if($invoices->count())
    <style>
    @media (max-width: 767.98px) {
        .modal-xl {
            max-width: 98vw !important;
            margin: 0;
        }
        .modal-content {
            border-radius: 0.7rem;
            padding: 0.5rem 0.2rem;
        }
        .table-responsive {
            font-size: 0.95rem;
        }
        table.table {
            min-width: 700px;
        }
        .modal-header, .modal-body, .modal-footer {
            padding-left: 0.7rem !important;
            padding-right: 0.7rem !important;
        }
        .modal-title {
            font-size: 1.1rem;
        }
        .btn, .badge {
            font-size: 0.95rem;
        }
        ul.list-unstyled {
            padding-left: 0;
        }
    }
    </style>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Payments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $i => $invoice)
                    <tr @if($invoice->reconciliation_done) class="table-success" @endif>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->created_at->format('d-m-Y') }}</td>
                        <td>{{ $invoice->customer->name ?? '-' }}</td>
                        <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>₹{{ number_format($invoice->payments->sum('paid_amount'), 2) }}</td>
                        <td>₹{{ number_format($invoice->grand_total - $invoice->payments->sum('paid_amount'), 2) }}</td>
                        <td>
                            @if($invoice->payments->count())
                                <ul class="list-unstyled mb-0">
                                    @foreach($invoice->payments as $payment)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</span>
                                                ₹{{ number_format($payment->paid_amount, 2) }}
                                                <span class="badge bg-info text-dark">{{ $payment->payment_mode }}</span>
                                            </div>
                                            <div>
                                                @if($payment->is_confirmed)
                                                    <span class="badge bg-success">Confirmed</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No payments</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $invoice->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">View & Reconcile</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">No pending reconciliation invoices found.</div>
@endif
