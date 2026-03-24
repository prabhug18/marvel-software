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
                            <a class="btn custom-orange-btn text-white d-none" id="addPaymentBtn" href="{{ route('payment.create') }}" disabled>
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
                                    @php
                                        $allCustomers = \App\Models\Customer::orderBy('name')->get();
                                        $uniqueCustomers = $allCustomers->unique('mobile_no');
                                    @endphp
                                    @foreach($uniqueCustomers as $customer)
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
                                <th scope="col">INVOICE NUMBER</th>
                                <th scope="col">BILLED AMOUNT</th>
                                <th scope="col">AMOUNT PAID</th>
                                <th scope="col">BALANCE</th>
                                <th scope="col">PAYMENT DATE</th>
                                <th scope="col">RECONCILIATION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $serial = 1;
                                    $seenInvoices = [];
                                    $uniqueGrandTotal = 0;
                                    // Preload reconciliation data for each invoice number
                                    $reconciliationData = [];
                                    foreach($payments as $payment) {
                                        if (!isset($reconciliationData[$payment->invoice_number])) {
                                            $invoice = \App\Models\Invoice::where('invoice_number', $payment->invoice_number)->first();
                                            $reconciliationData[$payment->invoice_number] = [
                                                'done' => ($invoice && $invoice->reconciliation_done) ? true : false,
                                                'id'   => $invoice ? $invoice->id : null
                                            ];
                                        }
                                    }
                                @endphp
                                @foreach($payments as $payment)
                                <tr>
                                    <td><span class="mobile-value">{{ $serial++ }}</span></td>
                                    <td><span class="mobile-value">{{ $payment->customer->name ?? 'Unknown Customer' }}</span></td>
                                    <td><span class="mobile-value">{{ $payment->invoice_number ?? '-' }}</span></td>
                                    <td>
                                        <span class="mobile-value">
                                        @if(!in_array($payment->invoice_number, $seenInvoices))
                                            ₹{{ number_format($payment->grand_total) }}
                                            @php 
                                                $seenInvoices[] = $payment->invoice_number;
                                                $uniqueGrandTotal += $payment->grand_total;
                                            @endphp
                                        @else
                                            -
                                        @endif
                                        </span>
                                    </td>
                                    <td><span class="mobile-value">₹{{ number_format($payment->paid_amount) }}</span></td>
                                    <td><span class="mobile-value">₹{{ number_format($payment->balance_amount) }}</span></td>
                                    <td><span class="mobile-value">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : '' }}</span></td>
                                    <td>
                                        <span class="mobile-value">
                                        @php
                                            $rData = $reconciliationData[$payment->invoice_number] ?? ['done' => false, 'id' => null];
                                        @endphp
                                        
                                        @if($rData['id'])
                                            <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $rData['id']) }}" style="text-decoration: none;" title="Go to Reconciliation">
                                                @if($rData['done'])
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </a>
                                        @else
                                            @if($rData['done'])
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-danger">No</span>
                                            @endif
                                        @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @php
                                $paidTotal = $payments->sum('paid_amount');
                                $balanceTotal = $uniqueGrandTotal - $paidTotal;
                            @endphp
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    
                                    <td colspan="3" class="text-end">Grand Total</td>
                                    <td>₹{{ number_format($uniqueGrandTotal) }}</td>
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
<!-- Select2 CSS (should be loaded before any JS and before your main app CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <!-- Strong custom Select2 CSS to enforce Bootstrap 4 theme and layout -->
    <style>
        .select2-container--bootstrap4 .select2-selection {
          border-radius: 0.25rem !important;
          min-height: 44px !important;
          border: 1px solid #ced4da !important;
          background-color: rgb(249, 249, 249) !important;
          font-size: 15px !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
          line-height: 2.9 !important;
          float: left !important;
        }
        .select2-container--bootstrap4 .select2-selection--single {
          height: 50px !important;
        }
        .select2-container--bootstrap4 .select2-selection--multiple {
          min-height: 45px !important;
        }
        .select2-container {
          width: 100% !important;
          z-index: 1060 !important;
        }
        .select2-dropdown {
          z-index: 2000 !important;
        }
    </style>
    <!-- Your main app CSS (should come after Select2 CSS) -->
    <link href="/assets/build/app.css" rel="stylesheet" />
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#customer_id').select2({
        theme: 'bootstrap4',
        placeholder: "Select Customer",
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0
    });
});
</script>
<style>
@media (max-width: 767.98px) {
  .table-responsive {
    font-size: 0.95rem;
  }
  #invoicePaymentTable, #invoicePaymentTable thead, #invoicePaymentTable tbody, #invoicePaymentTable th, #invoicePaymentTable tr {
    display: block;
    width: 100%;
  }
  #invoicePaymentTable thead {
    display: none;
  }
  #invoicePaymentTable tr {
    margin-bottom: 1.2rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    background: #fff;
    padding: 0.5rem;
  }
  #invoicePaymentTable td {
    display: block;
    padding: 0.5rem 0.5rem 0.5rem 0.5rem;
    border: none;
    border-bottom: 1px solid #eee;
    position: relative;
    min-height: 40px;
    word-break: break-word;
    white-space: normal;
    background: #fff;
  }
  #invoicePaymentTable td:before {
    content: attr(data-label);
    display: block;
    font-weight: bold;
    color: #f47820;
    margin-bottom: 0.2rem;
    font-size: 0.98em;
    white-space: pre-line;
    word-break: break-word;
  }
  #invoicePaymentTable td .mobile-value {
    margin-left: 100px;
    display: inline-block;
  }
  #invoicePaymentTable td:last-child {
    border-bottom: none;
  }
}
</style>
<script>
function setInvoicePaymentTableDataLabels() {
    var headers = Array.from(document.querySelectorAll('#invoicePaymentTable thead th')).map(th => th.innerText.trim());
    document.querySelectorAll('#invoicePaymentTable tbody tr').forEach(function(row) {
        row.querySelectorAll('td').forEach(function(td, i) {
            td.setAttribute('data-label', headers[i] || '');
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    setInvoicePaymentTableDataLabels();
});
</script>
@endpush