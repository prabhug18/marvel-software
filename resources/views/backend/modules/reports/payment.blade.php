@extends('layouts.backend')

@section('content')
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')
        
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            
            <!-- Summary Cards (Lifetime Totals) -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 bg-primary text-white h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase small font-weight-bold opacity-75">Total Collection</h6>
                            <h2 class="mb-0 fw-bold">₹{{ number_format($summary['total_collection'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 bg-success text-white h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase small font-weight-bold opacity-75">Confirmed Collection</h6>
                            <h2 class="mb-0 fw-bold">₹{{ number_format($summary['confirmed_collection'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 bg-warning text-white h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h6 class="text-uppercase small font-weight-bold opacity-75">Pending Collection</h6>
                            <h2 class="mb-0 fw-bold">₹{{ number_format($summary['pending_collection'], 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('reports.payment') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Date Range</label>
                                    <select name="range" id="dateRange" class="form-select border-primary shadow-sm">
                                        <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today's Collection</option>
                                        <option value="7days" {{ request('range') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                                        <option value="30days" {{ request('range') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                                        <option value="90days" {{ request('range') == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2 custom-date" style="display: {{ request('range') == 'custom' ? 'block' : 'none' }}">
                                    <label class="form-label fw-bold">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2 custom-date" style="display: {{ request('range') == 'custom' ? 'block' : 'none' }}">
                                    <label class="form-label fw-bold">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Payment Mode</label>
                                    <select name="payment_mode" class="form-select border-primary shadow-sm">
                                        <option value="">All Modes</option>
                                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                        <option value="Online" {{ request('payment_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                                        <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                        <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <a href="{{ route('reports.payment') }}" class="btn btn-outline-secondary w-100 shadow-sm"><i class="fas fa-undo"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center shadow-sm rounded-3 overflow-hidden">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                        <td>{{ $payment->invoice_number }}</td>
                                        <td>{{ $payment->customer->name ?? $payment->customer_name }}</td>
                                        <td class="fw-bold text-success">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $payment->payment_mode }}</span></td>
                                        <td>
                                            @if($payment->is_confirmed)
                                                <span class="badge bg-success rounded-pill px-3">Confirmed</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-3">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ url('payment/payment-reconciliation?invoice_id=' . $payment->invoice_id) }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm">
                                                <i class="fas fa-check-double"></i> Reconcile
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-5 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> No payments recorded for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $payments->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle Custom Date Inputs
        $('#dateRange').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.custom-date').fadeIn();
            } else {
                $('.custom-date').fadeOut();
            }
        });
    });
</script>
@endpush
