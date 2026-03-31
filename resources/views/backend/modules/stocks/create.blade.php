@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header') 
        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">        
                    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                        Stock created successfully.                         
                    </div>              
                    <form id="stockForm" enctype="multipart/form-data">
                        @csrf
                        <!-- Product Select -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="productSelect" class="form-label">Select Product: <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input id="product_search" name="product_search" class="form-control" placeholder="Type model, category, or brand" list="productOptions" autocomplete="off" required>
                                    <datalist id="productOptions"></datalist>
                                </div>
                                <input type="hidden" name="product_id" id="product_id" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Select Location: <span class="text-danger">*</span></label>
                                <select class="form-select" name="warehouse_id" id="warehouse_id" required>
                                    <option value="">-- Select Location --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Purchased From (Vendor)</label>
                                <input type="text" class="form-control" name="purchased_from" id="vendor_search" placeholder="Search Vendor Name..." list="vendorOptions" autocomplete="off">
                                <datalist id="vendorOptions"></datalist>
                                <input type="hidden" name="vendor_id" id="vendor_id">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Purchase Rate (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="purchase_rate" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Remarks</label>
                                <input type="text" class="form-control" name="remarks" placeholder="Enter Remarks">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Bulk Serial Numbers (Paste multiple serials here)</label>
                                <textarea class="form-control" name="bulk_serials" rows="3" placeholder="Paste serial numbers separated by commas or new lines..."></textarea>
                                <small class="text-muted">Example: SR101, SR102, SR103 (or one per line)</small>
                            </div>
                        </div>

                        <!-- Warehouse Fields -->
                        <div id="addStockWarehouseFields">
                            <div class="row g-2 align-items-end mb-3 warehouse-entry">
                                <div class="col-md-6">
                                    <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Scan or Enter Serial No" name="serial_no[]" required>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="button" class="btn btn-success" onclick="addField()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                       
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        $(document).ready(function() {
            // jQuery Validation
            $('#stockForm').on('submit', function(e) {               
                e.preventDefault(); // Prevent default form submission
               
                    var formData = new FormData(this);
                    // Add CSRF token for Laravel
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: "{{ route('stocks.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            showAlert();
                            setTimeout(function() {
                                document.querySelector('#successAlert').remove();
                            }, 3000);
                            $('#stockForm').trigger("reset");

                            setTimeout(function() {
                                window.location.reload();
                            }, 4000);
                            //location.reload();
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                // Clear previous errors
                                $('.invalid-feedback').text('');
                                $('.form-control, .form-select').removeClass('is-invalid');
                                $.each(errors, function(key, value) {
                                    let field = key.replace(/\.\d+$/, '[]');
                                    $('[name="' + field + '"]').addClass('is-invalid');
                                    // Show error below the first matching field
                                    let errorDiv = $('[name="' + field + '"]').first().siblings('.invalid-feedback');
                                    if (errorDiv.length === 0) {
                                        // If not present, create and insert after field
                                        $('[name="' + field + '"]').first().after('<div class="invalid-feedback">'+value[0]+'</div>');
                                    } else {
                                        errorDiv.text(value[0]);
                                    }
                                });
                            } else {
                                alert('An error occurred. Please try again.');
                            }
                        }
                    });
                    return false; // Prevent default form submit
                    
            });
        });     

        function showAlert() {
            const alertBox = document.getElementById("successAlert");
            alertBox.classList.remove("d-none"); // show the alert
        }
    
        // Fetch warehouses from the database and build options
        function loadWarehouses() {
            $.get('/api/warehouses', function(data) {
                let warehouseOptions = '<option value="">-- Select Location --</option>';
                data.forEach(function(warehouse) {
                    warehouseOptions += `<option value="${warehouse.id}">${warehouse.name}</option>`;
                });
                $('#warehouse_id').html(warehouseOptions);
            });
        }

        // Call on page load
        $(document).ready(function() {
            loadWarehouses();
        });

        function addField() {
            const container = document.getElementById('addStockWarehouseFields');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'g-2', 'align-items-end', 'mb-3', 'warehouse-entry');

            newRow.innerHTML = `
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Scan or Enter Serial No" name="serial_no[]" required>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick="addField()"><i class="fas fa-plus"></i></button>
                    <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()"><i class="fas fa-minus"></i></button>
                </div>
            `;
            container.appendChild(newRow);
        } 

        // State to store current search results
        let currentProducts = [];
        let currentVendors = [];

        // Updated sync function to use local arrays instead of DOM
        function syncHiddenId(searchInput, dataArray, hiddenInputId, datalistId) {
            let inputVal = $(searchInput).val();
            let matchedItem = dataArray.find(item => {
                // Build the same label string for comparison
                if (datalistId === 'productOptions') {
                    let label = item.model;
                    if (item.category && item.category.name) label += ' | ' + item.category.name;
                    if (item.brand && item.brand.name) label += ' | ' + item.brand.name;
                    if (item.model_no) label += ' | ' + item.model_no;
                    return label === inputVal;
                } else {
                    let label = item.name;
                    // Note: for vendors we don't always add mobile to the value attribute, 
                    // check how it's built in the loop below.
                    return label === inputVal;
                }
            });

            if (matchedItem) {
                $(`#${hiddenInputId}`).val(matchedItem.id);
                $(`#${datalistId}`).empty(); // Safe to clear DOM now
            } else if (inputVal === '') {
                $(`#${hiddenInputId}`).val('');
            }
        }

        $('#product_search').on('input', function() {
            let query = $(this).val();
            
            // Check against currently known products
            syncHiddenId(this, currentProducts, 'product_id', 'productOptions');

            if (query.length < 1) return;

            $.get('/api/products/search', {q: query}, function(data) {
                currentProducts = data; // Store results
                let options = '';
                data.forEach(function(product) {
                    let label = product.model;
                    if (product.category && product.category.name) {
                        label += ' | ' + product.category.name;
                    }
                    if (product.brand && product.brand.name) {
                        label += ' | ' + product.brand.name;
                    }
                    if (product.model_no) {
                        label += ' | ' + product.model_no;
                    }
                    options += `<option value="${label}" data-id="${product.id}">`;
                });
                $('#productOptions').html(options);
                
                // Try matching again with newly fetched data
                syncHiddenId('#product_search', currentProducts, 'product_id', 'productOptions');
            });
        });

        $('#product_search').on('change', function() {
            syncHiddenId(this, currentProducts, 'product_id', 'productOptions');
        });

        // --- Vendor Search Logic ---
        $('#vendor_search').on('input', function() {
            let query = $(this).val();
            syncHiddenId(this, currentVendors, 'vendor_id', 'vendorOptions');

            if (query.length < 1) return;

            $.get('/vendor-search', {q: query}, function(data) {
                currentVendors = data; // Store results
                let options = '';
                data.forEach(function(vendor) {
                    // Match the label used in syncHiddenId
                    options += `<option value="${vendor.name}" data-id="${vendor.id}">`;
                });
                $('#vendorOptions').html(options);
                syncHiddenId('#vendor_search', currentVendors, 'vendor_id', 'vendorOptions');
            });
        });

        $('#vendor_search').on('change', function() {
            syncHiddenId(this, currentVendors, 'vendor_id', 'vendorOptions');
        });

    </script>
@endsection