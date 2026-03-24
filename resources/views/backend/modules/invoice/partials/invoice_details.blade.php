<div class="container-fluid p-3">
    <div class="row mb-3">
        <div class="col-12 text-center">
            <h4 class="modal-title mb-2" style="color:#f47820; font-weight:700;">Invoice Details</h4>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-6">
            <strong>Invoice No:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Date:</strong> {{ $invoice->invoice_date }}<br>
            <strong>Customer:</strong> {{ $invoice->customer->name ?? '-' }}<br>
            <strong>Mobile:</strong> {{ $invoice->customer->mobile_no ?? '-' }}<br>
            <strong>Email:</strong> {{ $invoice->customer->email ?? '-' }}<br>
        </div>
        <div class="col-md-6">
            <strong>Amount:</strong> {{ $invoice->grand_total }}<br>
            <strong>Warehouse:</strong> {{ $invoice->warehouse->name ?? '-' }}<br>
            <strong>GST No:</strong> {{ $invoice->customer->gst_no ?? '-' }}<br>
            <strong>Type:</strong> {{ $invoice->customer->customer_type ?? '-' }}<br>
        </div>
    </div>
    <hr>
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="mb-2" style="color:#f47820;">Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Model</th>
                            <th>Serial No</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->model }}</td>
                            <td>{{ $item->serial_no }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ $item->unit_price }}</td>
                            <td>{{ $item->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="mb-2" style="color:#f47820;">Payments</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Paid Amount</th>
                            <th>Mode</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $pay)
                        <tr>
                            <td>{{ $pay->payment_date }}</td>
                            <td>{{ $pay->paid_amount }}</td>
                            <td>{{ $pay->payment_mode }}</td>
                            <td>{{ $pay->balance_amount }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="mb-2" style="color:#f47820;">Payment Reconciliation</h5>
            <div class="alert alert-info">For detailed reconciliation, visit the <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $invoice->id) }}" target="_blank">Payment Reconciliation Page</a>.</div>
        </div>
    </div>
</div>
