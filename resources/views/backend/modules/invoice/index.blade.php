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

                <!-- Unified Action & Filter Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: #ffffff;">
                    <div class="card-body p-3">
                        <form id="invoiceFilterForm" onsubmit="event.preventDefault(); applyCustomDateFilter();">
                            <input type="hidden" name="date_range" id="dateRangeInput" value="{{ $preset ?? '3_months' }}">
                            <input type="hidden" name="status_filter" id="statusFilterInput" value="{{ $statusFilter ?? 'all' }}">
                            
                            <!-- Row 1: Add Invoice, Quick Search & Export -->
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 border-bottom">
                                <div>
                                    <a class="btn custom-orange-btn text-white rounded-pill px-4 shadow-sm" href="{{ url('/invoice/create') }}">
                                        <i class="fas fa-plus me-2"></i>Add Invoice
                                    </a>
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="position-relative" style="min-width: 280px; max-width: 380px;">
                                        <input id="invoiceSearch" class="form-control form-control-sm rounded-pill ps-3 pe-4 border" type="text" placeholder="Quick Search..." autocomplete="off">
                                        <button id="viewDetailsBtn" class="btn btn-primary btn-sm ms-1 rounded-pill position-absolute end-0 top-0 h-100 px-3" style="display:none;">Details</button>
                                        <div id="searchSuggestions" class="list-group position-absolute w-100 shadow" style="z-index: 1000; display: none; top:100%;left:0;"></div>
                                    </div>
                                    
                                    <button type="button" onclick="exportFilteredInvoices();" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-file-excel me-1"></i>Export Invoices
                                    </button>
                                </div>
                            </div>

                            <!-- Row 2: Range Presets, Custom Date & Status Filter -->
                            <div class="d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between gap-3 pt-3">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <!-- Date Presets Segmented Group -->
                                    <div class="preset-segmented-control p-1 bg-light rounded-pill border d-inline-flex flex-wrap align-items-center gap-1">
                                        <span class="px-2 text-muted fw-bold small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-calendar-alt me-1 text-warning"></i>Range:
                                        </span>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '10_days' ? 'active' : '' }}" data-preset="10_days">10 Days</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '20_days' ? 'active' : '' }}" data-preset="20_days">20 Days</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '1_month' ? 'active' : '' }}" data-preset="1_month">1 Month</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '3_months' ? 'active' : '' }}" data-preset="3_months">3 Months</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '6_months' ? 'active' : '' }}" data-preset="6_months">6 Months</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == '9_months' ? 'active' : '' }}" data-preset="9_months">9 Months</button>
                                        <button type="button" class="btn btn-xs preset-pill date-preset-btn {{ ($preset ?? '3_months') == 'custom' ? 'active' : '' }}" data-preset="custom">Custom</button>
                                    </div>

                                    <!-- Custom Date Container -->
                                    <div id="customDateContainer" class="align-items-center gap-2 {{ ($preset ?? '3_months') == 'custom' ? 'd-flex' : 'd-none' }}">
                                        <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="max-width: 280px;">
                                            <input type="date" class="form-control border-0 shadow-none bg-white py-1 px-2 text-secondary" id="from_date" name="from_date" value="{{ $fromDate ?? request('from_date') }}" title="From Date">
                                            <span class="input-group-text bg-white border-0 text-muted px-1">→</span>
                                            <input type="date" class="form-control border-0 shadow-none bg-white py-1 px-2 text-secondary" id="to_date" name="to_date" value="{{ $toDate ?? request('to_date') }}" title="To Date">
                                        </div>
                                        <button type="button" onclick="applyCustomDateFilter();" class="btn btn-sm custom-orange-btn text-white rounded-pill px-3 py-1 fw-semibold shadow-sm">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                    </div>
                                </div>

                                <!-- Status Filter Segmented Group -->
                                <div class="preset-segmented-control p-1 bg-light rounded-pill border d-inline-flex align-items-center gap-1">
                                    <span class="px-2 text-muted fw-bold small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-tasks me-1 text-warning"></i>Status:
                                    </span>
                                    <button type="button" class="btn btn-xs status-pill {{ ($statusFilter ?? 'all') == 'all' ? 'active' : '' }}" data-status="all">All</button>
                                    <button type="button" class="btn btn-xs status-pill {{ ($statusFilter ?? 'all') == 'approved' ? 'active' : '' }}" data-status="approved">Approved</button>
                                    <button type="button" class="btn btn-xs status-pill {{ ($statusFilter ?? 'all') == 'pending' ? 'active' : '' }}" data-status="pending">Pending</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Responsive Data Table -->
                <div class="table-responsive" id="responsive-table">
                    <table id="invoiceTable" class="table table-striped table-bordered align-middle text-center" style="transition: opacity 0.2s ease;">
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
let selectedSearchId = null;

