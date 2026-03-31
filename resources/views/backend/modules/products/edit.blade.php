@extends('layouts.backend')

@section('content')

    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="col-md-6">
            @include('backend.include.formError')
            @if(Session::has('create_customer'))
                <div class="alert alert-success col-md-12">
                    <strong>{{session('create_customer')}}</strong>
                </div>
            @endif
            @if(Session::has('delete_customer'))
                <div class="alert alert-danger col-md-12">
                    <strong>{{session('delete_customer')}}</strong>
                </div>
            @endif
            @if(Session::has('edit_customer'))
                <div class="alert alert-warning col-md-12">
                    <strong>{{session('edit_customer')}}</strong>
                </div>
            @endif
        </div>
        
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="card border-0 shadow-sm rounded-4 mt-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-edit text-primary me-2"></i>Edit Product</h5>
                    <p class="text-muted small">Update the product information below.</p>
                </div>
                <div class="card-body p-4">
                    <form id="productForm" method="PUT" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Left Column: Form Fields -->
                            <div class="col-lg-8">
                                <input type="hidden" id="id" name="id" value="{{ $product->id }}">
                                
                                <!-- Section 1: Basic Information -->
                                <div class="bg-light p-4 rounded-4 mb-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="fas fa-info-circle text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0">Basic Information</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                                            <select id="category_id" name="category_id" class="form-select border-0 shadow-sm py-2 px-3 rounded-3" required>
                                                <option value="">Select Category</option>
                                                @foreach ($category as $key => $categoryVal)
                                                    <option value="{{ $key }}" {{$product->category_id == $key ? 'selected' : '' }}>{{ $categoryVal }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="category_id-error"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="brand_id" class="form-label fw-semibold small">Brand <span class="text-danger">*</span></label>
                                            <select id="brand_id" name="brand_id" class="form-select border-0 shadow-sm py-2 px-3 rounded-3" required>
                                                <option value="">Select Brand</option>
                                                @foreach ($brand as $key => $brandVal)
                                                    <option value="{{ $key }}" {{$product->brand_id == $key ? 'selected' : '' }}>{{ $brandVal }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="brand_id-error"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="model" class="form-label fw-semibold small">Model <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="model" id="model" placeholder="Enter Model" value="{{ $product->model }}" required />
                                            <div class="invalid-feedback" id="model-error"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="model_no" class="form-label fw-semibold small">Model No <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="model_no" id="model_no" placeholder="Enter Model No" value="{{ $product->model_no }}" required />
                                            <div class="invalid-feedback" id="model_no-error"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="capacity" class="form-label fw-semibold small">Capacity (AH)</label>
                                            <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="capacity" id="capacity" placeholder="e.g. 40AH, 60-70AH" value="{{ $product->capacity }}" />
                                            <div class="invalid-feedback" id="capacity-error"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="specification" class="form-label fw-semibold small">Specification</label>
                                            <textarea class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="specification" id="specification" rows="2" placeholder="Dimensions, Weight, Terminal Type...">{{ $product->specification }}</textarea>
                                            <div class="invalid-feedback" id="specification-error"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="remarks" class="form-label fw-semibold small">Remarks</label>
                                            <textarea class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="remarks" id="remarks" rows="2" placeholder="Additional notes...">{{ $product->remarks }}</textarea>
                                            <div class="invalid-feedback" id="remarks-error"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Warranty & Pricing -->
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="bg-light p-4 rounded-4 h-100">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                                                    <i class="fas fa-shield-alt text-warning"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0">Warranty Details</h6>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label for="series" class="form-label fw-semibold small">Total Warranty</label>
                                                    <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="series" id="series" placeholder="e.g. 48M, 60M" value="{{ $product->series }}" />
                                                    <div class="invalid-feedback" id="series-error"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="foc_months" class="form-label fw-semibold small">FOC Months</label>
                                                    <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="foc_months" id="foc_months" placeholder="e.g. 24, 24M" value="{{ $product->foc_months }}" />
                                                    <div class="invalid-feedback" id="foc_months-error"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="prorata_months" class="form-label fw-semibold small">Pro-rata Months</label>
                                                    <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="prorata_months" id="prorata_months" placeholder="e.g. 12, 12M" value="{{ $product->prorata_months }}" />
                                                    <div class="invalid-feedback" id="prorata_months-error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light p-4 rounded-4 h-100">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                                                    <i class="fas fa-tags text-success"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0">Pricing & Tax</h6>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="price" class="form-label fw-semibold small">Base Price (₹)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text border-0 bg-white small fw-bold text-muted px-2">₹</span>
                                                        <input type="number" class="form-control border-0 shadow-sm py-2 pe-3 rounded-end-3" name="price" id="price" placeholder="0.00" value="{{ $product->price }}" />
                                                    </div>
                                                    <div class="invalid-feedback" id="price-error"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="offer_price" class="form-label fw-semibold small">Offer Price (₹)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text border-0 bg-white small fw-bold text-muted px-2">₹</span>
                                                        <input type="number" step="0.01" class="form-control border-0 shadow-sm py-2 pe-3 rounded-end-3" name="offer_price" id="offer_price" placeholder="0.00" value="{{ $product->offer_price }}" />
                                                    </div>
                                                    <div class="invalid-feedback" id="offer_price-error"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="tax_percentage" class="form-label fw-semibold small">GST %</label>
                                                    <select class="form-select border-0 shadow-sm py-2 px-3 rounded-3" name="tax_percentage" id="tax_percentage">
                                                        <option value="">Select Tax</option>
                                                        @foreach($gstRates as $gst)
                                                            <option value="{{ $gst->name }}" {{ $product->tax_percentage == $gst->name ? 'selected' : '' }}>{{ $gst->name }}%</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback" id="tax_percentage-error"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="hsn_code" class="form-label fw-semibold small">HSN Code</label>
                                                    <input type="text" class="form-control border-0 shadow-sm py-2 px-3 rounded-3" name="hsn_code" id="hsn_code" placeholder="Enter HSN" value="{{ $product->hsn_code }}" />
                                                    <div class="invalid-feedback" id="hsn_code-error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Image Upload -->
                            <div class="col-lg-4">
                                <div class="bg-light p-4 rounded-4 h-100 position-sticky" style="top: 20px;">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="fas fa-image text-danger"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0">Product Image</h6>
                                    </div>
                                    
                                    <label for="product_images" class="product-upload-area border-2 border-dashed border-primary border-opacity-25 rounded-4 p-5 d-flex flex-column align-items-center justify-content-center text-center cursor-pointer mb-3 transition-all hover-shadow" style="background-color: rgba(var(--bs-primary-rgb), 0.02);">
                                        <div class="upload-icon bg-primary bg-opacity-10 p-4 rounded-circle mb-3">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Update image</h6>
                                        <p class="text-muted small mb-0">Leave empty to keep current</p>
                                        <input type="file" id="product_images" name="product_images" class="d-none" accept="image/*" />
                                    </label>
                                    <div class="invalid-feedback d-block mb-3 text-center" id="product_images-error"></div>

                                    <!-- Preview Area -->
                                    <div id="preview" class="row g-2 mt-4">
                                        @if($product->product_images)
                                            <div class="col-12 text-center p-2 border rounded-4 bg-white shadow-sm">
                                                <p class="text-muted small mb-2 fw-bold">Current Image</p>
                                                <img src="{{ asset('assets/uploads/'.$product->product_images) }}" class="img-fluid rounded-3 shadow-sm mb-2" alt="Product Image" style="max-height: 200px;">
                                            </div>
                                        @else
                                            <div class="col-12 text-center p-4 border rounded-4 border-dashed">
                                                <p class="text-muted small mb-0 italic">No image uploaded yet</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-auto pt-5">
                                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 shadow-sm fw-bold d-flex align-items-center justify-content-center">
                                            <i class="fas fa-save me-2"></i> Update Product
                                        </button>
                                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 py-2 rounded-3 mt-2 fw-semibold">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Custom Styling for Hover Effects -->
        <style>
            .cursor-pointer { cursor: pointer; }
            .transition-all { transition: all 0.3s ease; }
            .hover-shadow:hover { 
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
                border-color: var(--bs-primary) !important;
                background-color: rgba(var(--bs-primary-rgb), 0.04) !important;
            }
            .product-upload-area:hover .upload-icon {
                transform: scale(1.1);
                transition: transform 0.3s ease;
            }
            .bg-light { background-color: #f8fafc !important; }
            .form-control:focus, .form-select:focus {
                border-color: var(--bs-primary);
                box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
            }
        </style>
             
    </main>
     
    <script>
        const id = "{{ $product->id }}";
        //for uploading image in add products page
        const uploadInput = document.getElementById("product_images");
        const preview = document.getElementById("preview");

        uploadInput.addEventListener("change", (event) => {
        const file = event.target.files[0]; // Get only the first selected file
        if (!file) return;

        preview.innerHTML = ""; // Clear any previous preview

        const reader = new FileReader();
        reader.onload = () => {
            const col = document.createElement("div");
            col.className = "col-12";

            const img = document.createElement("img");
            img.src = reader.result;
            img.className = "img-fluid rounded shadow-sm mb-2";
            img.alt = file.name;

            col.appendChild(img);
            preview.appendChild(col);
        };

        reader.readAsDataURL(file);
        });


        function addField() {
        const container = document.getElementById('product-warehouseStockFields');
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'warehouse-entry');

        newRow.innerHTML = `
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-6">
                            <select class="form-select" name="warehouse[]">
                                <option value="">-- Select Warehouse --</option>
                                <option value="Warehouse A">Warehouse A</option>
                                <option value="Warehouse B">Warehouse B</option>
                                <option value="Warehouse C">Warehouse C</option>
                            </select>
                            </div>
                            <div class="col-md-4">
                            <input type="number" class="form-control" placeholder="Stock" name="stock[]">
                            </div>
                            <div class="col-md-2 d-flex">
                            <button type="button" class="btn btn-success btn-sm me-1" onclick="addField()">
                                <i class="fas fa-plus"></i>
                            </button>

        <!-- Remove Button -->
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.row').remove()">
                                <i class="fas fa-minus"></i>
                                </button>
                                </div>
        `;

        container.appendChild(newRow);
        }

        $(document).ready(function() {
            fetchCategories();
            fetchBrands();

            // Poll for changes every POLL_INTERVAL ms
            setInterval(function() {
                fetchCategories();
                fetchBrands();
            }, POLL_INTERVAL);

            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                var file = $('#product_images')[0].files[0];                
                if (file) {
                    formData.delete("product_images");
                    formData.append('product_images', file);
                }else{
                   formData.delete("product_images");
                   formData.append('product_images', '');
                }

                       
                formData.append('_method', 'PUT');
                let output = '';
                for (let [key, value] of formData.entries()) {
                    output += `${key}: ${value}\n`;
                }       
                console.log(output); // For debugging purposes
                // Clear previous errors
                $('.invalid-feedback').text('');
                $('.form-control, .form-select').removeClass('is-invalid');
                $.ajax({
                    url: "/products/" + id,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = "{{ route('products.index')}}";
                        });

                        $('#productForm').trigger("reset");
                        preview.innerHTML = "";
                    },
                    error: function(xhr) {
                        
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key + '-error').text(value[0]);                               
                                $('[name="' + key + '"]').addClass('is-invalid');
                                //alert(key);
                            });
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });
            });
        });

        function showAlert() {
            const alertBox = document.getElementById("successAlert");
            alertBox.classList.remove("d-none"); // show the alert
        }

        const POLL_INTERVAL = 10000;

        function fetchCategories() {
            $.get('/api/categories', function(data) {
                let $category = $('#category_id');
                const currentVal = $category.val();
                $category.empty().append('<option value="">Select Category</option>');
                $.each(data, function(i, item) {
                    $category.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                // Restore previous selection if still available
                if (currentVal && $category.find('option[value="' + currentVal + '"]').length) {
                    $category.val(currentVal);
                }
            });
        }

        function fetchBrands() {
            $.get('/api/brands', function(data) {
                let $brand = $('#brand_id');
                const currentVal = $brand.val();
                $brand.empty().append('<option value="">Select Brand</option>');
                $.each(data, function(i, item) {
                    $brand.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                // Restore previous selection if still available
                if (currentVal && $brand.find('option[value="' + currentVal + '"]').length) {
                    $brand.val(currentVal);
                }
            });
        }

        function fetchWarehouse() {
            $.get('/api/warehouse', function(data) {
                let $brand = $('#brand_id');
                const currentVal = $brand.val();
                $brand.empty().append('<option value="">Select Brand</option>');
                $.each(data, function(i, item) {
                    $brand.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                // Restore previous selection if still available
                if (currentVal && $brand.find('option[value="' + currentVal + '"]').length) {
                    $brand.val(currentVal);
                }
            });
        }
        
    </script>  
@endsection