@extends('layouts.backend')
<!-- Add in your layout or before </body> -->

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header')

       
        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <!-- Header Button -->
                    <div class="row mb-3">
                        <div class="col text-end">
                            <a class="btn custom-orange-btn text-white" id="addPaymentBtn" href="{{ route('payment.create') }}" disabled>
                                <i class="fas fa-user-plus me-2"></i>Add Payment
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <form class="row g-3 align-items-end" method="GET" action="">
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select class="form-select select2" id="customer_id" name="customer_id" style="width:100%">
                                    <option value="">All Customer</option>
                                    @foreach(App\Models\Customer::orderBy('name')->get() as $customer)
                                        <option value="{{ $customer->id }}"
                                            data-name="{{ $customer->name }}"
                                            data-mobile="{{ $customer->mobile_no }}"
                                            data-email="{{ $customer->email }}"
                                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->mobile_no }}, {{ $customer->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>

                    <!-- Responsive Table -->
                    @if(request('from_date') || request('to_date') || request('customer_id'))
                    <div class="table-responsive" id="responsive-table">
                        <table id="invoicePaymentTable" class="table table-striped table-bordered align-middle">
                            <thead class="custom-thead text-center">
                                <tr>
                                <th scope="col">S.NO</th>
                                <th scope="col">CUSTOMER NAME</th>
                                <th scope="col">BILLED AMOUNT</th>
                                <th scope="col">AMOUNT PAID</th>
                                <th scope="col">BALANCE</th>
                                <th scope="col">PAYMENT DATE</th>
                                <th scope="col">DESCRIPTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $serial = 1; @endphp
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $serial++ }}</td>
                                        <td>{{ $payment->customer->name ?? 'Unknown Customer' }}</td>
                                        <td>₹{{ number_format($payment->grand_total) }}</td>
                                        <td>₹{{ number_format($payment->paid_amount) }}</td>
                                        <td>₹{{ number_format($payment->balance_amount) }}</td>
                                        <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : '' }}</td>
                                        <td>{{ $payment->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @php
                                $grandTotal = $payments->sum('grand_total');
                                $paidTotal = $payments->sum('paid_amount');
                                $balanceTotal = $grandTotal - $paidTotal;
                            @endphp
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    <td colspan="2" class="text-end">Grand Total</td>
                                    <td>₹{{ number_format($grandTotal) }}</td>
                                    <td>₹{{ number_format($paidTotal) }}</td>
                                    <td>₹{{ number_format($balanceTotal) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        
    </div> 

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    function matchCustom(params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }
        if (typeof data.text === 'undefined') {
            return null;
        }
        var term = params.term.toLowerCase();
        var name = $(data.element).data('name') ? $(data.element).data('name').toLowerCase() : '';
        var mobile = $(data.element).data('mobile') ? $(data.element).data('mobile').toLowerCase() : '';
        var email = $(data.element).data('email') ? $(data.element).data('email').toLowerCase() : '';
        if (name.indexOf(term) > -1 || mobile.indexOf(term) > -1 || email.indexOf(term) > -1) {
            return data;
        }
        return null;
    }
    $('#customer_id').select2({
        theme: 'default', // Use default theme for classic look
        placeholder: "Select Customer",
        allowClear: true,
        width: '100%',
        matcher: matchCustom,
        minimumResultsForSearch: 0 // Always show the search box
    });

    
});
</script>
@endpush