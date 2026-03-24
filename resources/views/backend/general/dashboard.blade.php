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
        <div style="padding-top: 30px;">
        @if(auth()->user() && auth()->user()->hasRole('Admin'))
            <!-- Filter Form -->
            <div class="container mb-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow border-0 bg-white p-3">
                            <div class="row align-items-center g-3">
                                <div class="col-md-3 text-md-start text-center">
                                    <h5 class="fw-bold text-primary mb-0"><i class="bi bi-calendar-range me-2"></i>Date Filter</h5>
                                </div>
                                <div class="col-md-9">
                                    <form method="GET" action="" class="row g-2 align-items-end justify-content-end mb-0">
                                        <div class="col-md-4 col-12 mb-2 mb-md-0">
                                            <label for="from_date" class="form-label mb-1 fw-semibold">From</label>
                                            <input type="date" class="form-control shadow-sm py-2 px-3 fs-6" id="from_date" name="from_date" value="{{ request('from_date', $from ?? now()->startOfMonth()->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-4 col-12 mb-2 mb-md-0">
                                            <label for="to_date" class="form-label mb-1 fw-semibold">To</label>
                                            <input type="date" class="form-control shadow-sm py-2 px-3 fs-6" id="to_date" name="to_date" value="{{ request('to_date', $to ?? now()->endOfMonth()->format('Y-m-d')) }}">
                                        </div>
                                         <div class="col-md-4 col-12 d-flex align-items-end gap-2">
                                           <button type="submit" class="btn btn-primary px-4 py-2 fs-6 shadow"><i class="bi bi-funnel"></i> Filter</button>
                                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary px-3 py-2 fs-6 shadow"><i class="bi bi-x-lg"></i> Reset</a>
                                        </div>                                        
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-white p-4 h-100 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#invoiceListModal">
                        <div class="icon mb-3 rounded-circle text-primary bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 28px;"><i class="bi bi-list"></i></div>
                        <h6 class="text-secondary fw-semibold mb-2">Invoices</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ $filteredMonthInvoiceCount }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-white p-4 h-100 d-flex flex-column align-items-center justify-content-center" style="border-radius: 8px;">
                        <div class="icon mb-3 rounded-circle text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 28px;"><i class="bi bi-cash-stack"></i></div>
                        <h6 class="text-secondary fw-semibold mb-2">Total Billed</h6>
                        <h2 class="fw-bold mb-0 text-dark">₹{{ number_format($filteredMonthTotal, 2) }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-white p-4 h-100 d-flex flex-column align-items-center justify-content-center" style="border-radius: 8px;">
                        <div class="icon mb-3 rounded-circle text-info bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 28px;"><i class="bi bi-tag"></i></div>
                        <h6 class="text-secondary fw-semibold mb-2">Payment Reconciled</h6>
                        <h2 class="fw-bold mb-0 text-dark">₹{{ number_format($filteredMonthReconciled, 2) }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-white p-4 h-100 d-flex flex-column align-items-center justify-content-center" style="border-radius: 8px;">
                        <div class="icon mb-3 rounded-circle text-warning bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 28px;"><i class="bi bi-people"></i></div>
                        <h6 class="text-secondary fw-semibold mb-2">Pending Reconciliation</h6>
                        <h2 class="fw-bold mb-0 text-dark">₹{{ number_format($filteredPendingReconciliation, 2) }}</h2>
                    </div>
                </div>
                
                    <div class="col-md-12 mt-4">
                        <div class="card p-3">
                            <h6 class="mb-3">Invoice Total (This Month)</h6>
                            <canvas id="invoiceBarChart" height="80"></canvas>
                            <div class="text-end mt-2">
                                <strong>Total: ₹{{ number_format($currentMonthTotal, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4">
                        <div class="card shadow-sm border-0 border-top border-success border-4 p-4 bg-white mb-2" style="border-radius: 8px;">
                            <h6 class="text-secondary fw-semibold mb-2">Reconciled Amount (This Month)</h6>
                            <h3 class="fw-bold mb-0 text-success">₹{{ number_format($reconciledTotal, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4">
                        <div class="card shadow-sm border-0 border-top border-warning border-4 p-4 bg-white mb-2 pending-reconciliation-trigger" style="cursor:pointer; border-radius: 8px;" id="pendingReconciliationCard">
                            <h6 class="text-secondary fw-semibold mb-2">Pending Reconciliation</h6>
                            <h3 class="fw-bold mb-0 text-warning">₹{{ number_format($pendingTotal, 2) }}</h3>
                        </div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('invoiceBarChart').getContext('2d');
                        const invoiceBarChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @json($chartLabels),
                                datasets: [{
                                    label: 'Invoice Amount',
                                    data: @json($chartData),
                                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                indexAxis: 'x', // vertical bars
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: { display: true, text: 'Amount (₹)' }
                                    },
                                    x: {
                                        title: { display: true, text: 'Day' }
                                    }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        });
                    </script>
                @endif
            </div>
        </div>


    </main>

    <!-- Pending Reconciliation Modal -->
<div class="modal fade" id="pendingReconciliationModal" tabindex="-1" aria-labelledby="pendingReconciliationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingReconciliationModalLabel">Pending Reconciliation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="pendingReconciliationModalBody">
        <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>
      </div>
    </div>
  </div>
</div>

<!-- Invoice List Modal -->
<div class="modal fade" id="invoiceListModal" tabindex="-1" aria-labelledby="invoiceListModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invoiceListModalLabel">Invoices (Filtered)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @php
          $modalInvoices = \App\Models\Invoice::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();
        @endphp
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Invoice No</th>
                <th>Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($modalInvoices as $i => $invoice)
                <tr>
                  <td>{{ $i+1 }}</td>
                  <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : ($invoice->created_at ? $invoice->created_at->format('d-m-Y') : '-') }}</td>
                  <td>{{ $invoice->customer_name ?? ($invoice->customer->name ?? '-') }}</td>
                  <td>{{ $invoice->invoice_number }}</td>
                  <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                  <td><a href="{{ url('invoice-view?invoice_id=' . $invoice->id) }}" target="_blank" class="btn btn-sm btn-primary">View</a></td>
                </tr>
              @endforeach
              @if($modalInvoices->isEmpty())
                <tr><td colspan="6" class="text-center">No invoices found for this period.</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>



    <style>
@media (max-width: 767.98px) {
  /* Make modal full width and height on mobile */
  .modal-xl {
    max-width: 100vw !important;
    margin: 0;
  }
  .modal-content {
    border-radius: 0.7rem;
    padding: 0.5rem 0.2rem;
    min-height: 90vh;
    max-height: 98vh;
    overflow-y: auto;
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
  .table-responsive {
    font-size: 0.95rem;
  }
  table.table {
    min-width: 700px;
  }
  /* Make modal close button easier to tap */
  .modal-header .btn-close {
    font-size: 1.5rem;
    padding: 0.5rem;
  }
}
</style>

    <script>
        $(document).ready(function() {
            $('#brandFilter').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter1').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter2').select2({
                placeholder: "Select a brand",
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#brandFilter3').select2({
                placeholder: "Select Category",
                allowClear: true
            });
        });

        // Pending Reconciliation Modal AJAX
    $('#pendingReconciliationCard').on('click', function() {
        $('#pendingReconciliationModal').modal('show');
        $('#pendingReconciliationModalBody').html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $.ajax({
            url: '/payment/pending-reconciliation',
            type: 'GET',
            cache: false, // Prevent Firefox caching
            data: { _ts: Date.now() }, // Add timestamp to fully prevent cache
            success: function(data) {
                $('#pendingReconciliationModalBody').html(data);
            },
            error: function() {
                $('#pendingReconciliationModalBody').html('<div class="alert alert-danger">Failed to load pending reconciliation data.</div>');
            }
        });
    });

    // Optional: handle confirm button feedback (delegated)
    $(document).on('click', '.confirm-payment-btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        btn.prop('disabled', true).text('Confirming...');
        $.post(url, {_token: '{{ csrf_token() }}'}, function(resp) {
            if(resp.success) {
                btn.closest('tr').addClass('table-success');
                btn.replaceWith('<span class="badge bg-success">Confirmed</span>');
            } else {
                btn.prop('disabled', false).text('Confirm');
                alert('Failed to confirm payment.');
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Confirm');
            alert('Failed to confirm payment.');
        });
    });
    </script>

@endsection

