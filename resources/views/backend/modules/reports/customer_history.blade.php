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
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Find Customer Purchase History</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.customer_history') }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-10">
                                <input type="text" name="q" class="form-control" placeholder="Search by Customer Name, Mobile Number, or Company..." value="{{ request('q') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </form>

                    @if(request('q'))
                        @forelse($customers as $customer)
                            <div class="customer-result mb-5 border rounded-4 p-4 shadow-sm bg-light">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h4>{{ $customer->name }}</h4>
                                        <p class="text-muted mb-0"><i class="fas fa-phone me-2"></i>{{ $customer->mobile_no }}</p>
                                        <p class="text-muted"><i class="fas fa-envelope me-2"></i>{{ $customer->email ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="badge bg-primary fs-6">{{ count($customer->invoices) }} Invoices Found</div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover bg-white rounded-3">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Invoice Date</th>
                                                <th>Invoice No</th>
                                                <th>Total Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customer->invoices as $invoice)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</td>
                                                    <td>{{ $invoice->invoice_number }}</td>
                                                    <td class="fw-bold text-success">₹{{ number_format($invoice->grand_total, 2) }}</td>
                                                    <td>
                                                        <span class="badge {{ $invoice->status == 'Approved' ? 'bg-success' : 'bg-warning' }}">
                                                            {{ $invoice->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ url('/invoice-view?invoice_id=' . $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No invoices found for this customer.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">No customers found matching your search.</div>
                        @endforelse
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>Enter a name or mobile number to look up purchase history.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
