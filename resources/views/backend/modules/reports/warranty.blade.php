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
            <div class="card shadow-sm rounded-4 max-width-800 mx-auto">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-center"><i class="fas fa-shield-halved me-2 text-primary"></i>Product Warranty Verification</h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('reports.warranty_check') }}" class="mb-5">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" name="serial_no" class="form-control border-0 ps-0" placeholder="Enter Serial Number / Tracking ID..." value="{{ request('serial_no') }}" required>
                            <button type="submit" class="btn btn-primary px-4">Verify Status</button>
                        </div>
                    </form>

                    @if(request('serial_no'))
                        @if($item)
                            <div class="warranty-result text-center">
                                <div class="mb-4">
                                    @if($warrantyStatus == 'Active')
                                        <div class="display-1 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                                        <h2 class="text-success fw-bold">WARRANTY ACTIVE</h2>
                                    @else
                                        <div class="display-1 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                                        <h2 class="text-danger fw-bold">WARRANTY EXPIRED</h2>
                                    @endif
                                </div>

                                <div class="row text-start g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <label class="text-muted small text-uppercase fw-bold d-block">Customer Details</label>
                                            <p class="mb-0 fs-5 fw-bold">{{ $item->invoice->customer->name ?? $item->invoice->customer_name }}</p>
                                            <p class="text-muted mb-0 small">{{ $item->invoice->customer->mobile_no ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <label class="text-muted small text-uppercase fw-bold d-block">Invoice Info</label>
                                            <p class="mb-0 fs-5 fw-bold">#{{ $item->invoice->invoice_number }}</p>
                                            <p class="text-muted mb-0 small">Sold on: {{ \Carbon\Carbon::parse($item->invoice->invoice_date)->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <label class="text-muted small text-uppercase fw-bold d-block">Product Detail</label>
                                            <p class="mb-0 fs-5 fw-bold">{{ $item->product_name }}</p>
                                            <p class="text-muted mb-0 small">Model: {{ $item->model }} | Serial: <span class="text-dark fw-bold">{{ $item->serial_no }}</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="p-4 border rounded-3 {{ $warrantyStatus == 'Active' ? 'bg-success-subtle border-success' : 'bg-danger-subtle border-danger' }} text-center">
                                            <label class="text-muted small text-uppercase fw-bold d-block">Warranty Expiry Date</label>
                                            <p class="mb-0 display-6 fw-bold {{ $warrantyStatus == 'Active' ? 'text-success' : 'text-danger' }}">
                                                {{ $item->warranty_expiry->format('d M, Y') }}
                                            </p>
                                            <p class="small mt-1 mb-0">{{ (int)$item->product->foc_months }} Months Coverage from date of sale</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ url('/invoice-view?invoice_id=' . $item->invoice_id) }}" target="_blank" class="btn btn-outline-dark">
                                        <i class="fas fa-file-invoice me-2"></i>View Original Invoice
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="display-3 text-muted mb-3"><i class="fas fa-search-minus"></i></div>
                                <h5>Serial Number Not Found</h5>
                                <p class="text-muted">No sales record found for the serial number: <strong>{{ request('serial_no') }}</strong></p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-laptop-medical fa-4x mb-3 text-light"></i>
                            <p>Enter the product serial number to verify its warranty eligibility.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <style>
        .max-width-800 { max-width: 800px; }
        .bg-success-subtle { background-color: #d1e7dd; }
        .bg-danger-subtle { background-color: #f8d7da; }
    </style>
@endsection
