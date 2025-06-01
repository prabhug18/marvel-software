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
                        <div class="mb-4 col-10">
                            <label for="productSelect" class="form-label">Select Product:</label>
                            <div class="input-group">
                                <input id="product_search" name="product_search" class="form-control" placeholder="Type model, category, or brand" list="productOptions" autocomplete="off">
                                <datalist id="productOptions"></datalist>
                            </div>
                            <input type="hidden" name="product_id" id="product_id">
                        </div>

                        <!-- Warehouse Fields -->
                        <div id="addStockWarehouseFields">
                            <div class="row g-2 align-items-end mb-3 warehouse-entry">
                                <div class="col-md-5">
                                    <label class="form-label">Select Location</label>
                                    <select class="form-select" name="warehouse_id[]" id="warehouse_id[]">
                                        <option value="">-- Select Location --</option>
                                       
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock</label>
                                    <input type="number" class="form-control" placeholder="Stock" name="stock[]" id="stock[]">
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
    
        let warehouseOptions = '';

        // Fetch warehouses from the database and build options
        function loadWarehouses() {
            $.get('/api/warehouses', function(data) {
                warehouseOptions = '<option value="">-- Select Location --</option>';
                data.forEach(function(warehouse) {
                    warehouseOptions += `<option value="${warehouse.id}">${warehouse.name}</option>`;
                });
                // Set options for the first dropdown
                $('#addStockWarehouseFields select.form-select').html(warehouseOptions);
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
                <div class="col-md-5">
                    <label class="form-label">Select Location</label>
                    <select class="form-select" name="warehouse_id[]"  id="warehouse_id[]">
                        ${warehouseOptions}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock</label>
                    <input type="number" class="form-control" placeholder="Stock" name="stock[]" id="stock[]">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick="addField()"><i class="fas fa-plus"></i></button>
                    <button type="button" class="btn btn-danger" onclick="this.closest('.row').remove()"><i class="fas fa-minus"></i></button>
                </div>
            `;
            container.appendChild(newRow);
        } 

        $('#product_search').on('input', function() {
            let query = $(this).val();
            if (query.length < 1) return;

            $.get('/api/products/search', {q: query}, function(data) {
                let options = '';
                data.forEach(function(product) {
                    let label = product.model;
                    if (product.category && product.category.name) {
                        label += ' | ' + product.category.name;
                    }
                    if (product.brand && product.brand.name) {
                        label += ' | ' + product.brand.name;
                    }
                    options += `<option value="${label}" data-id="${product.id}">`;
                });
                $('#productOptions').html(options);
            });
        });

        // Set product_id when a suggestion is selected
        $('#product_search').on('change', function() {
            let inputVal = $(this).val();
            let found = false;
            $('#productOptions option').each(function() {
                if (this.value === inputVal) {
                    $('#product_id').val($(this).data('id'));
                    found = true;
                    return false;
                }
            });
            if (!found) {
                $('#product_id').val('');
            }
        });

    </script>
@endsection