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

                    <!-- Customer Info -->
                    <form class="row g-4">

                        @php
                            $isAdmin = auth()->user() && auth()->user()->hasRole('Admin');
                            $userWarehouseId = auth()->user() ? auth()->user()->warehouse_id : null;
                        @endphp
                        @if($isAdmin)
                        <div class="col-md-4">
                            <label for="warehouse_id" class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @else
                            <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $userWarehouseId }}">
                        @endif

                        <!-- Customer & Invoice Info -->
                        <div class="col-md-4 position-relative">
                            <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="name" placeholder="Enter Name / Mobile / Email" required autocomplete="off">
                            <div id="customerSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="invoice_number" placeholder="Enter Invoice  No." required readonly>
                        </div>

                        <div class="col-md-4">
                            <label for="dc_number" class="form-label">DC Number</label>
                            <input type="number" class="form-control" id="dc_number" name="dc_number" placeholder="Enter DC No." required>
                        </div>

                        <div class="col-md-4">
                            <label for="gst_number" class="form-label">GST Number</label>
                            <input type="text" class="form-control" id="gst_number" name="gst_number" placeholder="Enter GST No." required>
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                        </div>

                        <!-- Address Info -->
                        <div class="col-md-4">
                            <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="mobile_no" name="mobile_no" placeholder="Enter Mobile" required>
                        </div>

                        <div class="col-md-4">
                            <label for="email" class="form-label">Email ID</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email ID" required>
                        </div>

                        <div class="col-md-4">
                            <label for="address" class="form-label">Address 1</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address" required>
                        </div>

                        <div class="col-md-4">
                            <label for="invoiceState" class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state" class="form-select select2" id="state">
                                <option value="">Select State</option>
                                @foreach($state as $stateObj)
                                    <option value="{{ $stateObj->id }}">{{ $stateObj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="invoiceCity" class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city" class="form-select select2" id="city">
                                <option value="">Select City</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="number" class="form-control" id="pincode" name="pincode" placeholder="Enter Pincode" required>
                        </div>
                    </form>

                    <!-- Delivery Address Section -->
                    <div class="w-100 text-start ps-3 mt-4 mb-2">
                        <h4 class="mb-3">Delivery Address</h4>
                    </div>
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 d-flex align-items-center" style="min-height:70px;">
                                <input class="form-check-input me-3" type="radio" name="delivery_address_option" id="sameAsDelivery" value="same" checked>
                                <label class="form-check-label fw-semibold" for="sameAsDelivery">Same as Billing Address</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 d-flex align-items-center" style="min-height:70px;">
                                <input class="form-check-input me-3" type="radio" name="delivery_address_option" id="addNewDelivery" value="new">
                                <label class="form-check-label fw-semibold" for="addNewDelivery">Add Shipping Address</label>
                            </div>
                        </div>
                    </div>
                    <form id="deliveryAddressForm" class="row g-4 mb-3" style="display:none;">
                        <div class="col-md-5">
                            <label for="delivery_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="delivery_address" name="delivery_address" placeholder="Enter Delivery Address">
                        </div>
                        <div class="col-md-3">
                            <label for="delivery_state" class="form-label">State</label>
                            <select name="delivery_state" class="form-select select2" id="delivery_state">
                                <option value="">Select State</option>
                                @foreach($state as $stateObj)
                                    <option value="{{ $stateObj->id }}">{{ $stateObj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="delivery_city" class="form-label">City</label>
                            <select name="delivery_city" class="form-select select2" id="delivery_city">
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="delivery_pincode" class="form-label">Pincode</label>
                            <input type="number" class="form-control" id="delivery_pincode" name="delivery_pincode" placeholder="Pincode">
                        </div>
                    </form>

                    <script>
                    $(document).ready(function() {
                        // Show/hide delivery address fields
                        $('input[name="delivery_address_option"]').on('change', function() {
                            if($('#addNewDelivery').is(':checked')) {
                                $('#deliveryAddressForm').slideDown();
                            } else {
                                $('#deliveryAddressForm').slideUp();
                            }
                        });
                        // Initialize select2 for delivery state/city
                        $('#delivery_state').select2({
                            placeholder: "Select State",
                            width: '100%',
                            theme: 'bootstrap4'
                        });
                        $('#delivery_city').select2({
                            placeholder: "Select City",
                            width: '100%',
                            theme: 'bootstrap4'
                        });
                        // Load cities for delivery state
                        $('#delivery_state').change(function() {
                            var stateID = $(this).val();
                            if(stateID) {
                                $.ajax({
                                    url: '/get-city',
                                    type: 'GET',
                                    data: {state_id: stateID},
                                    success: function(data) {
                                        $('#delivery_city').empty().append('<option value="">Select City</option>');
                                        $.each(data, function(key, value) {
                                            $('#delivery_city').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                                        });
                                        $('#delivery_city').select2('destroy').select2({ theme: 'bootstrap4' });
                                    }
                                });
                            } else {
                                $('#delivery_city').empty().append('<option value="">Select City</option>');
                                $('#delivery_city').select2('destroy').select2({ theme: 'bootstrap4' });
                            }
                        });
                    });
                    </script>

                    <!-- Warranty Checker -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light border-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info mb-3"><i class="fas fa-shield-alt"></i> Warranty Checker</h5>
                                    <div class="row align-items-end g-3">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted">Scan / Enter Serial No</label>
                                            <input type="text" id="warrantyCheckSerial" class="form-control" placeholder="Serial Number">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-info text-white w-100" id="btnCheckWarranty">Check Warranty</button>
                                        </div>
                                        <div class="col-md-6" id="warrantyCheckResult">
                                            <!-- Result will appear here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-100 text-start ps-3">
                        <h4 class="mt-5 mb-3">Add Products</h4>
                    </div>
                    <form class="row g-4 mb-5">

                        <div class="col-md-2 position-relative">
                            <label for="invoiceProductName" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="invoiceProductName"
                                placeholder="Enter Product Name"
                                required
                                autocomplete="off"
                            />
                            <div id="productSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="invoiceProductModel" class="form-label">Model <span class="text-danger">*</span></label>
                            <input
                            type="text"
                            class="form-control"
                            id="invoiceProductModel"
                            placeholder="Enter Model"
                            required />
                        </div>

                        <div class="col-md-2">
                            <label for="invoiceProductSerialNo" class="form-label">Serial No <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="invoiceProductSerialNo"
                                placeholder="Enter Serial No(s)"
                                required
                                autocomplete="off"
                                multiple
                            />
                        </div>

                        <div class="col-md-1 px-1">
                            <label for="invoiceProductQty" class="form-label">Qty <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                class="form-control px-2"
                                id="invoiceProductQty"
                                placeholder="Qty"
                                min="1"
                                required
                            />
                            <div id="origPriceLabel" class="form-text text-secondary mt-1" style="display:none; font-weight:600; font-size: 0.75rem;">&nbsp;</div>
                        </div>

                        <div class="col-md-2 px-1">
                            <label for="invoiceProductGst" class="form-label">GST AMT</label>
                            <input
                                type="text"
                                class="form-control"
                                id="invoiceProductGst"
                                placeholder="GST Amount"
                                readonly
                            />
                        </div>

                        <div class="col-md-2 position-relative px-1">
                            <label for="invoiceProductPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="invoiceProductPrice"
                                    placeholder="Price"
                                    min="0"
                                    step="0.01"
                                    required />
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="verifyPriceBtn" title="Verify / Recalculate"><i class="fas fa-sync-alt"></i></button>
                            </div>
                            <div id="gstInclusivePriceLabel" class="form-text text-primary mt-1" style="display:none; font-weight:bold; font-size: 0.75rem; min-height:18px;"></div>
                        </div>

                        <div class="col-md-1 px-1">
                            <label class="form-label">&nbsp;</label>
                            <button
                            type="button"
                            class="btn custom-orange-btn text-white w-100"
                            id="invoiceAddProductBtn"
                            style="height: 38px;"
                            title="Add Product" >
                            <i class="fas fa-plus"></i>
                            </button>
                        </div>

                    </form>

                    <!-- Product Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center" id="invoiceProductTable">
                            <thead class="custom-thead table-primary">
                                <tr>
                                    <th>S.No</th>
                                    <th>Product Name</th>
                                    <th>Model</th>
                                    <th>Serial No</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted">
                                    <td colspan="8">No products added</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
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

                    <!-- Vehicle Details (optional) -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label for="vehicle_type" class="form-label">Vehicle Type (optional)</label>
                            <select id="vehicle_type" name="vehicle_type" class="form-select">
                                <option value="">Select Vehicle Type</option>
                                <option value="Two Wheeler">Two Wheeler</option>
                                <option value="Four Wheeler">Four Wheeler</option>
                                <option value="Commercial battery">Commercial battery</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="vehicle_details_wrap" style="display:none;">
                            <label for="vehicle_details" class="form-label">Details</label>
                            <input type="text" id="vehicle_details" name="vehicle_details" class="form-control" placeholder="Enter details (optional)">
                        </div>
                    </div>

                    <!-- Payments moved to Payment Reconciliation page -->
                    <!-- Generate Invoice Button -->
                    <button class="btn btn-success mt-3" id="invoiceGenerateBtn">Generate Invoice</button>

                    <!-- Payment UI removed from invoice create; use Payment Reconciliation page to record payments -->
                </div>
            </div>
        </div>
         
    </main>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Select2 CSS (should be loaded before any JS and before your main app CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <!-- Strong custom Select2 CSS to enforce Bootstrap 4 theme and layout -->
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
    <!-- Your main app CSS (should come after Select2 CSS) -->
    <link href="/assets/build/app.css" rel="stylesheet" />
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $(document).ready(function() {
                // Remove readonly attribute if present (defensive, in case browser cache or old markup)
                $('#state').removeAttr('readonly');
                $('#city').removeAttr('readonly');
                // Initialize select2 with search
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
                            if (Array.isArray(customers) && customers.length > 0) {
                                customers.forEach(function(c) {
                                    let displayLabel = (c.name || 'Unknown') + ' [' + (c.formatted_id || '') + '] (' + (c.mobile_no || '') + ')';
                                    $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-name="'+c.name+'" data-mobile="'+c.mobile_no+'" data-email="'+c.email+'" data-gst="'+(c.gst_no||'')+'" data-address="'+(c.address||'')+'" data-state="'+(c.state_id||'')+'" data-city="'+(c.city_id||'')+'" data-pincode="'+(c.pincode||'')+'">'+displayLabel+'</button>');
                                });
                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            $suggestions.hide();
                        }
                    });
                } else {
                    $suggestions.hide();
                }
            });

            // Use delegated click event for suggestion selection
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
                // Prefill city after AJAX loads city options
                $('#city').data('prefill', cityId); // Store cityId for use after AJAX
                $('#customerSuggestions').hide(); // Hide suggestions immediately after selection
            });

            // When city options are loaded (after state change), prefill city if needed
            $('#state').change(function() {
                var stateID = $(this).val();
                if(stateID) {
                    $.ajax({
                        url: '/get-city',
                        type: 'GET',
                        data: {state_id: stateID},
                        success: function(data) {
                            $('#city').empty().append('<option value="">Select City</option>');
                            $.each(data, function(key, value) {
                                $('#city').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                            });
                            // Always destroy and re-initialize select2 for city after AJAX
                            $('#city').select2('destroy').select2({
                                placeholder: "Select City",
                                width: '100%',
                                theme: 'bootstrap4'
                            });
                            // Prefill city if needed (after options are loaded)
                            var cityId = $('#city').data('prefill');
                            if (cityId) {
                                $('#city').val(cityId).trigger('change.select2');
                                $('#city').removeData('prefill');
                            }
                        }
                    });
                } else {
                    $('#city').empty().append('<option value="">Select City</option>');
                    $('#city').select2('destroy').select2({
                        placeholder: "Select City",
                        width: '100%',
                        theme: 'bootstrap4'
                    });
                }
            });            

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#customer_name, #customerSuggestions').length) {
                    $('#customerSuggestions').hide();
                }
            });
            
            // --- Product Auto Suggest and Autofill (AJAX version) ---
            let productSearchXhr = null;
            $('#invoiceProductName').on('input', function() {
                const val = $(this).val();
                let $suggestions = $('#productSuggestions');
                if ($suggestions.length === 0) {
                    $suggestions = $('<div id="productSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>');
                    $(this).after($suggestions);
                }
                
                if (productSearchXhr) {
                    productSearchXhr.abort();
                }

                if (val.length > 0) {
                    productSearchXhr = $.ajax({
                        url: '/product-search',
                        type: 'GET',
                        data: { q: val },
                        dataType: 'json',
                        success: function(products) {
                            $suggestions.empty();
                            if (Array.isArray(products) && products.length > 0) {
                                const seen = new Set();
                                products.forEach(function(p) {
                                    // Only show products with stock > 0
                                    // if (p.stock !== undefined && Number(p.stock) <= 0) return;
                                    const key = (p.brand||'')+'|'+(p.series||'')+'|'+(p.model||'')+'|'+(p.model_no||'');
                                    if (seen.has(key)) return;
                                    seen.add(key);
                                    const display = [p.brand, p.series, p.model, p.model_no].filter(Boolean).join(' - ') + (p.category ? ' ('+p.category+')' : '');
                                    // Ensure GST is a number and not empty or null
                                    let tax_percentage = 0;
                                    if (p.tax_percentage !== undefined && p.tax_percentage !== null && p.tax_percentage !== '') {
                                        tax_percentage = parseFloat(p.tax_percentage);
                                        if (isNaN(tax_percentage)) tax_percentage = 0;
                                    }
                                    // prefer offer_price if provided, otherwise fall back to price
                                    let suggestionPrice = (p.offer_price !== undefined && p.offer_price !== null && p.offer_price !== '') ? p.offer_price : p.price;
                                    $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-id="'+(p.id||'')+'" data-brand="'+(p.brand||'')+'" data-series="'+(p.series||'')+'" data-model="'+(p.model||'')+'" data-model_no="'+(p.model_no||'')+'" data-category="'+(p.category||'')+'" data-price="'+(suggestionPrice||'')+'" data-orig-price="'+(p.price||'')+'" data-tax_percentage="'+tax_percentage+'">'+display+'</button>');
                                });
                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            $suggestions.hide();
                        }
                    });
                } else {
                    $suggestions.hide();
                }
            });

            // Use delegated click event for product suggestion selection
            $(document).on('click', '#productSuggestions button', function() {
                const $btn = $(this);
                const productIdSelected = $btn.data('id') || null;
                const brand = $btn.data('brand') || '';
                const series = $btn.data('series') || '';
                const model = $btn.data('model') || '';
                // Always treat price as GST-inclusive and reverse-calculate base price
                let gst_inclusive_price = parseFloat($btn.data('price')) || 0;
                let gst_percentage = parseFloat($btn.data('tax_percentage'));
                if (isNaN(gst_percentage)) gst_percentage = 0;
                let base_price = gst_inclusive_price;
                if (gst_percentage > 0) {
                    base_price = gst_inclusive_price / (1 + gst_percentage / 100);
                }
                const name = [brand, series, model].filter(Boolean).join(' - ');
                $('#invoiceProductName').val(name);
                $('#invoiceProductModel').val(model);
                $('#invoiceProductPrice').val(base_price.toFixed(2)); // Always prefill GST-exclusive base price
                if ($('#invoiceProductPriceGstIncl').length === 0) {
                    $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
                }
                $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
                $('#invoiceProductPrice').data('gst-inclusive', gst_inclusive_price.toFixed(2));
                $('#invoiceProductPrice').data('tax-percentage', gst_percentage);
                // store selected product id and model_no on model input for later use
                $('#invoiceProductModel').data('product-id', productIdSelected);
                $('#invoiceProductModel').data('model-no', $btn.data('model_no') || '');
                // also store on the product name input so selections there carry the id
                $('#invoiceProductName').data('product-id', productIdSelected);
                // Populate new GST Amount field instead of GST %
                const gst_amount = gst_inclusive_price - base_price;
                $('#invoiceProductGst').val(gst_amount.toFixed(2));
                $('#productSuggestions').hide();
                // Show GST-inclusive price under Unit Price input
                $('#gstInclusivePriceLabel').html('<div>GST Inclusive: ₹' + gst_inclusive_price.toFixed(2) + '</div>').css({'display':'block'});
                // Show Original price under Serial No input if available
                var orig_price = parseFloat($btn.data('orig-price')) || null;
                if (orig_price && !isNaN(orig_price)) {
                    $('#origPriceLabel').html('Original Price: ₹' + orig_price.toFixed(2)).css({'display':'block'});
                } else {
                    $('#origPriceLabel').text('').hide();
                }
            }); 

            // If user types/selects product name directly (not clicking suggestion), try resolve on blur
            $('#invoiceProductName').on('blur change', function() {
                const val = $(this).val().trim();
                if (!val) {
                    clearProductFields();
                    return;
                }
                // If already have product-id stored, skip lookup
                if ($(this).data('product-id')) return;
                // Query backend for possible product matches
                $.ajax({
                    url: '/product-search',
                    type: 'GET',
                    data: { q: val },
                    dataType: 'json',
                    success: function(products) {
                        if (!Array.isArray(products) || products.length === 0) return;
                        // Try to match by full display (brand - series - model) or model
                        let matched = products.find(p => {
                            const display = [p.brand, p.series, p.model].filter(Boolean).join(' - ');
                            return (display.toLowerCase() === val.toLowerCase()) || (p.model && p.model.toLowerCase() === val.toLowerCase());
                        });
                        if (matched) {
                            const productId = matched.id || null;
                            $('#invoiceProductName').data('product-id', productId);
                            $('#invoiceProductModel').data('product-id', productId);
                            // also set model and price fields for convenience
                            if (matched.model) $('#invoiceProductModel').val(matched.model);
                                if ((matched.offer_price !== undefined && matched.offer_price !== null && matched.offer_price !== '') || matched.price !== undefined) {
                                    let gst_percentage = matched.tax_percentage !== undefined && matched.tax_percentage !== null ? parseFloat(matched.tax_percentage) : 0;
                                    let gst_inclusive_price = parseFloat(matched.offer_price !== undefined && matched.offer_price !== null && matched.offer_price !== '' ? matched.offer_price : matched.price) || 0;
                                let base_price = gst_inclusive_price;
                                if (gst_percentage > 0) base_price = gst_inclusive_price / (1 + gst_percentage/100);
                                $('#invoiceProductPrice').val(base_price.toFixed(2));
                                if ($('#invoiceProductPriceGstIncl').length === 0) $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
                                $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
                                $('#invoiceProductPrice').data('gst-inclusive', gst_inclusive_price.toFixed(2));
                                $('#invoiceProductPrice').data('tax-percentage', gst_percentage);
                                // Populate new GST Amount field instead of GST %
                                const gst_amount_matched = gst_inclusive_price - base_price;
                                $('#invoiceProductGst').val(gst_amount_matched.toFixed(2));
                                // Show GST-inclusive price under Unit Price input
                                $('#gstInclusivePriceLabel').html('<div>GST Inclusive: ₹' + gst_inclusive_price.toFixed(2) + '</div>').css({'display':'block'});
                                // Show Original price under Serial No input if available
                                var orig_price_matched = matched.price !== undefined && matched.price !== null && matched.price !== '' ? parseFloat(matched.price) : null;
                                if (orig_price_matched && !isNaN(orig_price_matched)) {
                                    $('#origPriceLabel').html('Original Price: ₹' + orig_price_matched.toFixed(2)).css({'display':'block'});
                                } else {
                                    $('#origPriceLabel').text('').hide();
                                }
                            }
                        }
                    }
                });
            });
            
            // When user clicks Verify, treat the entered price as GST-inclusive, calculate base price, and update UI
            $('#verifyPriceBtn').on('click', function() {
                let entered_price = parseFloat($('#invoiceProductPrice').val()) || 0;
                let gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
                
                let gst_inclusive_price = entered_price;
                let base_price = gst_inclusive_price;
                if (gst_percentage > 0) {
                    base_price = gst_inclusive_price / (1 + gst_percentage / 100);
                }

                if ($('#invoiceProductPriceGstIncl').length === 0) {
                    $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
                }
                
                // Set the input field to base price and hidden field to inclusive
                $('#invoiceProductPrice').val(base_price.toFixed(2));
                $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
                
                $('#invoiceProductPrice').data('gst-exclusive', base_price.toFixed(2));
                $('#invoiceProductPrice').data('gst-inclusive', gst_inclusive_price.toFixed(2));
                
                // Show GST-inclusive price under Unit Price input
                $('#gstInclusivePriceLabel').html('<div>GST Inclusive: ₹' + gst_inclusive_price.toFixed(2) + '</div>').css({'display':'block'});

                // Recalculate and update the GST Amount field as well
                const gst_amt_updated = gst_inclusive_price - base_price;
                $('#invoiceProductGst').val(gst_amt_updated.toFixed(2));
            });

            // Warranty Check Logic
            $('#btnCheckWarranty').on('click', function() {
                let serial = $('#warrantyCheckSerial').val().trim();
                let resultDiv = $('#warrantyCheckResult');
                if (!serial) {
                    resultDiv.html('<div class="text-danger small"><i class="fas fa-exclamation-circle"></i> Please enter a serial number.</div>');
                    return;
                }
                
                resultDiv.html('<div class="text-info small"><i class="fas fa-spinner fa-spin"></i> Checking warranty...</div>');
                
                $.ajax({
                    url: '/check-warranty',
                    type: 'GET',
                    data: { serial_no: serial },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'Error' || res.status === 'Not Sold') {
                            resultDiv.html(`<div class="badge bg-${res.badge} p-2 text-wrap w-100 text-start" style="font-size:0.9rem;">
                                <i class="fas fa-times-circle"></i> ${res.status}: ${res.message}</div>`);
                        } else {
                            resultDiv.html(`
                            <div class="border rounded p-2 bg-white" style="font-size:0.85rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">Status:</span>
                                    <span class="badge bg-${res.badge}">${res.status}</span>
                                </div>
                                <div class="row">
                                    <div class="col-6 text-muted">Sold To:</div><div class="col-6 fw-bold text-end">${res.customer ?? 'N/A'}</div>
                                    <div class="col-6 text-muted">Invoice Date:</div><div class="col-6 fw-bold text-end">${res.invoice_date} (${res.months_passed} mo)</div>
                                    <div class="col-6 text-muted">Warranty Details:</div><div class="col-6 fw-bold text-end">${res.foc_months} FOC / ${res.prorata_months} Pro-rata</div>
                                    <div class="col-6 text-muted">Est. Expiry:</div><div class="col-6 fw-bold text-end">${res.warranty_end}</div>
                                </div>
                            </div>`);
                        }
                    },
                    error: function(xhr) {
                        resultDiv.html('<div class="text-danger small"><i class="fas fa-exclamation-circle"></i> Error checking warranty. Please try again.</div>');
                    }
                });
            });

            $(document).ready(function() {
                // Auto-generate invoice number on page load
                $.ajax({
                    url: '/generate-invoice-number',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.invoice_number) {
                            $('#invoice_number').val(data.invoice_number);
                        }
                    }
                });
            });

            @if($isAdmin)
                $(document).ready(function() {
                    // On warehouse change, regenerate invoice number
                    $('#warehouse_id').on('change', function() {
                        var warehouseId = $(this).val();
                        $.ajax({
                            url: '/generate-invoice-number',
                            type: 'GET',
                            data: { warehouse_id: warehouseId },
                            dataType: 'json',
                            success: function(data) {
                                if (data.invoice_number) {
                                    $('#invoice_number').val(data.invoice_number);
                                }
                            }
                        });
                    });
                    // Trigger change on page load to set initial invoice number
                    $('#warehouse_id').trigger('change');
                });
            @endif
        });


        window.addEventListener("DOMContentLoaded", () => {
        let productsArr = [];

        // Barcode/Serial No input: auto-add comma after scan (barcode scanners usually send Enter or Tab)
        const serialInput = document.getElementById('invoiceProductSerialNo');
        const qtyInput = document.getElementById('invoiceProductQty');
        function updateQtyFromSerials() {
            if (!serialInput || !qtyInput) return;
            const rawValue = serialInput.value;
            // Split by comma for separate quantities, but ignore & for combo (qty 1)
            const commaSerials = rawValue.split(',').map(s => s.trim()).filter(Boolean);
            qtyInput.value = commaSerials.length > 0 ? commaSerials.length : '';
        }
        if (serialInput) {
            serialInput.addEventListener('keydown', function(e) {
                // Barcode scanners often send Enter (keyCode 13)
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // If last char is not comma, add comma
                    if (serialInput.value.trim() !== '' && !serialInput.value.trim().endsWith(',')) {
                        serialInput.value = serialInput.value.trim() + ', ';
                    }
                    updateQtyFromSerials();
                }
                // Tab: do nothing special, allow normal tab behavior
            });
            serialInput.addEventListener('input', updateQtyFromSerials);
            serialInput.addEventListener('blur', updateQtyFromSerials);
        }

        // Get button elements
        const addProductBtn = document.getElementById('invoiceAddProductBtn');
        const generateInvoiceBtn = document.getElementById('invoiceGenerateBtn');

        addProductBtn.addEventListener('click', function() {
            addProduct();
            updateProductTable(); // Always update table to reflect latest Unit Price and Total
            // Remove GST Inclusive Price label after adding product
            $('#gstInclusivePriceLabel').text('').hide();
            $('#origPriceLabel').text('').hide();
        });
        
        generateInvoiceBtn.addEventListener('click', function() {
            generateInvoice();
        });

        // === Add Product Function ===
        function addProduct() {
            const customerName = document.getElementById('customer_name').value.trim();
            if (!customerName) {
                Swal.fire({ icon: 'error', title: 'Missing Customer', text: 'Please select or enter a customer before adding products.' });
                document.getElementById('customer_name').focus();
                return;
            }
            const name = document.getElementById('invoiceProductName').value.trim();
            const model = document.getElementById('invoiceProductModel').value.trim();
            const serialNoRaw = document.getElementById('invoiceProductSerialNo').value.trim();
            // Accept multiple serial numbers separated by comma
            const serialNumbers = serialNoRaw.split(',').map(s => s.trim()).filter(Boolean);
            const qty = parseInt(document.getElementById('invoiceProductQty').value);
            let gst_percentage = 0;
            if ($('#invoiceProductPrice').data('tax-percentage') !== undefined) {
                gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
            }
            if (!gst_percentage && $('#invoiceProductPriceGstIncl').length > 0) {
                gst_percentage = parseFloat($('#invoiceProductPriceGstIncl').data('tax-percentage')) || 0;
            }
            // Detect if product was selected from auto-suggestion or verified manually
            let gst_inclusive_price;
            if ($('#invoiceProductPriceGstIncl').length > 0 && $('#invoiceProductPriceGstIncl').val() && !isNaN(parseFloat($('#invoiceProductPriceGstIncl').val()))) {
                gst_inclusive_price = parseFloat($('#invoiceProductPriceGstIncl').val());
            } else {
                // If they never clicked Verify, assume they typed base price or manually calculated.
                let base_val = parseFloat(document.getElementById('invoiceProductPrice').value);
                gst_inclusive_price = base_val * (1 + gst_percentage / 100);
            }
            // For auto-suggestion, use the price as GST-inclusive and do NOT reverse-calculate
            // For manual entry, reverse-calculate GST-exclusive price
            let base_price;
            if ($('#invoiceProductPriceGstIncl').length > 0 && $('#invoiceProductPriceGstIncl').val() && !isNaN(parseFloat($('#invoiceProductPriceGstIncl').val()))) {
                // Auto-suggestion: base price is already GST-exclusive in the input
                base_price = parseFloat(document.getElementById('invoiceProductPrice').value);
            } else {
                // Manual entry: reverse-calculate
                base_price = gst_inclusive_price;
                if (gst_percentage > 0) {
                    base_price = gst_inclusive_price / (1 + gst_percentage / 100);
                }
            }
            const warehouseId = $('#warehouse_id').val();
            if (!name || !model || serialNumbers.length === 0 || isNaN(qty) || qty <= 0 || isNaN(gst_inclusive_price) || gst_inclusive_price < 0) {
                Swal.fire({ icon: 'error', title: 'Invalid Details', text: 'Please enter valid product details. At least one Serial No is required.' });
                return;
            }

            // --- Strict Stock & Serial Validation ---
            $.ajax({
                url: '/check-stock',
                type: 'GET',
                data: {
                    model: model,
                    warehouse_id: warehouseId,
                    serial_no: serialNoRaw // Send the raw string of serials
                },
                dataType: 'json',
                success: function(response) {
                    const availableStock = response.available_stock !== undefined ? parseInt(response.available_stock) : 0;
                    const unavailable = response.unavailable_serials || [];

                    if (unavailable.length > 0 || qty > availableStock) {
                        let warningText = '';
                        if (unavailable.length > 0) {
                            warningText += 'The following serial numbers may already be sold or not in this warehouse: ' + unavailable.join(', ') + '. ';
                        }
                        if (qty > availableStock) {
                            warningText += 'Only ' + availableStock + ' units are available in this location, but you are adding ' + qty + '. ';
                        }
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Warning',
                            text: warningText + 'Do you still want to add this product to the invoice?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, add it',
                            cancelButtonText: 'No, cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                performAddProduct(name, model, serialNumbers, qty, base_price, gst_inclusive_price, gst_percentage);
                            }
                        });
                    } else {
                        // All checks passed
                        performAddProduct(name, model, serialNumbers, qty, base_price, gst_inclusive_price, gst_percentage);
                    }
                },
                error: function() {
                    // Fail gracefully - allow adding if verification fails
                    performAddProduct(name, model, serialNumbers, qty, base_price, gst_inclusive_price, gst_percentage);
                }
            });
        }

        // Helper to perform the actual product addition logic
        function performAddProduct(name, model, serialNumbers, qty, base_price, gst_inclusive_price, gst_percentage) {
            // --- Fetch product tax_percentage from backend (AJAX) ---
            $.ajax({
                url: '/product-search',
                type: 'GET',
                data: { q: model },
                dataType: 'json',
                success: function(products) {
                    let tax_percentage = gst_percentage;
                    let foundProductId = null;
                    if (Array.isArray(products) && products.length > 0) {
                        const found = products.find(p => {
                            return (p.model && p.model.toLowerCase() === model.toLowerCase());
                        });
                        if (found) {
                            if (found.tax_percentage !== undefined && found.tax_percentage !== null && found.tax_percentage !== '') {
                                tax_percentage = parseFloat(found.tax_percentage);
                            }
                            if (found.id !== undefined) foundProductId = found.id;
                        }
                    }
                    // Always recalculate base_price and gst_inclusive_price for manual entry
                    let final_gst_inclusive_price = gst_inclusive_price;
                    let final_base_price = base_price;
                    if (tax_percentage > 0) {
                        final_base_price = gst_inclusive_price / (1 + tax_percentage / 100);
                        final_gst_inclusive_price = final_base_price * (1 + tax_percentage / 100);
                    }
                    // Combined serial numbers into a single product entry
                    const combinedSerials = (serialNumbers || []).join(', ');
                    // prefer explicit selected id and model_no from UI if available
                    const selectedProductId = $('#invoiceProductModel').data('product-id') || foundProductId || null;
                    const selectedModelNo = $('#invoiceProductModel').data('model-no') || (found ? found.model_no : '');
                    productsArr.push({
                        name: name,
                        model: model,
                        model_no: selectedModelNo,
                        product_id: selectedProductId,
                        serial_no: combinedSerials,
                        qty: qty, 
                        price: final_base_price,
                        gst_inclusive_price: final_gst_inclusive_price,
                        total_incl_gst: final_gst_inclusive_price * qty,
                        tax_percentage: tax_percentage
                    });
                    updateProductTable();
                    clearProductFields();
                    updateTotals();
                },
                error: function(xhr, status, error) {
                    let final_gst_inclusive_price = gst_inclusive_price;
                    let final_base_price = base_price;
                    if (gst_percentage > 0) {
                        final_base_price = gst_inclusive_price / (1 + gst_percentage / 100);
                        final_gst_inclusive_price = final_base_price * (1 + gst_percentage / 100);
                    }
                    // Combine serial numbers into a single product entry for offline/error path
                    const combinedSerials = (serialNumbers || []).join(', ');
                    const selectedProductIdFallback = $('#invoiceProductModel').data('product-id') || null;
                    const selectedModelNoFallback = $('#invoiceProductModel').data('model-no') || '';
                    productsArr.push({
                        name: name,
                        model: model,
                        model_no: selectedModelNoFallback,
                        product_id: selectedProductIdFallback,
                        serial_no: combinedSerials,
                        qty: qty,
                        price: final_base_price,
                        gst_inclusive_price: final_gst_inclusive_price,
                        total_incl_gst: final_gst_inclusive_price * qty,
                        tax_percentage: gst_percentage
                    });
                    updateProductTable();
                    clearProductFields();
                    updateTotals();
                }
            });
        }

        function cleanProductName(name) {
            if (!name) return "";
            // Remove warranty strings like " - 48M - " or " - 24 months - "
            return name.replace(/\s*-\s*\d+\s*(M|months)\s*-\s*/gi, ' - ');
        }

        // === Update Product Table ===
        function updateProductTable() {
            const tbody = document.querySelector('#invoiceProductTable tbody');
            tbody.innerHTML = '';
            if (productsArr.length === 0) {
                tbody.innerHTML = '<tr class="text-muted"><td colspan="8">No products added</td></tr>';
                return;
            }
            productsArr.forEach((product, index) => {
                // Show GST-inclusive price (prefer offer/gst_inclusive) and total
                const unit_price = (product.gst_inclusive_price !== undefined && product.gst_inclusive_price !== null) ? Number(product.gst_inclusive_price) : Number(product.price);
                const total_incl_gst = (product.gst_inclusive_price !== undefined && product.gst_inclusive_price !== null) ? (Number(product.gst_inclusive_price) * Number(product.qty)) : (Number(product.total_incl_gst) || 0);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${cleanProductName(product.name)}</td>
                    <td>${product.model_no || product.model || ''}</td>
                    <td>${(product.serial_no || '').split(',').map(s => s.trim()).filter(Boolean).join('<br>')}</td>
                    <td>${product.qty}</td>
                    <td>₹${unit_price.toFixed(2)}</td>
                    <td>₹${total_incl_gst.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="removeProduct(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            updateTotals();
        }

        // === Remove Product Globally Accessible ===
        window.removeProduct = function(index) {
            productsArr.splice(index, 1);
            updateProductTable();
            updateTotals();
        };

        // === Clear Fields After Adding Product ===
        function clearProductFields() {
            document.getElementById('invoiceProductName').value = '';
            document.getElementById('invoiceProductModel').value = '';
            document.getElementById('invoiceProductSerialNo').value = '';
            document.getElementById('invoiceProductQty').value = '';
            document.getElementById('invoiceProductPrice').value = '';
            document.getElementById('invoiceProductGst').value = '';
        }

        // === Update Totals ===
        function updateTotals() {
            // Grand total should be the sum of GST-inclusive totals for all products
            let grandTotal = productsArr.reduce((sum, p) => sum + ((p.gst_inclusive_price !== undefined && p.qty !== undefined) ? (p.gst_inclusive_price * p.qty) : (p.total_incl_gst || 0)), 0);
            // Calculate GST splits as before
            let cgst = 0, sgst = 0, igst = 0, tax = 0;
            const stateId = document.getElementById('state').value;
            if (stateId == '35') {
                cgst = productsArr.reduce((sum, p) => {
                    let base_price = p.price;
                    let gst_amt = 0;
                    if (p.tax_percentage > 0) {
                        gst_amt = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + (gst_amt / 2);
                }, 0);
                sgst = productsArr.reduce((sum, p) => {
                    let base_price = p.price;
                    let gst_amt = 0;
                    if (p.tax_percentage > 0) {
                        gst_amt = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + (gst_amt / 2);
                }, 0);
                igst = 0;
                tax = cgst + sgst;
            } else {
                cgst = 0;
                sgst = 0;
                igst = productsArr.reduce((sum, p) => {
                    let base_price = p.price;
                    let gst_amt = 0;
                    if (p.tax_percentage > 0) {
                        gst_amt = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + gst_amt;
                }, 0);
                tax = igst;
            }
            // Do NOT add GST amounts again (gst_inclusive_price already includes tax)
            // Update UI
            const cgstEl = document.getElementById('invoiceCGST');
            const sgstEl = document.getElementById('invoiceSGST');
            const igstEl = document.getElementById('invoiceIGST');
            const grandTotalEl = document.getElementById('invoiceGrandTotal');
            if (cgstEl) cgstEl.textContent = cgst.toFixed(2);
            if (sgstEl) sgstEl.textContent = sgst.toFixed(2);
            if (igstEl) igstEl.textContent = igst.toFixed(2);
            if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);
        }

        // === Generate Invoice Function ===
        function generateInvoice() {
            // Gather all form data
            const customer_name = document.getElementById('customer_name').value;
            const mobile_no = document.getElementById('mobile_no').value;
            const email = document.getElementById('email').value;
            const address = document.getElementById('address').value;
            const state = document.getElementById('state').value;
            const city = document.getElementById('city').value;
            const pincode = document.getElementById('pincode').value;
            const gst_number = document.getElementById('gst_number').value;
            const invoice_number = document.getElementById('invoice_number').value;
            const invoice_date = document.getElementById('invoice_date').value;
            const dc_number = document.getElementById('dc_number').value;
            // Totals
            const cgst = parseFloat(document.getElementById('invoiceCGST').textContent) || 0;
            const sgst = parseFloat(document.getElementById('invoiceSGST').textContent) || 0;
            const igst = parseFloat(document.getElementById('invoiceIGST').textContent) || 0;
            const grand_total = parseFloat(document.getElementById('invoiceGrandTotal').textContent) || 0;

            const generateInvoiceBtn = document.getElementById('invoiceGenerateBtn');
            generateInvoiceBtn.disabled = true;
            generateInvoiceBtn.innerHTML = 'Generating... <i class="fas fa-spinner fa-spin"></i>';

            if (!customer_name || !mobile_no || !state || !city ||  !invoice_number || !invoice_date || productsArr.length === 0) {
                Swal.fire({ icon: 'error', title: 'Missing Fields', text: 'Please fill all required fields and add at least one product.' });
                generateInvoiceBtn.disabled = false;
                generateInvoiceBtn.innerHTML = 'Generate Invoice';
                return;
            }

            // Prepare products array for backend
            const products = productsArr.map(p => {
                // Always send GST-exclusive price and GST-inclusive price
                const base_price = p.price;
                const gst_inclusive_price = p.gst_inclusive_price;
                const tax_amount = p.tax_percentage > 0 ? (base_price * p.tax_percentage / 100) * p.qty : 0;
                const total = gst_inclusive_price * p.qty;
                return {
                    name: p.name,
                    model: p.model,
                    product_id: p.product_id || p.id || null,
                    serial_no: p.serial_no,
                    qty: p.qty,
                    unit_price: base_price,
                    gst_inclusive_price: gst_inclusive_price,
                    total: total,
                    tax_percentage: p.tax_percentage,
                    tax_amount: tax_amount
                };
            });

            // Payments are recorded on the Payment Reconciliation page; no payment validation here.

            // CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Collect delivery address fields if 'Add New Delivery Address' is selected
            let delivery_address_option = $('input[name="delivery_address_option"]:checked').val();
            let delivery_address = '';
            let delivery_state = '';
            let delivery_city = '';
            let delivery_pincode = '';
            if (delivery_address_option === 'new') {
                delivery_address = $('#delivery_address').val();
                delivery_state = $('#delivery_state').val();
                delivery_city = $('#delivery_city').val();
                delivery_pincode = $('#delivery_pincode').val();
            }

            // Vehicle optional fields
            var vehicle_type = $('#vehicle_type').val() || null;
            var vehicle_details = null;
            if (vehicle_type === 'Commercial battery') {
                vehicle_details = $('#vehicle_details').val() || null;
            }

            // AJAX POST to backend
            // DEBUG: log payload to console for debugging product_id persistence
            console.log('DEBUG: create -> products payload', products);
            $.ajax({
                url: '/invoice',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: 'application/json',
                data: JSON.stringify({
                    customer_name,
                    mobile_no,
                    email,
                    address,
                    state,
                    city,
                    pincode,
                    gst_number,
                    invoice_number,
                    invoice_date,
                    dc_number,
                    cgst,
                    sgst,
                    igst,
                    grand_total,
                    products,
                    warehouse_id: $('#warehouse_id').val(),
                    vehicle_type,
                    vehicle_details,
                    // payment handled separately via Payment Reconciliation
                    delivery_address_option,
                    delivery_address,
                    delivery_state,
                    delivery_city,
                    delivery_pincode
                }),
                success: function(response) {
                    Swal.fire({ icon: 'success', title: 'Success', text: 'Invoice saved successfully!', timer: 2000, showConfirmButton: false }).then(() => {
                        window.location.href = '/invoice';
                    });
                },
                error: function(xhr) {
                    let msg = 'Error saving invoice.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg += '\n' + xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    
                    const generateInvoiceBtn = document.getElementById('invoiceGenerateBtn');
                    generateInvoiceBtn.disabled = false;
                    generateInvoiceBtn.innerHTML = 'Generate Invoice';
                }
            });
        }

        // === Load Invoice List if on invoice-list.html ===
        const invoiceTable = document.querySelector("#invoiceTable tbody");
        if (invoiceTable) {
            const savedInvoices = JSON.parse(localStorage.getItem("invoices")) || [];

            savedInvoices.forEach((invoice, index) => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${invoice.invoiceNumber}</td>
                <td>${invoice.customer}</td>
                <td>${invoice.mobile}</td>
                <td>${invoice.city}, ${invoice.state}</td>
                <td>${invoice.date}</td>
                <td>${invoice.products.map(p => `${p.name} (x${p.qty})`).join(", ")}</td>
                <td>₹${invoice.grandTotal.toFixed(2)}</td>
            `;

            invoiceTable.appendChild(tr);
            });
        }
        });
   
    </script>
   
@endsection