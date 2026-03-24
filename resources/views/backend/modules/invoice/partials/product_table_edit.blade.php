<!-- Add Product Form (single row, horizontal like create page) -->
<div class="row g-3 mb-4 align-items-end" id="editProductFormRow">
    <div class="col-md-2 position-relative">
        <label for="invoiceProductName" class="form-label">Product Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="invoiceProductName" placeholder="Enter Product Name" autocomplete="off" />
        <div id="productSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
    </div>
    <div class="col-md-2">
        <label for="invoiceProductModel" class="form-label">Model <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="invoiceProductModel" placeholder="Enter Model" />
    </div>
    <div class="col-md-2">
        <label for="invoiceProductSerialNo" class="form-label">Serial Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="invoiceProductSerialNo" placeholder="Enter Serial Number" />
    </div>
    <div class="col-md-2">
        <label for="invoiceProductQty" class="form-label">Qty <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="invoiceProductQty" placeholder="Enter Qty" min="1" />
        <div id="origPriceLabel" class="form-text text-secondary mt-1" style="display:none; font-weight:600; margin-top:6px;">&nbsp;</div>
    </div>
    <div class="col-md-2">
        <label for="invoiceProductPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
        <div style="position:relative;">
            <input type="number" class="form-control" id="invoiceProductPrice" placeholder="Enter Unit Price" min="0" step="0.01" />
            <div id="gstInclusivePriceLabel" class="form-text text-primary mt-1" style="display:none; font-weight:bold; min-height:22px; position:absolute; left:0; right:0; bottom:-28px; z-index:2;"></div>
        </div>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn custom-orange-btn text-white w-100" id="invoiceAddProductBtn">Add Product</button>
    </div>
