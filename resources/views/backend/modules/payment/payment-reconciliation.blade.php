@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <a href="{{ url('invoice') }}" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Invoices</a>
                    <h4 class="mb-3">{{ $heading ?? 'Payment Reconciliation' }} for Invoice</h4>
                    @php
                        $invoiceId = request('invoice_id');
                        $invoice = \App\Models\Invoice::with('customer')->find($invoiceId);
                        $payments = $invoice ? \App\Models\Payment::where('invoice_id', $invoiceId)->orderBy('payment_date')->get() : collect();
                    @endphp
                    @if(!$invoice)
                        <div class="alert alert-danger">Invoice not found.</div>
                    @else
                        <div class="mb-2">
                            <strong>Customer:</strong> {{ $invoice->customer->name ?? '-' }}<br>
                            <strong>Invoice Number:</strong> {!! (string) $invoice->getOriginal('invoice_number') !!}<br>
                            <strong>Grand Total:</strong> ₹{{ number_format($invoice->grand_total, 2) }}
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Payment Date</th>
                                        <th>Paid Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Description</th>
                                        <th>Recorded By</th>
                                        <th>Confirmed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $i => $payment)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                            <td>₹{{ number_format($payment->paid_amount, 2) }}</td>
                                            <td>{{ $payment->payment_mode }}</td>
                                            <td>{{ $payment->description }}</td>
                                            <td>{{ $payment->user->name ?? 'User#'.$payment->user_id }}</td>
                                            <td>
                                                @if($payment->is_confirmed)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$payment->is_confirmed)
                                                <form method="POST" action="{{ url('payment/confirm') }}" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">Confirm</button>
                                                </form>
                                                @else
                                                <button class="btn btn-sm btn-success" disabled>Confirmed</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center">No payments found for this invoice.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <strong>Total Paid:</strong> ₹{{ number_format($payments->sum('paid_amount'), 2) }}<br>
                            <strong>Balance:</strong> ₹{{ number_format($invoice->grand_total - $payments->sum('paid_amount'), 2) }}
                        </div>
                        <form method="POST" action="{{ url('payment/mark-reconciliation') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="invoice_id" value="{{ $invoiceId ?? request('invoice_id') }}">
                            <button type="submit" class="btn btn-success" @if($payments->count() == 0 || $payments->where('is_confirmed', false)->count() > 0) disabled @endif>
                                <i class="fas fa-check-circle me-1"></i> Mark Reconciliation as Done
                            </button>
                        </form>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('redirect_invoice'))
                        <script>
                            setTimeout(function() {
                                window.location.href = "{{ url('invoice') }}";
                            }, 2000);
                        </script>
                    @endif
                </div>
            </div>
        </div>
        
    </main>

    <style>
    @media (max-width: 767.98px) {
      .table-responsive {
        font-size: 0.95rem;
      }
      table.table, table.table thead, table.table tbody, table.table th, table.table tr {
        display: block;
        width: 100%;
      }
      table.table thead {
        display: none;
      }
      table.table tr {
        margin-bottom: 1.2rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        background: #fff;
        padding: 0.5rem;
      }
      table.table td {
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
      table.table td:before {
        content: attr(data-label);
        display: block;
        font-weight: bold;
        color: #f47820;
        margin-bottom: 0.2rem;
        font-size: 0.98em;
        white-space: pre-line;
        word-break: break-word;
      }
      table.table td:last-child {
        border-bottom: none;
      }
    }
    </style>
    <script>
    function setPaymentTableDataLabels() {
        var headers = Array.from(document.querySelectorAll('table.table thead th')).map(th => th.innerText.trim());
        document.querySelectorAll('table.table tbody tr').forEach(function(row) {
            row.querySelectorAll('td').forEach(function(td, i) {
                td.setAttribute('data-label', headers[i] || '');
            });
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        setPaymentTableDataLabels();
    });
    </script>
@endsection