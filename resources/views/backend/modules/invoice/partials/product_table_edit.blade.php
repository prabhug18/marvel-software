<div class="w-100 text-start ps-3">
    <h4 class="mt-5 mb-3">Edit Products</h4>
</div>
<!-- Add Product Form (single row, horizontal like create page) -->
<div class="row g-3 mb-4 align-items-end" id="editProductFormRow">
    <div class="col-md-3 position-relative">
        <label for="invoiceProductName" class="form-label">Product Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="invoiceProductName" placeholder="Enter Product Name" autocomplete="off" />
        <div id="productSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
    </div>
    <div class="col-md-3">
        <label for="invoiceProductModel" class="form-label">Model <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="invoiceProductModel" placeholder="Enter Model" />
    </div>
    <div class="col-md-2">
        <label for="invoiceProductQty" class="form-label">Qty <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="invoiceProductQty" placeholder="Enter Qty" min="1" />
    </div>
    <div class="col-md-2">
        <label for="invoiceProductPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="invoiceProductPrice" placeholder="Enter Unit Price" min="0" step="0.01" />
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
                    <td>{{ $item->product_name }}</td>
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
        // Defensive: ensure all required fields exist
        window.productsArr = window.productsArr.map(function(item) {
            return {
                name: item.product_name ? item.product_name : (item.name || ''),
                model: item.model || '',
                qty: item.qty || 1,
                price: item.unit_price !== undefined ? Number(item.unit_price) : (item.price !== undefined ? Number(item.price) : 0),
                total: item.total !== undefined ? Number(item.total) : ((item.qty && (item.unit_price !== undefined ? Number(item.unit_price) : Number(item.price))) ? Number(item.qty) * (item.unit_price !== undefined ? Number(item.unit_price) : Number(item.price)) : 0),
                tax_percentage: item.tax_percentage !== undefined ? Number(item.tax_percentage) : 5
            };
        });
        setTimeout(function() { updateProductTable(); updateTotals(); }, 0);
    }
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
});
window.productsArr = window.productsArr || [];
window.addEventListener("DOMContentLoaded", () => {
    const addProductBtn = document.getElementById('invoiceAddProductBtn');
    if (addProductBtn) addProductBtn.addEventListener('click', addProduct);
    if (typeof updateProductTable === 'function') {
        updateProductTable();
        updateTotals();
    }
});
function addProduct() {
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
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${product.name}</td>
            <td>${product.qty}</td>
            <td>₹${product.price.toFixed(2)}</td>
            <td>₹${product.total.toFixed(2)}</td>
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
    const subtotal = window.productsArr.reduce((sum, p) => sum + p.total, 0);
    const stateId = document.getElementById('state') ? document.getElementById('state').value : null;
    let cgst = 0, sgst = 0, igst = 0, tax = 0;
    if (stateId == '35') {
        cgst = window.productsArr.reduce((sum, p) => sum + ((p.total * ((p.tax_percentage ? p.tax_percentage : 5)/2) / 100)), 0);
        sgst = window.productsArr.reduce((sum, p) => sum + ((p.total * ((p.tax_percentage ? p.tax_percentage : 5)/2) / 100)), 0);
        igst = 0;
        tax = cgst + sgst;
    } else {
        cgst = 0;
        sgst = 0;
        igst = window.productsArr.reduce((sum, p) => sum + ((p.total * (p.tax_percentage ? p.tax_percentage : 5) / 100)), 0);
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
    if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
}
</script>
