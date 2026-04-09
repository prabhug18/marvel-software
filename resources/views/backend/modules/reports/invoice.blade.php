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
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm rounded-4 border-0 bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold">Total Invoiced</h6>
                            <h3 class="mb-0">₹{{ number_format($summary['total_invoiced'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm rounded-4 border-0 bg-success text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold">Total CGST</h6>
                            <h3 class="mb-0">₹{{ number_format($summary['total_cgst'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm rounded-4 border-0 bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold">Total SGST</h6>
                            <h3 class="mb-0">₹{{ number_format($summary['total_sgst'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm rounded-4 border-0 bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold">Total IGST</h6>
                            <h3 class="mb-0">₹{{ number_format($summary['total_igst'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('reports.invoice') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <select name="range" id="dateRange" class="form-select">
                                        <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="7days" {{ request('range') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                                        <option value="30days" {{ request('range') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                                        <option value="90days" {{ request('range') == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2 custom-date" style="display: {{ request('range') == 'custom' ? 'block' : 'none' }}">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2 custom-date" style="display: {{ request('range') == 'custom' ? 'block' : 'none' }}">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Customer</label>
                                    <select name="customer_id" id="customerSelect" class="form-select">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->mobile_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 me-2 mt-4">Filter</button>
                                    <a href="{{ route('reports.invoice', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success w-100 me-2 mt-4">Export</a>
                                    <a href="{{ route('reports.invoice') }}" class="btn btn-secondary w-100 mt-4">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle text-center">
                            <thead class="custom-thead">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Warehouse</th>
                                    <th>CGST</th>
                                    <th>SGST</th>
                                    <th>IGST</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->customer->name ?? $invoice->customer_name }}</td>
                                        <td>{{ $invoice->warehouse->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($invoice->cgst, 2) }}</td>
                                        <td>{{ number_format($invoice->sgst, 2) }}</td>
                                        <td>{{ number_format($invoice->igst, 2) }}</td>
                                        <td class="fw-bold">₹{{ number_format($invoice->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $invoices->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for Customer Search
        $('#customerSelect').select2({
            placeholder: "Search Customer...",
            allowClear: true,
            width: '100%'
        });

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