function initInvoiceDataTable() {
    if ($.fn.DataTable.isDataTable('#invoiceTable')) {
        $('#invoiceTable').DataTable().destroy();
    }
    return $('#invoiceTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        order: [[3, 'desc']],
        lengthMenu: [[10, 20, 30, 50, 100, -1], [10, 20, 30, 50, 100, "All"]],
        pageLength: 20,
        language: {
            searchPlaceholder: "Search table records...",
            search: "",
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next: '<i class="fas fa-chevron-right"></i>'
            }
        },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip><"clear">'
    });
}

function fetchFilteredInvoices(preset, fromDate, toDate, statusFilter) {
    $('#invoiceTable').css('opacity', '0.4');
    $.ajax({
        url: "{{ route('invoice.index') }}",
        type: 'GET',
        data: {
            date_range: preset || $('#dateRangeInput').val(),
            from_date: fromDate || $('#from_date').val(),
            to_date: toDate || $('#to_date').val(),
            status_filter: statusFilter || $('#statusFilterInput').val()
        },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if ($.fn.DataTable.isDataTable('#invoiceTable')) {
                $('#invoiceTable').DataTable().destroy();
            }
            $('#invoiceTableBody').html(response);
            if (typeof setInvoiceTableDataLabels === 'function') {
                setInvoiceTableDataLabels();
            }
            initInvoiceDataTable();
            $('#invoiceTable').css('opacity', '1');
        },
        error: function() {
            $('#invoiceTable').css('opacity', '1');
        }
    });
}

$(document).ready(function() {
    initInvoiceDataTable();

    // Preset Pill Click Handler (AJAX reload, zero layout collapse)
    $(document).on('click', '.preset-pill, .date-preset-btn', function(e) {
        e.preventDefault();
        var preset = $(this).data('preset');
        $('#dateRangeInput').val(preset);
        $('.preset-pill').removeClass('active');
        $(this).addClass('active');

        if (preset === 'custom') {
            $('#customDateContainer').removeClass('d-none').addClass('d-flex');
        } else {
            $('#customDateContainer').removeClass('d-flex').addClass('d-none');
            $('#from_date').val('');
            $('#to_date').val('');
            fetchFilteredInvoices(preset, '', '');
        }
    });

    // Status Pill Click Handler
    $(document).on('click', '.status-pill', function(e) {
        e.preventDefault();
        var status = $(this).data('status');
        $('#statusFilterInput').val(status);
        $('.status-pill').removeClass('active');
        $(this).addClass('active');
        fetchFilteredInvoices();
    });
});

function applyCustomDateFilter() {
    var preset = $('#dateRangeInput').val() || 'custom';
    var fromDate = $('#from_date').val();
    var toDate = $('#to_date').val();
    fetchFilteredInvoices(preset, fromDate, toDate);
}

function exportFilteredInvoices() {
    var from = document.getElementById('from_date').value;
    var to = document.getElementById('to_date').value;
    var url = "{{ route('invoice.export') }}";
    if (from && to) {
        url += "?from_date=" + encodeURIComponent(from) + "&to_date=" + encodeURIComponent(to);
    }
    window.open(url, '_blank');
}

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

<style>
    .preset-segmented-control {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
    .preset-pill {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        color: #475569;
        border: 1px solid transparent;
        background: transparent;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .preset-pill:hover {
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .preset-pill.active {
        background-color: #ff6b00 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(255, 107, 0, 0.35) !important;
    }
    .status-pill {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        color: #475569;
        border: 1px solid transparent;
        background: transparent;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .status-pill:hover {
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .status-pill.active {
        background-color: #ff6b00 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(255, 107, 0, 0.35) !important;
    }
    .btn-xs {
        font-size: 0.75rem;
        line-height: 1.2;
    }
</style>

<!-- Responsive table and utility CSS moved to styles.css -->

@endsection