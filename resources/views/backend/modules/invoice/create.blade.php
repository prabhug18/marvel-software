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
                            <label for="email" class="form-label">Email ID <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email ID" required>
                        </div>

                        <div class="col-md-4">
                            <label for="address" class="form-label">Address 1 <span class="text-danger">*</span></label>
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
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pincode" name="pincode" placeholder="Enter Pincode" required>
                        </div>                        

                    </form>

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
                                placeholder="Enter Serial No"
                           required />
                        </div>

                        <div class="col-md-2">
                            <label for="invoiceProductQty" class="form-label">Qty <span class="text-danger">*</span></label>
                            <input
                            type="number"
                            class="form-control"
                            id="invoiceProductQty"
                            placeholder="Enter Qty"
                            min="1"
                            required />
                        </div>

                        <div class="col-md-2 position-relative d-flex flex-column justify-content-end">
                            <label for="invoiceProductPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                class="form-control"
                                id="invoiceProductPrice"
                                placeholder="Enter Unit Price"
                                min="0"
                                step="0.01"
                                required />
                            <div id="gstInclusivePriceLabel" class="form-text text-primary mt-1" style="display:none; font-weight:bold; min-height:22px; position:absolute; left:0; right:0; bottom:-28px; z-index:2;"></div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button
                            type="button"
                            class="btn custom-orange-btn text-white w-100"
                            id="invoiceAddProductBtn" >
                            Add Product
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

                    <!-- Quick Payment Section (Dynamic) -->
                    <div class="row g-4 justify-content-center mt-4" id="paymentFieldsContainer">
                        <div class="payment-entry row g-3 align-items-end mb-2">
                            <div class="col-md-4">
                                <label>Paid Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control paid-amount" name="paid_amount[]" placeholder="Enter amount">
                            </div>
                            <div class="col-md-4">
                                <label>Payment Mode</label>
                                <select class="form-control payment-mode" name="payment_mode[]">
                                    <option value="">Select Mode</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Bank">Bank</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-success add-payment-field">+</button>
                            </div>
                        </div>
                    </div>
                    <!-- Generate Invoice Button -->
                    <button class="btn btn-success mt-3" id="invoiceGenerateBtn">Generate Invoice</button>

                    @push('scripts')
                    <script>
                    $(document).ready(function() {
                        function paymentFieldTemplate() {
                            return `<div class="payment-entry row g-3 align-items-end mb-2">
                                <div class="col-md-4">
                                    <input type="number" step="0.01" min="0" class="form-control paid-amount" name="paid_amount[]" placeholder="Enter amount">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control payment-mode" name="payment_mode[]">
                                        <option value="">Select Mode</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Card">Card</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Bank">Bank</option>
                                        <option value="Cheque">Cheque</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-payment-field">-</button>
                                </div>
                            </div>`;
                        }
                        // Add new payment field
                        $(document).on('click', '.add-payment-field', function() {
                            $('#paymentFieldsContainer').append(paymentFieldTemplate());
                        });
                        // Remove payment field
                        $(document).on('click', '.remove-payment-field', function() {
                            $(this).closest('.payment-entry').remove();
                        });
                    });
                    </script>
                    @endpush
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
            });
        
            // --- Customer Auto Suggest and Autofill (AJAX version) ---
            $('#customer_name').on('input', function() {
                const val = $(this).val();
                const $suggestions = $('#customerSuggestions');
                $suggestions.empty();
                if (val.length > 0) {
                    $.ajax({
                        url: '/customer-search',
                        type: 'GET',
                        data: { q: val },
                        dataType: 'json',
                        success: function(customers) {
                            if (Array.isArray(customers) && customers.length > 0) {
                                const seen = new Set();
                                customers.forEach(function(c) {
                                    const key = (c.mobile_no || '') + '|' + (c.email || '');
                                    if (seen.has(key)) return;
                                    seen.add(key);
                                    $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-name="'+c.name+'" data-mobile="'+c.mobile_no+'" data-email="'+c.email+'" data-gst="'+(c.gst_no||'')+'" data-address="'+(c.address||'')+'" data-state="'+(c.state_id||'')+'" data-city="'+(c.city_id||'')+'" data-pincode="'+(c.pincode||'')+'">'+c.name+' ('+c.mobile_no+', '+c.email+')</button>');
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
            $('#invoiceProductName').on('input', function() {
                const val = $(this).val();
                let $suggestions = $('#productSuggestions');
                if ($suggestions.length === 0) {
                    $suggestions = $('<div id="productSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>');
                    $(this).after($suggestions);
                }
                $suggestions.empty();
                if (val.length > 0) {
                    $.ajax({
                        url: '/product-search',
                        type: 'GET',
                        data: { q: val },
                        dataType: 'json',
                        success: function(products) {
                            if (Array.isArray(products) && products.length > 0) {
                                const seen = new Set();
                                products.forEach(function(p) {
                                    // Only show products with stock > 0
                                    // if (p.stock !== undefined && Number(p.stock) <= 0) return;
                                    const key = (p.brand||'')+'|'+(p.series||'')+'|'+(p.model||'');
                                    if (seen.has(key)) return;
                                    seen.add(key);
                                    const display = [p.brand, p.series, p.model].filter(Boolean).join(' - ') + (p.category ? ' ('+p.category+')' : '');
                                    // Ensure GST is a number and not empty or null
                                    let tax_percentage = 0;
                                    if (p.tax_percentage !== undefined && p.tax_percentage !== null && p.tax_percentage !== '') {
                                        tax_percentage = parseFloat(p.tax_percentage);
                                        if (isNaN(tax_percentage)) tax_percentage = 0;
                                    }
                                    $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-brand="'+(p.brand||'')+'" data-series="'+(p.series||'')+'" data-model="'+(p.model||'')+'" data-category="'+(p.category||'')+'" data-price="'+(p.price||'')+'" data-tax_percentage="'+tax_percentage+'">'+display+'</button>');
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
                $('#productSuggestions').hide();
                // Show GST-inclusive price label
                $('#gstInclusivePriceLabel').text('GST Inclusive Price: ₹' + gst_inclusive_price.toFixed(2)).css('display','block');
            }); 
            
            // When user enters price manually, treat as GST-inclusive and reverse-calculate GST-exclusive
            $('#invoiceProductPrice').on('input', function() {
                let gst_inclusive_price = parseFloat($(this).val()) || 0;
                let gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
                let base_price = gst_inclusive_price;
                if (gst_percentage > 0) {
                    base_price = gst_inclusive_price / (1 + gst_percentage / 100);
                }
                if ($('#invoiceProductPriceGstIncl').length === 0) {
                    $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
                }
                $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
                $(this).data('gst-exclusive', base_price.toFixed(2));
                // --- Update preview total if qty is filled ---
                let qty = parseInt($('#invoiceProductQty').val()) || 0;
                let total = gst_inclusive_price * qty;
                // Optionally, you can show this total somewhere as a preview if needed
                // For now, this ensures that when addProduct() is called, the correct values are used
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

        // Get button elements
        const addProductBtn = document.getElementById('invoiceAddProductBtn');
        const generateInvoiceBtn = document.getElementById('invoiceGenerateBtn');

        addProductBtn.addEventListener('click', function() {
            addProduct();
            updateProductTable(); // Always update table to reflect latest Unit Price and Total
            // Remove GST Inclusive Price label after adding product
            $('#gstInclusivePriceLabel').text('').hide();
        });
        generateInvoiceBtn.addEventListener('click', function() {
            generateInvoice();
        });

        // === Add Product Function ===
        function addProduct() {
            const customerName = document.getElementById('customer_name').value.trim();
            if (!customerName) {
                alert('❌ Please select or enter a customer before adding products.');
                document.getElementById('customer_name').focus();
                return;
            }
            const name = document.getElementById('invoiceProductName').value.trim();
            const model = document.getElementById('invoiceProductModel').value.trim();
            const serialNo = document.getElementById('invoiceProductSerialNo').value.trim();
            const qty = parseInt(document.getElementById('invoiceProductQty').value);
            let gst_percentage = 0;
            if ($('#invoiceProductPrice').data('tax-percentage') !== undefined) {
                gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
            }
            if (!gst_percentage && $('#invoiceProductPriceGstIncl').length > 0) {
                gst_percentage = parseFloat($('#invoiceProductPriceGstIncl').data('tax-percentage')) || 0;
            }
            // Detect if product was selected from auto-suggestion (GST-inclusive price is stored in hidden field)
            let gst_inclusive_price;
            if ($('#invoiceProductPriceGstIncl').length > 0 && $('#invoiceProductPriceGstIncl').val() && !isNaN(parseFloat($('#invoiceProductPriceGstIncl').val()))) {
                // Product selected from auto-suggestion
                gst_inclusive_price = parseFloat($('#invoiceProductPriceGstIncl').val());
            } else {
                // Manual entry
                gst_inclusive_price = parseFloat(document.getElementById('invoiceProductPrice').value);
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
            if (!name || !model || !serialNo || isNaN(qty) || qty <= 0 || isNaN(gst_inclusive_price) || gst_inclusive_price < 0) {
                alert('❌ Please enter valid product details. Serial No is required.');
                return;
            }

            // --- Stock Validation AJAX (allow negative stock) ---
            $.ajax({
                url: '/check-stock',
                type: 'GET',
                data: {
                    model: model,
                    warehouse_id: warehouseId
                },
                dataType: 'json',
                success: function(response) {
                    const availableStock = response.available_stock !== undefined ? parseInt(response.available_stock) : null;
                    if (availableStock !== null && qty > availableStock) {
                        alert('⚠️ Warning: Not enough stock available. This will result in negative stock.');
                    }
                    // --- Fetch product tax_percentage from backend (AJAX) ---
                    $.ajax({
                        url: '/product-search',
                        type: 'GET',
                        data: { q: model },
                        dataType: 'json',
                        success: function(products) {
                            let tax_percentage = gst_percentage;
                            if (Array.isArray(products) && products.length > 0) {
                                const found = products.find(p => {
                                    return (p.model && p.model.toLowerCase() === model.toLowerCase());
                                });
                                if (found && found.tax_percentage !== undefined && found.tax_percentage !== null && found.tax_percentage !== '') {
                                    tax_percentage = parseFloat(found.tax_percentage);
                                }
                            }
                            // Always recalculate base_price and gst_inclusive_price for manual entry
                            let final_gst_inclusive_price = gst_inclusive_price;
                            let final_base_price = base_price;
                            if (tax_percentage > 0) {
                                final_base_price = gst_inclusive_price / (1 + tax_percentage / 100);
                                final_gst_inclusive_price = final_base_price * (1 + tax_percentage / 100);
                            }
                            productsArr.push({
                                name: name,
                                model: model,
                                serial_no: serialNo, // <-- use the correct variable name
                                qty: qty,
                                price: final_base_price,
                                gst_inclusive_price: final_gst_inclusive_price,
                                // Calculate total_incl_gst based on GST-exclusive unit price (base_price * qty)
                                total_incl_gst: final_base_price * qty,
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
                            productsArr.push({
                                name: name,
                                model: model,
                                serial_no: serialNo, // <-- use the correct variable name
                                qty: qty,
                                price: final_base_price,
                                gst_inclusive_price: final_gst_inclusive_price,
                                // Calculate total_incl_gst based on GST-exclusive unit price (base_price * qty)
                                total_incl_gst: final_base_price * qty,
                                tax_percentage: gst_percentage
                            });
                            updateProductTable();
                            clearProductFields();
                            updateTotals();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    alert('❌ Error checking stock. Please try again.');
                }
            });
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
                // Show GST-exclusive price (reverse calculated) and total
                const unit_price = product.price; // GST-exclusive
                const total_incl_gst = product.price * product.qty;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${product.name}</td>
                    <td>${product.model || ''}</td>
                    <td>${product.serial_no || ''}</td>
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
        }

        // === Update Totals ===
        function updateTotals() {
            // Grand total should be the sum of total_incl_gst for all products
            let grandTotal = productsArr.reduce((sum, p) => sum + (p.total_incl_gst || 0), 0);
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
            // Add GST amounts to grand total
            grandTotal += cgst + sgst + igst;
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

            if (!customer_name || !mobile_no || !address || !state || !city || !pincode || !invoice_number || !invoice_date || productsArr.length === 0) {
                alert("❌ Please fill all required fields and add at least one product.");
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
                    serial_no: p.serial_no,
                    qty: p.qty,
                    unit_price: base_price,
                    gst_inclusive_price: gst_inclusive_price,
                    total: total,
                    tax_percentage: p.tax_percentage,
                    tax_amount: tax_amount
                };
            });

            // --- Collect dynamic payment fields ---
            const paid_amount = [];
            const payment_mode = [];
            $('#paymentFieldsContainer .payment-entry').each(function() {
                const amt = parseFloat($(this).find('.paid-amount').val()) || 0;
                const mode = $(this).find('.payment-mode').val();
                paid_amount.push(amt);
                payment_mode.push(mode);
            });

            // Check if total paid matches grand total
            const totalPaid = paid_amount.reduce((sum, val) => sum + (parseFloat(val) || 0), 0);
            if (totalPaid !== grand_total) {
                alert('❌ Full amount not paid. Please ensure the total paid amount matches the Grand Total.');
                return;
            }

            // CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // AJAX POST to backend
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
                    paid_amount,
                    payment_mode
                }),
                success: function(response) {
                    alert('✅ Invoice saved successfully!');
                    if (response && response.invoice_id) {
                        // Open the invoice view page in a new tab and trigger BOTH email and print via auto=all param
                        var viewUrl = '/invoice-view?invoice_id=' + response.invoice_id + '&auto=all';
                        var win = window.open(viewUrl, '_blank');
                        // Fallback: if popup blocked, redirect in current tab
                        if (!win) {
                            window.location.href = viewUrl;
                        }
                    }
                    window.location.href = '/invoice';
                },
                error: function(xhr) {
                    let msg = '❌ Error saving invoice.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg += '\n' + xhr.responseJSON.message;
                    alert(msg);
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