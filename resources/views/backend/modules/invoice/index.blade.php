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
                <div class="row mb-3 align-items-end justify-content-between flex-column flex-md-row">
                    <div class="col-auto mb-2 mb-md-0">
                        <a class="btn custom-orange-btn text-white" href="{{ url('/invoice/create') }}">
                            <i class="fas fa-user-plus me-2"></i>Add Invoice
                        </a>
                    </div>
                    <div class="col-auto">
                        <form class="d-flex flex-column flex-md-row align-items-end" method="GET" action="{{ route('invoice.export') }}" target="_blank" onsubmit="return validateExportDates();">
                            <div class="me-2 mb-2 mb-md-0">
                                <label for="from_date" class="form-label mb-0">From:</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
                            </div>
                            <div class="me-2 mb-2 mb-md-0">
                                <label for="to_date" class="form-label mb-0">To:</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
                            </div>
                            <button type="submit" class="btn btn-success">Export Invoices</button>
                        </form>
                    </div>
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
                            <th scope="col">AMOUNT</th>
                            <th scope="col">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody">
                            @include('backend.modules.invoice.partials.invoice_rows', ['invoices' => $invoices])
                        </tbody>
                    </table>
                </div>
                <style>
                @media (max-width: 767.98px) {
                    #invoiceTable, #invoiceTable thead, #invoiceTable tbody, #invoiceTable th, #invoiceTable tr {
                        display: block;
                        width: 100%;
                    }
                    #invoiceTable thead {
                        display: none;
                    }
                    #invoiceTable tr {
                        margin-bottom: 1.2rem;
                        border: 1px solid #dee2e6;
                        border-radius: 0.5rem;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
                        background: #fff;
                        padding: 0.5rem;
                    }
                    #invoiceTable td {
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
                    #invoiceTable td .mobile-value {
                        margin-left: 100px;
                        display: inline-block;
                    }
                    #invoiceTable td:before {
                        content: attr(data-label);
                        display: block;
                        font-weight: bold;
                        color: #f47820;
                        margin-bottom: 0.2rem;
                        font-size: 0.98em;
                        white-space: pre-line;
                        word-break: break-word;
                    }
                    #invoiceTable td:last-child {
                        border-bottom: none;
                    }
                }
                @media (min-width: 768px) {
                    #invoiceTable td .mobile-value {
                        margin-left: 0;
                        display: inline;
                    }
                }
                </style>
                <script>
                function setInvoiceTableDataLabels() {
                    var headers = Array.from(document.querySelectorAll('#invoiceTable thead th')).map(th => th.innerText.trim());
                    document.querySelectorAll('#invoiceTable tbody tr').forEach(function(row) {
                        row.querySelectorAll('td').forEach(function(td, i) {
                            td.setAttribute('data-label', headers[i] || '');
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    setInvoiceTableDataLabels();
                });
                </script>

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
                setInvoiceTableDataLabels(); // <-- Add this line to update data-labels for new rows
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

function validateExportDates() {
    var from = document.getElementById('from_date').value;
    var to = document.getElementById('to_date').value;
    if (!from || !to) {
        alert('Please select both From and To dates to export invoices.');
        return false;
    }
    return true;
}
</script>
@endpush

<style>
@media (max-width: 767.98px) {
    .table-responsive {
        font-size: 0.95rem;
    }
    #invoiceTable th, #invoiceTable td {
        white-space: nowrap;
        padding: 0.4rem 0.3rem;
    }
    .custom-thead th {
        font-size: 0.95rem;
    }
    .btn, .form-control {
        font-size: 1rem;
    }
    .card-body, .container-fluid {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}
</style>

@endsection