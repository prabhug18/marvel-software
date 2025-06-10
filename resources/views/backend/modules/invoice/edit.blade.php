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
                    @if(!Auth::user() || !Auth::user()->hasRole('Admin'))
                        <div class="alert alert-danger">You do not have permission to edit invoices.</div>
                    @else
                        <form id="invoiceEditForm" method="POST" action="{{ route('invoice.update', $invoice->id) }}" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                            <input type="hidden" name="cgst" id="hiddenCGST" value="{{ $invoice->cgst }}">
                            <input type="hidden" name="sgst" id="hiddenSGST" value="{{ $invoice->sgst }}">
                            <input type="hidden" name="igst" id="hiddenIGST" value="{{ $invoice->igst }}">
                            <input type="hidden" name="grand_total" id="hiddenGrandTotal" value="{{ $invoice->grand_total }}">
                            @php
                                $isAdmin = auth()->user() && auth()->user()->hasRole('Admin');
                                $userWarehouseId = auth()->user() ? auth()->user()->warehouse_id : null;
                                $customer = $invoice->customer;
                            @endphp
                            @if($isAdmin)
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $invoice->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $userWarehouseId }}">
                            @endif
                                <div class="col-md-4 position-relative">
                                    <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $invoice->customer_name }}" required autocomplete="off">
                                    <div id="customerSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $invoice->invoice_number }}" required readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="dc_number" class="form-label">DC Number <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="dc_number" name="dc_number" value="{{ $invoice->dc_number }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="gst_number" class="form-label">GST Number</label>
                                    <input type="text" class="form-control" id="gst_number" name="gst_number" value="{{ $customer->gst_no ?? '' }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="mobile_no" name="mobile_no" value="{{ $customer->mobile_no ?? '' }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email ID <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email ?? '' }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="address" class="form-label">Address 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" value="{{ $customer->address ?? '' }}" required>
                                </div>
                            </div>
                            <div class="row">
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
                                    <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="pincode" name="pincode" value="{{ $customer->pincode ?? '' }}" required>
                                </div>
                            </div>

                            <!-- Product Table (show existing items, allow edit/remove/add) -->
                            @php
                                // Prepare productsArr for JS prefill (array of items)
                                $productsArr = $invoice->items->map(function($item) {
                                    return [
                                        'product_name' => $item->product_name,
                                        'model' => $item->model,
                                        'qty' => $item->qty,
                                        'unit_price' => $item->unit_price,
                                        'total' => $item->total,
                                        'tax_percentage' => $item->tax_percentage,
                                    ];
                                });
                            @endphp
                            <script>
                                window.productsArr = @json($productsArr);
                            </script>
                            @include('backend.modules.invoice.partials.product_table_edit', ['invoice' => $invoice])

                            <!-- Totals: Only CGST, SGST, IGST, Grand Total in a single row -->
                            <div class="mt-4">
                                <div class="row text-center mt-4">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 shadow-sm bg-light">
                                            <p class="mb-1 fw-bold text-dark">CGST</p>
                                            <p class="fs-5">₹<span id="invoiceCGST">0.00</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 shadow-sm bg-light">
                                            <p class="mb-1 fw-bold text-dark">SGST</p>
                                            <p class="fs-5">₹<span id="invoiceSGST">0.00</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 shadow-sm bg-light">
                                            <p class="mb-1 fw-bold text-dark">IGST</p>
                                            <p class="fs-5">₹<span id="invoiceIGST">0.00</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 shadow-sm bg-light">
                                            <p class="mb-1 fw-bold text-dark">Grand Total</p>
                                            <p class="fs-5">₹<span id="invoiceGrandTotal">0.00</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 mt-3">
                                <div class="col-md-12 text-end">
                                    <input type="submit" class="btn btn-primary" value="Update Invoice" id="updateInvoiceBtn">
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
         
    </main>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Remove debug logs and force submit
        $('#updateInvoiceBtn').on('click', function(e) {
            e.preventDefault();
            $('#invoiceEditForm').submit();
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
                    error: function() { $suggestions.hide(); }
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
                    url: '/get-city',
                    type: 'GET',
                    data: {state_id: stateID},
                    success: function(data) {
                        $('#city').empty().append('<option value="">Select City</option>');
                        $.each(data, function(key, value) {
                            $('#city').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                        });
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
        $('#state').select2({ placeholder: "Select State", tags: true, width: '100%' });
        $('#city').select2({ placeholder: "Select City", tags: true, width: '100%' });
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
                                if (p.stock !== undefined && Number(p.stock) <= 0) return;
                                const key = (p.brand||'')+'|'+(p.series||'')+'|'+(p.model||'');
                                if (seen.has(key)) return;
                                seen.add(key);
                                const display = [p.brand, p.series, p.model].filter(Boolean).join(' - ') + (p.category ? ' ('+p.category+')' : '');
                                $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-brand="'+(p.brand||'')+'" data-series="'+(p.series||'')+'" data-model="'+(p.model||'')+'" data-category="'+(p.category||'')+'" data-price="'+(p.price||'')+'">'+display+'</button>');
                            });
                            $suggestions.show();
                        } else {
                            $suggestions.hide();
                        }
                    },
                    error: function() { $suggestions.hide(); }
                });
            } else {
                $suggestions.hide();
            }
        });
        $(document).on('click', '#productSuggestions button', function() {
            const $btn = $(this);
            const brand = $btn.data('brand') || '';
            const series = $btn.data('series') || '';
            const model = $btn.data('model') || '';
            const price = $btn.data('price') || '';
            const name = [brand, series, model].filter(Boolean).join(' - ');
            $('#invoiceProductName').val(name);
            $('#invoiceProductModel').val(model);
            $('#invoiceProductPrice').val(price);
            $('#productSuggestions').hide();
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#invoiceProductName, #productSuggestions').length) {
                $('#productSuggestions').hide();
            }
        });
        // --- Enable form submit for Update Invoice ---
        $('#invoiceEditForm').off('submit').on('submit', function(e) {
            // Only sync productsArr to hidden input, do NOT call e.preventDefault() or return false
            if (window.productsArr && window.productsArr.length > 0) {
                $(this).find('input[name="products_json"]').remove();
                $('<input>').attr({
                    type: 'hidden',
                    name: 'products_json',
                    value: JSON.stringify(window.productsArr)
                }).appendTo(this);
            }
            // Update hidden tax/total fields before submit
            $('#hiddenCGST').val($('#invoiceCGST').text());
            $('#hiddenSGST').val($('#invoiceSGST').text());
            $('#hiddenIGST').val($('#invoiceIGST').text());
            $('#hiddenGrandTotal').val($('#invoiceGrandTotal').text());
            // Allow normal submit
        });
    });

    // --- Product Add/Edit/Remove/Validation/Update Totals (COPY from create page) ---
    window.productsArr = window.productsArr || [];
    window.addEventListener("DOMContentLoaded", () => {
        // Attach event listeners
        const addProductBtn = document.getElementById('invoiceAddProductBtn');
        if (addProductBtn) addProductBtn.addEventListener('click', addProduct);
        // Update table and totals on load
        if (typeof updateProductTable === 'function') {
            updateProductTable();
            updateTotals();
        }
    });
    function addProduct() {
        const customerName = document.getElementById('customer_name').value.trim();
        if (!customerName) {
            alert('❌ Please select or enter a customer before adding products.');
            document.getElementById('customer_name').focus();
            return;
        }
        const name = document.getElementById('invoiceProductName').value.trim();
        const model = document.getElementById('invoiceProductModel').value.trim();
        const qty = parseInt(document.getElementById('invoiceProductQty').value);
        const price = parseFloat(document.getElementById('invoiceProductPrice').value);
        const warehouseId = $('#warehouse_id').val();
        if (!name || !model || isNaN(qty) || qty <= 0 || isNaN(price) || price < 0) {
            alert('❌ Please enter valid product details.');
            return;
        }
        // --- Stock Validation AJAX ---
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
                if (availableStock === null) {
                    alert('❌ Unable to check stock. Please try again.');
                    return;
                }
                if (qty > availableStock) {
                    alert('❌ Not enough stock available. Only ' + availableStock + ' units in stock.');
                    return;
                }
                // --- Continue with existing add product logic if stock is sufficient ---
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
                        window.productsArr.push({ name: productFullName, product_name: productFullName, model, qty, price, total: qty * price, tax_percentage });
                        updateProductTable();
                        clearProductFields();
                        updateTotals();
                    },
                    error: function() {
                        const productFullName = `${name} - ${model}`;
                        window.productsArr.push({ name: productFullName, product_name: productFullName, model, qty, price, total: qty * price, tax_percentage: 5 });
                        updateProductTable();
                        clearProductFields();
                        updateTotals();
                    }
                });
            },
            error: function() {
                alert('❌ Error checking stock. Please try again.');
            }
        });
    }
    function updateProductTable() {
        const tbody = document.querySelector('#invoiceProductTable tbody');
        tbody.innerHTML = '';
        if (window.productsArr.length === 0) {
            tbody.innerHTML = '<tr class="text-muted"><td colspan="6">No products added</td></tr>';
            return;
        }
        window.productsArr.forEach((product, index) => {
            // Defensive: ensure price and total are numbers
            const price = typeof product.price === 'number' ? product.price : parseFloat(product.price) || 0;
            const total = typeof product.total === 'number' ? product.total : parseFloat(product.total) || (price * (parseInt(product.qty) || 0));
            const name = product.name || product.product_name || '';
            const qty = product.qty || 0;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${name}</td>
                <td>${qty}</td>
                <td>₹${price.toFixed(2)}</td>
                <td>₹${total.toFixed(2)}</td>
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
    window.removeProduct = function(index) {
        window.productsArr.splice(index, 1);
        updateProductTable();
        updateTotals();
    };
    function clearProductFields() {
        document.getElementById('invoiceProductName').value = '';
        document.getElementById('invoiceProductModel').value = '';
        document.getElementById('invoiceProductQty').value = '';
        document.getElementById('invoiceProductPrice').value = '';
    }
    function updateTotals() {
        // Defensive: ensure all totals are numbers
        const subtotal = window.productsArr.reduce((sum, p) => sum + (typeof p.total === 'number' ? p.total : parseFloat(p.total) || 0), 0);
        const stateId = document.getElementById('state').value;
        let cgst = 0, sgst = 0, igst = 0, tax = 0;
        if (stateId == '35') {
            cgst = window.productsArr.reduce((sum, p) => sum + (((typeof p.total === 'number' ? p.total : parseFloat(p.total) || 0) * ((p.tax_percentage ? p.tax_percentage : 5)/2) / 100)), 0);
            sgst = window.productsArr.reduce((sum, p) => sum + (((typeof p.total === 'number' ? p.total : parseFloat(p.total) || 0) * ((p.tax_percentage ? p.tax_percentage : 5)/2) / 100)), 0);
            igst = 0;
            tax = cgst + sgst;
        } else {
            cgst = 0;
            sgst = 0;
            igst = window.productsArr.reduce((sum, p) => sum + (((typeof p.total === 'number' ? p.total : parseFloat(p.total) || 0) * (p.tax_percentage ? p.tax_percentage : 5) / 100)), 0);
            tax = igst;
        }
        const grandTotal = Math.round(subtotal + tax);
        const cgstEl = document.getElementById('invoiceCGST');
        const sgstEl = document.getElementById('invoiceSGST');
        const igstEl = document.getElementById('invoiceIGST');
        const grandTotalEl = document.getElementById('invoiceGrandTotal');
        const subtotalEl = document.getElementById('invoiceSubtotal');
        if (cgstEl) cgstEl.textContent = cgst.toFixed(2);
        if (sgstEl) sgstEl.textContent = sgst.toFixed(2);
        if (igstEl) igstEl.textContent = igst.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(0);
        if (subtotalEl) subtotalEl.textContent = Number(subtotal).toFixed(2);
        // --- Always update hidden fields for backend ---
        if (document.getElementById('hiddenCGST')) document.getElementById('hiddenCGST').value = cgst.toFixed(2);
        if (document.getElementById('hiddenSGST')) document.getElementById('hiddenSGST').value = sgst.toFixed(2);
        if (document.getElementById('hiddenIGST')) document.getElementById('hiddenIGST').value = igst.toFixed(2);
        if (document.getElementById('hiddenGrandTotal')) document.getElementById('hiddenGrandTotal').value = grandTotal.toFixed(0);
    }
    </script>
@endsection
