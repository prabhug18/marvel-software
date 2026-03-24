<div class="container-fluid p-3">
    <div class="row mb-3">
        <div class="col-12 text-center">
            <h4 class="modal-title mb-2" style="color:#f47820; font-weight:700;">Customer Details</h4>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-6">
            <strong>Name:</strong> {{ $customer->name }}<br>
            <strong>Email:</strong> {{ $customer->email ?? '-' }}<br>
            <strong>Mobile:</strong> {{ $customer->mobile_no ?? '-' }}<br>
            <strong>Type:</strong> {{ $customer->customer_type ?? '-' }}<br>
            <strong>Address:</strong> {{ $customer->address ?? '-' }}<br>
        </div>
        <div class="col-md-6">
            <strong>State:</strong> {{ $customer->state->name ?? '-' }}<br>
            <strong>City:</strong> {{ $customer->city->name ?? '-' }}<br>
            <strong>GST No:</strong> {{ $customer->gst_no ?? '-' }}<br>
        </div>
    </div>
    <hr>
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="mb-2" style="color:#f47820;">Invoices</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->invoice_date }}</td>
                            <td>{{ $inv->grand_total }}</td>
                            <td>
                                @foreach($inv->payments as $pay)
                                    <div><span class="badge bg-success">Paid: {{ $pay->paid_amount }}</span> <span class="badge bg-info">Mode: {{ $pay->payment_mode }}</span></div>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
