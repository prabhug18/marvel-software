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
                    <h5 class="mb-0 text-center"><i class="fas fa-history me-2 text-primary"></i>Find Customer Purchase History</h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('reports.customer_history') }}" class="mb-5 position-relative">
                        <div class="row g-3 justify-content-center">
                            <div class="col-md-9 position-relative">
                                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" id="customerSearchInput" class="form-control border-0 ps-2" 
                                           placeholder="Type Name, Mobile, or Company to see suggestions..." 
                                           value="{{ request('search') }}" 
                                           autocomplete="off" 
                                           required>
                                    <button type="submit" class="btn btn-primary px-5 fw-bold">Retrieve History</button>
                                </div>
                                <div id="suggestionBox" class="suggestion-list-container shadow-lg"></div>
                                <div class="text-center mt-2 small text-muted">Suggestions: <span class="fw-bold">Name - Mobile - Company</span></div>
                            </div>
                        </div>
                    </form>

                    <!-- Rest of the history results remains the same -->
                    @if(request('search'))
                        @forelse($customerRecords as $customer)
                            <!-- Result card -->
                            <div class="customer-result mb-5 border rounded-4 p-4 shadow-sm bg-light">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-6 border-start border-primary border-4 ps-4">
                                        <h3 class="mb-1 fw-bold text-dark">{{ $customer->name }}</h3>
                                        <p class="text-muted mb-0 font-monospace"><i class="fas fa-phone me-2 text-primary"></i>{{ $customer->mobile_no }}</p>
                                        <p class="text-muted mb-0"><i class="fas fa-envelope me-2 text-primary"></i>{{ $customer->email ?? 'N/A' }}</p>
                                        @if($customer->gst_no)
                                            <p class="text-muted mb-0"><i class="fas fa-building me-2 text-primary"></i>GST: {{ $customer->gst_no }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="bg-white p-3 d-inline-block rounded-4 shadow-sm border">
                                            <h4 class="mb-0 fw-bold text-primary">{{ count($customer->invoices) }}</h4>
                                            <div class="text-uppercase small fw-bold text-muted">Total Invoices</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover bg-white rounded-3 shadow-sm border overflow-hidden">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="py-3 px-4">Invoice Date</th>
                                                <th class="py-3 px-4">Invoice No</th>
                                                <th class="py-3 px-4 text-end">Total Amount</th>
                                                <th class="py-3 px-4 text-center">Status</th>
                                                <th class="py-3 px-4 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customer->invoices as $invoice)
                                                <tr>
                                                    <td class="px-4">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') }}</td>
                                                    <td class="px-4 fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                                                    <td class="px-4 fw-bold text-success text-end">₹{{ number_format($invoice->grand_total, 2) }}</td>
                                                    <td class="px-4 text-center">
                                                        <span class="badge {{ $invoice->status == 'Approved' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 shadow-sm">
                                                            {{ $invoice->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 text-center">
                                                        <a href="{{ url('/invoice-view?invoice_id=' . $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                                            <i class="fas fa-eye me-1"></i>View Details
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">No invoices found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <h5 class="text-muted">No results found for "{{ request('search') }}"</h5>
                            </div>
                        @endforelse
                    @else
                        <div class="text-center py-5 text-muted opacity-50">
                            <i class="fas fa-search fa-5x mb-3 text-light"></i>
                            <h5 class="fw-normal">Search to view detailed purchase history...</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <style>
        .suggestion-list-container {
            position: absolute;
            top: 52px; /* Adjusted for input group height */
            left: 12px; /* Match Bootstrap row padding */
            right: 12px; /* Match Bootstrap row padding */
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 0 0 15px 15px;
            z-index: 1050; /* Above regular elements */
            max-height: 280px;
            overflow-y: auto;
            display: none;
            border-top: none;
        }
        .suggestion-item {
            padding: 12px 25px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.15s ease-in-out;
            color: #444;
            font-size: 0.92rem;
            text-align: left;
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .suggestion-item:hover {
            background-color: #f8faff;
            color: #0d6efd;
            padding-left: 32px;
        }
    </style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const customers = @json($allCustomers);
        const searchInput = $('#customerSearchInput');
        const suggestionBox = $('#suggestionBox');

        searchInput.on('input', function() {
            const query = $(this).val().toLowerCase();
            suggestionBox.empty().hide();

            if (query.length < 2) return;

            const filtered = customers.filter(c => 
                (c.name && c.name.toLowerCase().includes(query)) || 
                (c.mobile_no && c.mobile_no.toLowerCase().includes(query)) ||
                (c.gst_no && c.gst_no.toLowerCase().includes(query))
            ).slice(0, 10);

            if (filtered.length > 0) {
                filtered.forEach(c => {
                    const company = c.gst_no ? ` - ${c.gst_no}` : '';
                    const display = `${c.name} - ${c.mobile_no}${company}`;
                    suggestionBox.append(`<div class="suggestion-item" data-value="${c.name}">${display}</div>`);
                });
                suggestionBox.show();
            }
        });

        $(document).on('click', '.suggestion-item', function() {
            searchInput.val($(this).data('value'));
            suggestionBox.hide();
            searchInput.closest('form').submit();
        });

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.input-group, #suggestionBox').length) {
                suggestionBox.hide();
            }
        });
    });
</script>
@endpush
