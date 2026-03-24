@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')
        <div class="container-fluid px-3" style="padding-top: 30px;">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <h2 class="mb-4">{{ $heading ?? 'Edit Invoice' }}</h2>
                    <form id="invoiceEditForm" method="POST" action="{{ route('invoice.update', $invoice->id) }}">
                        @csrf
                        @method('PUT')
                        @php
                            $isAdmin = auth()->user() && auth()->user()->hasRole('Admin');
                            $userWarehouseId = auth()->user() ? auth()->user()->warehouse_id : null;
                            $customer = $invoice->customer;
                        @endphp
                        <div class="row g-4">
                            @if($isAdmin)
                            <div class="col-md-4">
                                <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select" id="warehouse_id" name="warehouse_id" required readonly style="pointer-events:none; background-color:#e9ecef;">
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $invoice->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="warehouse_id" value="{{ $invoice->warehouse_id }}">
                            </div>
                            @else
                                <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $userWarehouseId }}">
                            @endif
                            <div class="col-md-4 position-relative">
                                <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $invoice->customer_name }}" required autocomplete="off" readonly>
                                <div id="customerSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $invoice->invoice_number }}" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="dc_number" class="form-label">DC Number</label>
                                <input type="number" class="form-control" id="dc_number" name="dc_number" value="{{ $invoice->dc_number }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" class="form-control" id="gst_number" name="gst_number" value="{{ $customer->gst_no ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="invoice_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date }}" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="mobile_no" name="mobile_no" value="{{ $customer->mobile_no ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email ID</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="address" class="form-label">Address 1</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $customer->address ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <select name="state" class="form-select select2" id="state">
                                    <option value="">Select State</option>
                                    @foreach($states as $stateObj)
                                        <option value="{{ $stateObj->id }}" {{ $customer->state_id == $stateObj->id ? 'selected' : '' }}>{{ $stateObj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <select name="city" class="form-select select2" id="city">
                                    <option value="{{ $customer->city_id ?? '' }}">{{ $customer->city->name ?? 'Select City' }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="number" class="form-control" id="pincode" name="pincode" value="{{ $customer->pincode ?? '' }}" required>
                            </div>
                            
                            
                        </div>

                        <div class="w-100 text-start ps-3">
                            <h4 class="mt-5 mb-3">Add Products</h4>
                        </div>
                        @include('backend.modules.invoice.partials.product_table_edit', ['invoice' => $invoice])

                        <div class="mt-4">
                            <div class="row text-center mt-4">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 shadow-sm bg-light">
                                        <p class="mb-1 fw-bold text-primary">CGST</p>
                                        <p class="fs-5">₹<span id="invoiceCGST">0.00</span></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 shadow-sm bg-light">
                                        <p class="mb-1 fw-bold text-success">SGST</p>
                                        <p class="fs-5">₹<span id="invoiceSGST">0.00</span></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 shadow-sm bg-light">
                                        <p class="mb-1 fw-bold text-danger">IGST</p>
                                        <p class="fs-5">₹<span id="invoiceIGST">0.00</span></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 shadow-sm bg-warning bg-opacity-25">
                                        <p class="mb-1 fw-bold text-dark">Grand Total</p>
                                        <p class="fs-5">₹<span id="invoiceGrandTotal">0.00</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Type & Details (moved here so they're near the submit button) -->
                        <div class="row g-4 mt-3">
                            <div class="col-md-4">
                                <label for="vehicle_type" class="form-label">Vehicle Type (optional)</label>
                                <select id="vehicle_type" name="vehicle_type" class="form-select">
                                    <option value="">Select Vehicle Type</option>
                                    <option value="Two Wheeler" {{ $invoice->vehicle_type == 'Two Wheeler' ? 'selected' : '' }}>Two Wheeler</option>
                                    <option value="Four Wheeler" {{ $invoice->vehicle_type == 'Four Wheeler' ? 'selected' : '' }}>Four Wheeler</option>
                                    <option value="Commercial battery" {{ $invoice->vehicle_type == 'Commercial battery' ? 'selected' : '' }}>Commercial battery</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="vehicle_details_wrap" style="{{ $invoice->vehicle_type == 'Commercial battery' ? '' : 'display:none;' }}">
                                <label for="vehicle_details" class="form-label"> Details</label>
                                <input type="text" id="vehicle_details" name="vehicle_details" class="form-control" value="{{ $invoice->vehicle_details ?? '' }}" placeholder="Enter details (optional)">
                            </div>
                        </div>

                        <!-- Payments moved to Payment Reconciliation page -->

                        <div class="row g-3 mb-3">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-success mt-3" id="invoiceUpdateBtn">Update Invoice</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap4 .select2-selection {
          border-radius: 0.25rem !important;
          min-height: 44px !important;
          border: 1px solid #ced4da !important;
          background-color: rgb(249, 249, 249) !important;
          font-size: 15px !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
          line-height: 2.9 !important;
          float: left !important;
        }
        .select2-container--bootstrap4 .select2-selection--single {
          height: 50px !important;
        }
        .select2-container--bootstrap4 .select2-selection--multiple {
          min-height: 45px !important;
        }
        .select2-container {
          width: 100% !important;
          z-index: 1060 !important;
        }
        .select2-dropdown {
          z-index: 2000 !important;
        }
    </style>
    <link href="/assets/build/app.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Match create page button and payment section styles */
        #invoiceUpdateBtn {
            margin-top: 24px;
            min-width: 180px;
            font-size: 1.2rem;
            padding: 12px 32px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .row.mb-3.mt-3, .row.g-3.mb-3 {
            margin-bottom: 2rem !important;
        }
        .btn-outline-primary {
            border-width: 2px;
        }
        #addPaymentFieldBtn {
            min-width: 140px;
            font-size: 1rem;
        }
        /* Responsive fix for bottom button */
        @media (max-width: 600px) {
            #invoiceUpdateBtn {
                width: 100%;
                min-width: unset;
                font-size: 1rem;
                padding: 10px 0;
            }
        }
    </style>
    <script>
    $(document).ready(function() {
        // --- Select2 for State/City ---
        $('#state').select2({
            placeholder: "Select State",
            width: '100%',
            theme: 'bootstrap4'
        });
        $('#city').select2({
            placeholder: "Select City",
            width: '100%',
            theme: 'bootstrap4'
        });

        // Vehicle type toggle
        $('#vehicle_type').on('change', function() {
            var v = $(this).val();
            if (v === 'Commercial battery') {
                $('#vehicle_details_wrap').slideDown();
            } else {
                $('#vehicle_details_wrap').slideUp();
                $('#vehicle_details').val('');
            }
        });

        // --- Customer Auto Suggest and Autofill (AJAX version) ---
        let customerSearchXhr = null;
        $('#customer_name').on('input', function() {
            const val = $(this).val();
            const $suggestions = $('#customerSuggestions');
            
            if (customerSearchXhr) {
                customerSearchXhr.abort();
            }

            if (val.length > 0) {
                customerSearchXhr = $.ajax({
                    url: '/customer-search',
                    type: 'GET',
                    data: { q: val },
                    dataType: 'json',
                    success: function(customers) {
                        $suggestions.empty();
                        if(customers.length > 0) {
                            customers.forEach(function(c) {
                                $suggestions.append(`<button type="button" class="list-group-item list-group-item-action" data-name="${c.name}" data-gst="${c.gst_no}" data-mobile="${c.mobile_no}" data-email="${c.email}" data-address="${c.address}" data-pincode="${c.pincode}" data-state="${c.state_id}" data-city="${c.city_id}">${c.name} (${c.mobile_no})</button>`);
                            });
                            $suggestions.show();
                        } else {
                            $suggestions.hide();
                        }
                    },
                });
            } else {
                $suggestions.hide();
            }
        });

        $(document).on('click', '#customerSuggestions button', function() {
            const $btn = $(this);
            $('#customer_name').val($btn.data('name'));
            $('#gst_number').val($btn.data('gst'));
            $('#mobile_no').val($btn.data('mobile'));
            $('#email').val($btn.data('email'));
            $('#address').val($btn.data('address'));
            $('#pincode').val($btn.data('pincode'));
            const stateId = $btn.data('state');
            const cityId = $btn.data('city');
            $('#state').val(stateId).trigger('change');
            $('#city').data('prefill', cityId);
            $('#customerSuggestions').hide();
        });

        $('#state').change(function() {
            var stateID = $(this).val();
            if(stateID) {
                $.ajax({
                    url: '/city-list',
                    type: 'GET',
                    data: { state_id: stateID },
                    dataType: 'json',
                    success: function(cities) {
                        $('#city').empty();
                        $('#city').append('<option value="">Select City</option>');
                        cities.forEach(function(city) {
                            $('#city').append(`<option value="${city.id}">${city.name}</option>`);
                        });
                        $('#city').val($('#city').data('prefill')).trigger('change');
                    }
                });
            } else {
                $('#city').empty().append('<option value="">Select City</option>');
                $('#city').select2('destroy').select2({ theme: 'bootstrap4' });
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#customer_name, #customerSuggestions').length) {
                $('#customerSuggestions').hide();
            }
        });

        // Payments are recorded on the Payment Reconciliation page; payment UI removed from this page.
    });

    // --- Product Table and JS Logic ---
    window.addEventListener("DOMContentLoaded", () => {
        window.productsArr = @json($invoice->items);
        // You can now use productsArr for add/edit/remove logic as in create.blade.php
        // ...copy/adapt JS logic from create page for product table, totals, etc...
    });
    </script>
<script>
$(document).ready(function() {
    $('#invoiceUpdateBtn').on('click', function(e) {
        e.preventDefault();
        console.log('invoiceUpdateBtn clicked');
        // --- Collect form data ---
        let form = $('#invoiceEditForm');
        let formData = form.serializeArray();
    // --- Collect productsArr (use server-side fallback if window.productsArr is not populated) ---
    const serverProductsFallback = @json($invoice->items ?? []);
    let productsArr = (window.productsArr && Array.isArray(window.productsArr)) ? window.productsArr : (Array.isArray(serverProductsFallback) ? serverProductsFallback : []);
        if (!productsArr.length) {
            Swal.fire({ icon: 'error', title: 'No Products', text: 'Please add at least one product.' });
            return;
        }
        // --- Validate serial_no for all products ---
        for (let i = 0; i < productsArr.length; i++) {
            let sn = productsArr[i].serial_no || productsArr[i].serialNo || '';
            if (!sn || sn.trim() === '') {
                // Try to highlight the serial number input for this product row
                let productRow = $("#productTable tbody tr").eq(i);
                let serialInput = productRow.find('.serial-no-input');
                if (serialInput.length) {
                    serialInput.addClass('border-danger');
                    serialInput.focus();
                }
                Swal.fire({ icon: 'error', title: 'Serial Number Required', text: 'Serial number is required for all products. Please enter serial number for product #' + (i+1) + '.' });
                return;
            } else {
                // Remove highlight if present
                let productRow = $("#productTable tbody tr").eq(i);
                let serialInput = productRow.find('.serial-no-input');
                if (serialInput.length) {
                    serialInput.removeClass('border-danger');
                }
            }
        }

        // Payments are handled on the Payment Reconciliation page. Skip collecting inline payment fields.
        // --- Validate required fields ---
        let requiredFields = ['customer_name','invoice_number','invoice_date','warehouse_id','state','city','mobile_no'];
        for (let field of requiredFields) {
            let val = form.find('[name="'+field+'"]').val();
            if (!val) {
                Swal.fire({ icon: 'error', title: 'Missing Fields', text: 'Please fill all required fields.' });
                return;
            }
        }
        // No inline payment validation here — payments are recorded separately in Payment Reconciliation.
        // --- Prepare products for backend ---
        let products = productsArr.map(p => {
            // Calculate tax_amount for each product
            let tax_amount = 0;
            if (p.tax_percentage && p.price && p.qty) {
                tax_amount = (p.price * p.qty) * (p.tax_percentage / 100);
            }
            return {
                name: p.name,
                model: p.model,
                product_id: p.product_id || p.id || null,
                serial_no: (typeof p.serial_no !== 'undefined' && p.serial_no !== null) ? p.serial_no : (typeof p.serialNo !== 'undefined' && p.serialNo !== null ? p.serialNo : ''),
                qty: p.qty,
                unit_price: p.price,
                gst_inclusive_price: p.gst_inclusive_price,
                total: p.total,
                tax_percentage: p.tax_percentage,
                tax_amount: tax_amount
            };
        });
        // --- Prepare data for AJAX ---
        let data = {};
        formData.forEach(function(item) { data[item.name] = item.value; });
        // Ensure vehicle fields are explicitly included (serializeArray should include them, but be explicit)
        data.vehicle_type = $('#vehicle_type').val() || null;
        data.vehicle_details = ($('#vehicle_details').length ? $('#vehicle_details').val() : null) || null;
    data.products = products;
        data.cgst = parseFloat($('#invoiceCGST').text()) || 0;
        data.sgst = parseFloat($('#invoiceSGST').text()) || 0;
        data.igst = parseFloat($('#invoiceIGST').text()) || 0;
    // Grand total read from the UI
    let grandTotal = parseFloat($('#invoiceGrandTotal').text()) || 0;
    data.grand_total = grandTotal;
        // --- AJAX PUT request ---
        // DEBUG: log payload to console for debugging product_id persistence
        console.log('DEBUG: edit -> products payload', data.products);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-HTTP-Method-Override': 'PUT' },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire({ icon: 'success', title: 'Updated!', text: 'Invoice updated successfully!', timer: 2000, showConfirmButton: false }).then(() => {
                    window.location.href = '/invoice';
                });
            },
            error: function(xhr) {
                let msg = 'Error updating invoice.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += '\n' + xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });
});
</script>
@endsection
