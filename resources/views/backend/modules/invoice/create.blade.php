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
                            <select name="state" class="form-select select2" id="state" readonly>
                                <option value="">Select State</option>
                                @foreach($state as $stateObj)
                                    <option value="{{ $stateObj->id }}">{{ $stateObj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="invoiceCity" class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city" class="form-select select2" id="city" readonly>
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

                        <div class="col-md-3 position-relative">
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

                        <div class="col-md-3">
                            <label for="invoiceProductModel" class="form-label">Model <span class="text-danger">*</span></label>
                            <input
                            type="text"
                            class="form-control"
                            id="invoiceProductModel"
                            placeholder="Enter Model"
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

                        <div class="col-md-2">
                            <label for="invoiceProductPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input
                            type="number"
                            class="form-control"
                            id="invoiceProductPrice"
                            placeholder="Enter Unit Price"
                            min="0"
                            step="0.01"
                            required />
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
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted">
                                    <td colspan="6">No products added</td>
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

                    <!-- Generate Invoice Button -->
                    <button class="btn btn-success mt-3" id="invoiceGenerateBtn">Generate Invoice</button>

                    </div>
                </div>
            </div>
        </div>
         
    </main>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
        
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
                }
            });

            $(document).ready(function() {
                $('#state').select2({
                    placeholder: "Select State",
                    tags: true,
                    width: '100%'
                });
            });

            $(document).ready(function() {
                $('#city').select2({
                    placeholder: "Select City",
                    tags: true,
                    width: '100%'
                });
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
                                    if (p.stock !== undefined && Number(p.stock) <= 0) return;
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
            });

            // Update GST-Inclusive Price display when Unit Price is changed manually
            $('#invoiceProductPrice').on('input', function() {
                let base_price = parseFloat($(this).val()) || 0;
                let gst_percentage = parseFloat($(this).data('tax-percentage')) || 0;
                let gst_inclusive_price = base_price;
                if (gst_percentage > 0) {
                    gst_inclusive_price = base_price * (1 + gst_percentage / 100);
                }
                if ($('#invoiceProductPriceGstIncl').length === 0) {
                    $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
                }
                $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
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
        });
        generateInvoiceBtn.addEventListener('click', generateInvoice);

        // === Add Product Function ===
        function addProduct() {
            // Do NOT set or recalculate invoiceProductPrice here
            const customerName = document.getElementById('customer_name').value.trim();
            if (!customerName) {
                alert('❌ Please select or enter a customer before adding products.');
                document.getElementById('customer_name').focus();
                return;
            }
            const name = document.getElementById('invoiceProductName').value.trim();
            const model = document.getElementById('invoiceProductModel').value.trim();
            const qty = parseInt(document.getElementById('invoiceProductQty').value);
            const price = parseFloat(document.getElementById('invoiceProductPrice').value); // Always use as GST-exclusive
            let gst_percentage = 0;
            if ($('#invoiceProductPrice').data('tax-percentage')) {
                gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
            }
            let price_for_storage = price; // Always GST-exclusive
            const warehouseId = $('#warehouse_id').val();
            if (!name || !model || isNaN(qty) || qty <= 0 || isNaN(price) || price < 0) {
                alert('❌ Please enter valid product details.');
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
                    // No blocking on stock, just show a warning if stock is insufficient
                    const availableStock = response.available_stock !== undefined ? parseInt(response.available_stock) : null;
                    if (availableStock !== null && qty > availableStock) {
                        alert('⚠️ Warning: Not enough stock available. This will result in negative stock.');
                    }
                    // --- Continue with existing add product logic regardless of stock ---
                    // --- Fetch product tax_percentage from backend (AJAX) ---
                    $.ajax({
                        url: '/product-search',
                        type: 'GET',
                        data: { q: model },
                        dataType: 'json',
                        success: function(products) {
                            let tax_percentage = 5; // default to 5 now
                            if (Array.isArray(products) && products.length > 0) {
                                const found = products.find(p => {
                                    return (p.model && p.model.toLowerCase() === model.toLowerCase());
                                });
                                if (found && found.tax_percentage !== undefined && found.tax_percentage !== null && found.tax_percentage !== '') {
                                    tax_percentage = parseFloat(found.tax_percentage);
                                }
                            }
                            const productFullName = `${name} - ${model}`;
                            productsArr.push({ name: productFullName, qty, price: price_for_storage, total: qty * price_for_storage, tax_percentage: gst_percentage });
                            updateProductTable();
                            clearProductFields();
                            updateTotals();
                        },
                        error: function(xhr, status, error) {
                            const productFullName = `${name} - ${model}`;
                            productsArr.push({ name: productFullName, qty, price: price_for_storage, total: qty * price_for_storage, tax_percentage: 5 });
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
                tbody.innerHTML = '<tr class="text-muted"><td colspan="6">No products added</td></tr>';
                return;
            }
            productsArr.forEach((product, index) => {
                // Always use the user-entered price as GST-exclusive
                const base_price = product.price;
                const total_ex_gst = base_price * product.qty;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${product.name}</td>
                    <td>${product.qty}</td>
                    <td>₹${base_price.toFixed(2)}</td>
                    <td>₹${total_ex_gst.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="removeProduct(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            updateTotals(); // Ensure totals update after table update
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
            document.getElementById('invoiceProductQty').value = '';
            document.getElementById('invoiceProductPrice').value = '';
        }

        // === Update Totals ===
        function updateTotals() {
            // Calculate subtotal as sum of GST-exclusive totals
            const subtotal = productsArr.reduce((sum, p) => {
                const base_price = p.price;
                return sum + (base_price * p.qty);
            }, 0);
            const stateId = document.getElementById('state').value;
            let cgst = 0, sgst = 0, igst = 0, tax = 0;
            if (stateId == '35') {
                cgst = productsArr.reduce((sum, p) => {
                    const base_price = p.price;
                    let gst_price = 0;
                    if (p.tax_percentage > 0) {
                        gst_price = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + (gst_price / 2);
                }, 0);
                sgst = productsArr.reduce((sum, p) => {
                    const base_price = p.price;
                    let gst_price = 0;
                    if (p.tax_percentage > 0) {
                        gst_price = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + (gst_price / 2);
                }, 0);
                igst = 0;
                tax = cgst + sgst;
            } else {
                cgst = 0;
                sgst = 0;
                igst = productsArr.reduce((sum, p) => {
                    const base_price = p.price;
                    let gst_price = 0;
                    if (p.tax_percentage > 0) {
                        gst_price = (base_price * p.tax_percentage / 100) * p.qty;
                    }
                    return sum + gst_price;
                }, 0);
                tax = igst;
            }
            // Grand total is subtotal + total GST
            const grandTotal = Math.round(subtotal + cgst + sgst + igst);
            // Defensive: check if elements exist before updating
            const cgstEl = document.getElementById('invoiceCGST');
            const sgstEl = document.getElementById('invoiceSGST');
            const igstEl = document.getElementById('invoiceIGST');
            const grandTotalEl = document.getElementById('invoiceGrandTotal');
            const subtotalEl = document.getElementById('invoiceSubtotal');
            if (cgstEl) cgstEl.textContent = cgst.toFixed(2);
            if (sgstEl) sgstEl.textContent = sgst.toFixed(2);
            if (igstEl) igstEl.textContent = igst.toFixed(2);
            if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2); // Always show .00
            if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
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
                const base_price = p.price; // always GST-exclusive, user-entered
                const total_ex_gst = base_price * p.qty;
                return {
                    name: p.name,
                    model: p.name.split(' - ').pop(),
                    qty: p.qty,
                    unit_price: base_price, // store as unit_price
                    total: total_ex_gst,   // store as total
                    tax_percentage: p.tax_percentage
                };
            });

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
                    warehouse_id: $('#warehouse_id').val() // Always include warehouse_id
                }),
                success: function(response) {
                    alert('✅ Invoice saved successfully!');
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