</div>
<form id="editProductForm" style="display:none;"></form>
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
            @forelse($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name ?? $item->product_name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                    <td>₹{{ number_format($item->total, 2) }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-product-btn" data-index="{{ $index }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="text-muted">
                    <td colspan="6">No products added</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
// --- Product Autosuggestion, Add/Edit/Remove, and Totals Logic (copied from create page) ---
$(document).ready(function() {
    // Pre-fill productsArr from window.productsArr if available (set in edit.blade.php)
    if (window.productsArr && Array.isArray(window.productsArr) && window.productsArr.length > 0) {
        // Defensive: ensure all required fields exist, including serial_no.
        // Preserve any gst_inclusive_price/offer_price if present so unit price can show inclusive value.
        window.productsArr = window.productsArr.map(function(item) {
            const gstIncl = item.gst_inclusive_price !== undefined ? Number(item.gst_inclusive_price) : (item.offer_price !== undefined ? Number(item.offer_price) : null);
            const unitPrice = item.unit_price !== undefined ? Number(item.unit_price) : (item.price !== undefined ? Number(item.price) : 0);
            return {
                name: item.product_name ? item.product_name : (item.name || ''),
                model: item.model || '',
                product_id: item.product_id || item.productId || item.id || null,
                serial_no: item.serial_no || item.serialNo || '',
                qty: item.qty || 1,
                price: unitPrice,
                gst_inclusive_price: gstIncl,
                total: item.total !== undefined ? Number(item.total) : ((item.qty && (unitPrice)) ? Number(item.qty) * unitPrice : 0),
                tax_percentage: item.tax_percentage !== undefined ? Number(item.tax_percentage) : 5
            };
        });
        setTimeout(function() { updateProductTable(); updateTotals(); }, 0);
    }
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
                            if (p.stock !== undefined && Number(p.stock) <= 0) return;
                            const key = (p.brand||'')+'|'+(p.series||'')+'|'+(p.model||'');
                            if (seen.has(key)) return;
                            seen.add(key);
                            const display = [p.brand, p.series, p.model].filter(Boolean).join(' - ') + (p.category ? ' ('+p.category+')' : '');
                            // prefer offer_price if provided
                            let suggestionPrice = (p.offer_price !== undefined && p.offer_price !== null && p.offer_price !== '') ? p.offer_price : p.price;
                            $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-id="'+(p.id||'')+'" data-brand="'+(p.brand||'')+'" data-series="'+(p.series||'')+'" data-model="'+(p.model||'')+'" data-category="'+(p.category||'')+'" data-price="'+(suggestionPrice||'')+'" data-orig-price="'+(p.price||'')+'" data-tax_percentage="'+(p.tax_percentage||'')+'">'+display+'</button>');
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
        const $btn  = $(this);
        const productIdSelected = $btn.data('id') || null;
        const brand = $btn.data('brand') || '';
        const series = $btn.data('series') || '';
        const model = $btn.data('model') || '';
        const gst_inclusive_price = parseFloat($btn.data('price')) || 0;
        // Read tax_percentage from button data, fallback to 0
        let gst_percentage = 0;
        if ($btn.data('tax_percentage') !== undefined && $btn.data('tax_percentage') !== '') {
            gst_percentage = parseFloat($btn.data('tax_percentage')) || 0;
        }
        // Always reverse-calculate GST-exclusive price
        let gst_exclusive_price = gst_inclusive_price;
        if (gst_percentage > 0) {
            gst_exclusive_price = gst_inclusive_price / (1 + gst_percentage / 100);
    
            $('#invoiceProductPrice').data('tax-percentage', gst_percentage);
            // store selected product id on model input for later
            $('#invoiceProductModel').data('product-id', productIdSelected);
            $('#productSuggestions').hide();
        $('#invoiceProductModel').val(model);
        // Set GST-exclusive price in textbox, but also update hidden field and label
        $('#invoiceProductPrice').val(gst_exclusive_price ? gst_exclusive_price.toFixed(2) : '');
        if ($('#invoiceProductPriceGstIncl').length === 0) {
            $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
        }
        $('#invoiceProductPriceGstIncl').val(gst_inclusive_price.toFixed(2));
        $('#invoiceProductPrice').data('gst-inclusive', gst_inclusive_price.toFixed(2));
        $('#invoiceProductPrice').data('tax-percentage', gst_percentage);
        $('#productSuggestions').hide();
        // Show GST-inclusive price label below the input
        if ($('#gstInclusivePriceLabel').length === 0) {
            $('<div id="gstInclusivePriceLabel" class="form-text text-primary mt-1" style="font-weight:bold; min-height:22px; margin-top:6px;"></div>').insertAfter('#invoiceProductPrice');
        }
        $('#gstInclusivePriceLabel').text('GST Inclusive: ₹' + gst_inclusive_price.toFixed(2)).css('display','block');
        // Show Original price under Qty input if available
        var orig_price = parseFloat($btn.data('orig-price')) || null;
        if (orig_price && !isNaN(orig_price)) {
            $('#origPriceLabel').html('Original Price: ₹' + orig_price.toFixed(2)).css({'display':'block'});
        } else {
            $('#origPriceLabel').text('').hide();
        }
        }
    });
    // When user enters price manually, treat as GST-inclusive and reverse-calculate GST-exclusive
    $('#invoiceProductPrice').on('input', function() {
        let entered_price = parseFloat($(this).val()) || 0;
        let gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
        // Always treat entered value as GST-inclusive, and reverse-calculate GST-exclusive
        let gst_exclusive_price = entered_price;
        if (gst_percentage > 0) {
            gst_exclusive_price = entered_price / (1 + gst_percentage / 100);
        }
        // Do NOT update the textbox value, just calculate and store GST-exclusive price
        // Store GST-inclusive price in hidden field
        if ($('#invoiceProductPriceGstIncl').length === 0) {
            $('<input>').attr({type: 'hidden', id: 'invoiceProductPriceGstIncl'}).appendTo('body');
        }
        $('#invoiceProductPriceGstIncl').val(entered_price);
        $(this).data('gst-exclusive', gst_exclusive_price);
        // Show GST-inclusive price label
        if ($('#gstInclusivePriceLabel').length === 0) {
            $('<div id="gstInclusivePriceLabel" class="form-text text-primary mt-1" style="font-weight:bold; min-height:22px; margin-top:6px;"></div>').insertAfter('#invoiceProductPrice');
        }
        $('#gstInclusivePriceLabel').text('GST Inclusive: ₹' + entered_price.toFixed(2)).css('display','block');
        // --- Update preview total if qty is filled ---
        let qty = parseInt($('#invoiceProductQty').val()) || 0;
        let total = entered_price * qty;
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#invoiceProductName, #productSuggestions').length) {
            $('#productSuggestions').hide();
        }
    });
});
window.productsArr = window.productsArr || [];
window.addEventListener("DOMContentLoaded", () => {
    // Use jQuery to ensure handler is attached after DOM is ready
    $(document).on('click', '#invoiceAddProductBtn', function(e) {
        e.preventDefault();
        addProduct();
    });
    if (typeof updateProductTable === 'function') {
        updateProductTable();
        updateTotals();
    }
});
function addProduct() {
    const name = document.getElementById('invoiceProductName').value.trim();
    const model = document.getElementById('invoiceProductModel').value.trim();
    const serial_no = document.getElementById('invoiceProductSerialNo').value.trim();
    const qty = parseInt(document.getElementById('invoiceProductQty').value);
    let gst_percentage = 0;
    if ($('#invoiceProductPrice').data('tax-percentage') !== undefined) {
        gst_percentage = parseFloat($('#invoiceProductPrice').data('tax-percentage')) || 0;
    }
    if (!gst_percentage && $('#invoiceProductPriceGstIncl').length > 0) {
        gst_percentage = parseFloat($('#invoiceProductPriceGstIncl').data('tax-percentage')) || 0;
    }
    // Determine if user manually changed the price after auto-suggestion
    let gst_inclusive_price;
    let base_price;
    // Always use the GST-inclusive price from the hidden field if it exists and is valid
    if (
        $('#invoiceProductPriceGstIncl').length > 0 &&
        $('#invoiceProductPriceGstIncl').val() &&
        !isNaN(parseFloat($('#invoiceProductPriceGstIncl').val()))
    ) {
        gst_inclusive_price = parseFloat($('#invoiceProductPriceGstIncl').val());
        base_price = gst_inclusive_price;
        if (gst_percentage > 0) {
            base_price = gst_inclusive_price / (1 + gst_percentage / 100);
        }
    } else {
        // Manual entry: treat as GST-inclusive and reverse-calculate
        gst_inclusive_price = parseFloat(document.getElementById('invoiceProductPrice').value);
        base_price = gst_inclusive_price;
        if (gst_percentage > 0) {
            base_price = gst_inclusive_price / (1 + gst_percentage / 100);
        }
    }
    const warehouseId = $('#warehouse_id').val();
    if (!name || !model || !serial_no || isNaN(qty) || qty <= 0 || isNaN(gst_inclusive_price) || gst_inclusive_price < 0) {
        Swal.fire({ icon: 'error', title: 'Invalid Details', text: 'Please enter valid product details. Serial Number is required.' });
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
                Swal.fire({ icon: 'error', title: 'Error', text: 'Unable to check stock. Please try again.' });
                return;
            }
            if (availableStock !== null && qty > availableStock) {
                Swal.fire({ icon: 'warning', title: 'Warning', text: 'Not enough stock available. This will result in negative stock.' });
                // Allow negative stock, do not return
            }
            // --- Fetch product tax_percentage from backend (AJAX) ---
            $.ajax({
                url: '/product-search',
                type: 'GET',
                data: { q: model },
                dataType: 'json',
                success: function(products) {
                    let tax_percentage = gst_percentage || 5;
                    if (Array.isArray(products) && products.length > 0) {
                        const found = products.find(p => {
                            return (p.model && p.model.toLowerCase() === model.toLowerCase());
                        });
                        if (found && found.tax_percentage !== undefined && found.tax_percentage !== null && found.tax_percentage !== '') {
                            tax_percentage = parseFloat(found.tax_percentage);
                        }
                    }
                    const productFullName = `${name}`;
                    // Total should use the GST-inclusive price entered manually
                    let total_inclusive = gst_inclusive_price * qty;
                    // prefer explicit selected id from UI if available
                    const selectedProductId = $('#invoiceProductModel').data('product-id') || null;
                    window.productsArr.push({
                        name: productFullName,
                        product_name: productFullName,
                        model,
                        product_id: selectedProductId,
                        serial_no,
                        qty,
                        price: base_price, // GST-exclusive for table
                        gst_inclusive_price: gst_inclusive_price,
                        total: total_inclusive,
                        tax_percentage
                    });
                    updateProductTable();
                    clearProductFields();
                    updateTotals();
                    // Hide GST-inclusive and original price labels after adding product
                    $('#gstInclusivePriceLabel').text('').hide();
                    $('#origPriceLabel').text('').hide();
                },
                error: function() {
                    const productFullName = `${name} - ${model}`;
                    let total_inclusive = gst_inclusive_price * qty;
                    let used_tax = gst_percentage || 5;
                    const selectedProductIdFallback = $('#invoiceProductModel').data('product-id') || null;
                    window.productsArr.push({
                        name: productFullName,
                        product_name: productFullName,
                        model,
                        product_id: selectedProductIdFallback,
                        serial_no,
                        qty,
                        price: base_price,
                        gst_inclusive_price: gst_inclusive_price,
                        total: total_inclusive,
                        tax_percentage: used_tax
                    });
                    updateProductTable();
                    clearProductFields();
                    updateTotals();
                    // Hide GST-inclusive and original price labels after adding product
                    $('#gstInclusivePriceLabel').text('').hide();
                    $('#origPriceLabel').text('').hide();
                }
            });
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error checking stock. Please try again.' });
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
        const unitPriceDisplay = (product.gst_inclusive_price !== undefined && product.gst_inclusive_price !== null) ? Number(product.gst_inclusive_price) : Number(product.price);
        const totalDisplay = (product.total !== undefined && product.total !== null) ? Number(product.total) : (Number(product.qty || 0) * unitPriceDisplay);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${product.name}</td>
            <td>${product.qty}</td>
            <td>₹${unitPriceDisplay.toFixed(2)}</td>
            <td>₹${totalDisplay.toFixed(2)}</td>
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
    document.getElementById('invoiceProductSerialNo').value = '';
    document.getElementById('invoiceProductQty').value = '';
    document.getElementById('invoiceProductPrice').value = '';
}
function updateTotals() {
    // Subtotal: sum of GST-exclusive prices × qty
    const subtotal = window.productsArr.reduce((sum, p) => sum + (p.price * p.qty), 0);
    const stateId = document.getElementById('state') ? document.getElementById('state').value : null;
    let cgst = 0, sgst = 0, igst = 0, tax = 0;
    if (stateId == '35') {
        cgst = window.productsArr.reduce((sum, p) => {
            let gst_amt = 0;
            if (p.tax_percentage > 0) {
                gst_amt = (p.price * p.qty) * (p.tax_percentage / 100);
            }
            return sum + (gst_amt / 2);
        }, 0);
        sgst = window.productsArr.reduce((sum, p) => {
            let gst_amt = 0;
            if (p.tax_percentage > 0) {
                gst_amt = (p.price * p.qty) * (p.tax_percentage / 100);
            }
            return sum + (gst_amt / 2);
        }, 0);
        igst = 0;
        tax = cgst + sgst;
    } else {
        cgst = 0;
        sgst = 0;
        igst = window.productsArr.reduce((sum, p) => {
            let gst_amt = 0;
            if (p.tax_percentage > 0) {
                gst_amt = (p.price * p.qty) * (p.tax_percentage / 100);
            }
            return sum + gst_amt;
        }, 0);
        tax = igst;
    }
    // Grand Total: subtotal + GST
    const grandTotal = subtotal + tax;
    const cgstEl = document.getElementById('invoiceCGST');
    const sgstEl = document.getElementById('invoiceSGST');
    const igstEl = document.getElementById('invoiceIGST');
    const grandTotalEl = document.getElementById('invoiceGrandTotal');
    const subtotalEl = document.getElementById('invoiceSubtotal');
    if (cgstEl) cgstEl.textContent = cgst.toFixed(2);
    if (sgstEl) sgstEl.textContent = sgst.toFixed(2);
    if (igstEl) igstEl.textContent = igst.toFixed(2);
    if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);
    if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
}
</script>
