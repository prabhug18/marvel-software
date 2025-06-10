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

                <!-- Date Filter and Export Button -->
                <div class="row mb-3 align-items-end justify-content-between">
                    <div class="col-auto">
                        <a class="btn custom-orange-btn text-white" href="{{ url('/invoice/create') }}">
                            <i class="fas fa-user-plus me-2"></i>Add Invoice
                        </a>
                    </div>
                    <form class="col-auto d-flex align-items-end" method="GET" action="{{ route('invoice.export') }}" target="_blank">
                        <div class="me-2">
                            <label for="from_date" class="form-label mb-0">From:</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
                        </div>
                        <div class="me-2">
                            <label for="to_date" class="form-label mb-0">To:</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
                        </div>
                        <button type="submit" class="btn btn-success">Export Invoices</button>
                    </form>
                </div>

                <!-- Responsive Table -->
                <div class="table-responsive" id="responsive-table">
                    <table id="invoiceTable" class="table table-striped table-bordered align-middle text-center">
                        <thead class="custom-thead text-center align-middle">
                            <tr>
                            <th scope="col">S.NO</th>
                            <th scope="col">DATE</th>
                            <th scope="col">CUSTOMER</th>
                            <th scope="col">INVOICE NO</th>
                            <th scope="col">DESCRIPTION</th>
                            <th scope="col">AMOUNT</th>
                            <th scope="col">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody">
                            @include('backend.modules.invoice.partials.invoice_rows', ['invoices' => $invoices])
                        </tbody>
                    </table>
                </div>

                </div>
            </div>
        </div>
        
    </main>
@push('scripts')
<script>
let page = 1;
let loading = false;
let lastPage = {{ $invoices->hasMorePages() ? 'false' : 'true' }};

function loadMoreInvoices() {
    if (loading || lastPage) return;
    loading = true;
    page++;
    $.ajax({
        url: '?page=' + page,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }, // Ensure Laravel detects AJAX
        success: function(data) {
            if (data.trim() === '') {
                lastPage = true;
                if (!$('#endOfInvoices').length) {
                    $('#invoiceTableBody').append('<tr id="endOfInvoices"><td colspan="7" class="text-center text-muted">End of invoices</td></tr>');
                }
            } else {
                $('#invoiceTableBody').append(data);
            }
            loading = false;
        },
        error: function() {
            loading = false;
        }
    });
}

$(window).on('scroll', function() {
    console.log('Scroll event fired');
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
        loadMoreInvoices();
    }
});
</script>
@endpush

@endsection