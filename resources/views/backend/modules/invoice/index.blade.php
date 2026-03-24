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

                <!-- Search Bar, Date Filter and Export Button -->
                <div class="row mb-3 align-items-end justify-content-between flex-column flex-md-row">
                    <div class="col-auto mb-2 mb-md-0">
                        <a class="btn custom-orange-btn text-white" href="{{ url('/invoice/create') }}">
                            <i class="fas fa-user-plus me-2"></i>Add Invoice
                        </a>
                    </div>
                    <div class="col-md-6 mb-2 mb-md-0 position-relative d-flex align-items-center" style="min-width:340px;max-width:420px;">
                        <label for="invoiceSearch" class="form-label mb-0 me-2" style="white-space:nowrap;min-width:90px;">Search Here:</label>
                        <input id="invoiceSearch" class="form-control form-control-sm" style="min-width:240px;max-width:320px;" type="text" placeholder="Search by name, email, mobile, or invoice..." autocomplete="off">
                        <button id="viewDetailsBtn" class="btn btn-primary btn-sm ms-2" style="display:none;white-space:nowrap;">View Details</button>
                        <div id="searchSuggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none; top:100%;left:0;"></div>
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
                <!-- Responsive table CSS moved to styles.css -->
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
let selectedSearchId = null;

function loadMoreInvoices() {
    if (loading || lastPage) return;
    loading = true;
    page++;
    $.ajax({
        url: '?page=' + page,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(data) {
            if (data.trim() === '') {
                lastPage = true;
                if (!$('#endOfInvoices').length) {
                    $('#invoiceTableBody').append('<tr id="endOfInvoices"><td colspan="7" class="text-center text-muted">End of invoices</td></tr>');
                }
            } else {
                $('#invoiceTableBody').append(data);
                setInvoiceTableDataLabels();
            }
            loading = false;
        },
        error: function() {
            loading = false;
        }
    });
}

$(window).on('scroll', function() {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
        loadMoreInvoices();
    }
});

function validateExportDates() {
    var from = document.getElementById('from_date').value;
    var to = document.getElementById('to_date').value;
    if (!from || !to) {
        Swal.fire({ icon: 'warning', title: 'Select Dates', text: 'Please select both From and To dates to export invoices.' });
        return false;
    }
    return true;
}

// --- Search & Modal Logic ---
$('#invoiceSearch').on('input', function() {
    let query = $(this).val();
    if (query.length < 2) {
        $('#searchSuggestions').hide();
        $('#viewDetailsBtn').hide();
        selectedSearchId = null;
        return;
    }
    $.ajax({
        url: '/invoice/search',
        type: 'GET',
        data: {q: query},
        success: function(data) {
            let suggestions = '';
            let shownCustomerIds = {};
            if (data.length > 0) {
                data.forEach(function(item) {
                    if(item.type === 'customer') {
                        if(shownCustomerIds[item.id]) return; // skip duplicate
                        shownCustomerIds[item.id] = true;
                    }
                    suggestions += `<a href="#" class="list-group-item list-group-item-action search-suggestion" data-id="${item.id}" data-type="${item.type}">
                        <div><strong>${item.display}</strong></div>
                        <div class="small text-muted">${item.subtext}</div>
                    </a>`;
                });
                $('#searchSuggestions').html(suggestions).show();
            } else {
                $('#searchSuggestions').html('<div class="list-group-item">No results found</div>').show();
            }
        }
    });
});

$(document).on('click', '.search-suggestion', function(e) {
    e.preventDefault();
    selectedSearchId = $(this).data('id');
    $('#invoiceSearch').val($(this).find('strong').text());
    $('#searchSuggestions').hide();
    $('#viewDetailsBtn').show();
});

$('#viewDetailsBtn').on('click', function() {
    if (!selectedSearchId) return;
    $.ajax({
        url: '/invoice/details', // You must implement this route in your controller
        type: 'GET',
        data: {id: selectedSearchId},
        success: function(data) {
            // Fill modal with returned HTML
            $('#detailsModal .modal-content').html(data);
            $('#detailsModal').modal('show');
        }
    });
});

