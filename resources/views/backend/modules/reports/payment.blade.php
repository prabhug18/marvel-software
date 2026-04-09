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
            
            <!-- Summary Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 bg-success text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold">Total Payments Received</h6>
                            <h3 class="mb-0">₹{{ number_format($totalReceived, 2) }}</h3>
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
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payment Mode</label>
                                    <select name="payment_mode" class="form-select">
                                        <option value="">All Modes</option>
                                        <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                        <option value="Online" {{ request('payment_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                                        <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 me-2">Filter</button>
                                    <a href="{{ route('reports.payment') }}" class="btn btn-secondary w-100">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle text-center">
                            <thead class="custom-thead">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                        <td>{{ $payment->invoice_number }}</td>
                                        <td>{{ $payment->customer->name ?? $payment->customer_name }}</td>
                                        <td class="fw-bold">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $payment->payment_mode }}</span></td>
                                        <td>
                                            @if($payment->is_confirmed)
                                                <span class="badge bg-success">Confirmed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">No payments found.</td>
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