// Hide suggestions when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#invoiceSearch, #searchSuggestions').length) {
        $('#searchSuggestions').hide();
    }
});
</script>
<!-- Modal for Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1.2rem; box-shadow: 0 8px 32px rgba(44,62,80,0.18);">
      <!-- Content will be loaded via AJAX -->
      <div class="modal-body text-center p-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- All search and modal CSS moved to styles.css -->

<!-- Approval Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="approveModalBody">Approve this invoice? Current status: <strong id="approveStatus"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmApproveBtn" class="btn btn-primary">Approve</button>
            </div>
        </div>
    </div>
</div>

<script>
// Approval handling
let invoiceToApprove = null;
// Initialize Bootstrap Modal instance (use native API when available)
const approveModalEl = document.getElementById('approveModal');
let approveModalInstance = null;
try {
    if (approveModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Disable Bootstrap's automatic focus to avoid focusing elements while aria-hidden is true.
        approveModalInstance = new bootstrap.Modal(approveModalEl, { focus: false });
    }
} catch (e) {
    approveModalInstance = null;
}

$(document).on('click', '.invoice-approve-btn', function(e) {
    e.preventDefault();
    invoiceToApprove = $(this).data('id');
    $('#approveStatus').text('Pending');
    // Show modal using native API if available, otherwise fallback to jQuery plugin
    if (approveModalInstance) {
        approveModalInstance.show();
    } else {
        // Try to initialize jQuery modal without auto-focus where supported
        try {
            $('#approveModal').modal({ focus: false });
            $('#approveModal').modal('show');
        } catch (err) {
            // Fallback: just show the modal
            $('#approveModal').modal('show');
        }
    }
});

// Only focus confirm button after modal is fully shown to avoid aria-hidden focus issues
if (approveModalEl) {
    approveModalEl.addEventListener('shown.bs.modal', function() {
        const btn = document.getElementById('confirmApproveBtn');
        if (btn) btn.focus();
    });
}

$('#confirmApproveBtn').on('click', function() {
        if (!invoiceToApprove) return;
        const token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
                url: '/invoice/' + invoiceToApprove + '/approve',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                success: function(res) {
                if (res.success) {
                // Update row: replace Approve button with a green check icon, show View link and update status text
                const btn = $('.invoice-approve-btn[data-id="' + invoiceToApprove + '"]');
                const container = btn.closest('td');
                // Remove the approve control so it is hidden after approval
                container.find('.invoice-approve-btn').remove();
                // Update status small if present, otherwise append one
                const statusEl = container.find('.invoice-status-text');
                if (statusEl.length) {
                    statusEl.text('Status: Approved').removeClass('text-warning').addClass('text-success');
                } else {
                    container.append('<small class="d-block text-success mt-1 invoice-status-text">Status: Approved</small>');
                }
                // Add View link so user can immediately open the invoice (matches blade's href format)
                if (!container.find('a[title="View"]').length) {
                    // Prefer the server-rendered view URL stored on the row; fallback to legacy query URL
                    const row = btn.closest('tr');
                    const viewHref = (row && row.data && row.data('invoice-view-url')) ? row.data('invoice-view-url') : ('/invoice-view?invoice_id=' + invoiceToApprove);
                    // prepend view icon so it's visible near other actions
                    container.prepend('<a href="' + viewHref + '" target="_blank" rel="noopener" title="View"><i class="fas fa-eye text-primary mx-1"></i></a>');
                }
                // Hide modal using native instance if available
                if (approveModalInstance && typeof approveModalInstance.hide === 'function') {
                    approveModalInstance.hide();
                } else {
                    $('#approveModal').modal('hide');
                }
                // Reload the page so server-rendered links/status are authoritative
                setTimeout(function() { location.reload(); }, 400);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error: ' + (res.message || 'Could not approve') });
            }
                },
                error: function(xhr) {
                        let msg = 'Error approving invoice.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
        });
});
</script>

@endpush

<!-- Responsive table and utility CSS moved to styles.css -->

@endsection