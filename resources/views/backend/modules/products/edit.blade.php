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
            <div class="card shadow-sm rounded-4 mt-4"> 
                <div class="card-body">
                <form id="productForm" method="PUT" enctype="multipart/form-data">
                    @csrf
                   
                    <div class="row">     
                        <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                            Product updated successfully.                         
                        </div>                   
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select id="category_id" name="category_id" class="form-select">
                                        <option value="">Select Category</option>
                                        @foreach ($category as $key => $categoryVal)
                                            <option value="{{ $key }}" {{$product->category_id == $key ? 'selected' : '' }}>{{ $categoryVal }}</option>
                                        @endforeach                                       
                                    </select>
                                    <div class="invalid-feedback" id="category_id-error"></div>
                                </div>
                                <input type="hidden" class="form-control" id="id" name="id" value="{{ $product->id }}">
                                <div class="col-md-6">
                                    <label for="brand_id" class="form-label">Brand</label>
                                    <select id="brand_id" name="brand_id" class="form-select">
                                        <option value="">Select Brand</option>  
                                        @foreach ($brand as $key => $brandVal)
                                            <option value="{{ $key }}" {{$product->brand_id == $key ? 'selected' : '' }}>{{ $brandVal }}</option>
                                        @endforeach                                     
                                    </select>
                                    <div class="invalid-feedback" id="brand_id-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control" name="model" id="model" placeholder="Enter Model" value="{{ $product->model }}"/>
                                    <div class="invalid-feedback" id="model-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Series</label>
                                    <input type="text" class="form-control" name="series" id="series" placeholder="Enter Series" value="{{ $product->series }}" />
                                    <div class="invalid-feedback" id="series-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Processor</label>
                                    <input type="text" class="form-control" name="processor" id="processor" placeholder="Enter Processor" value="{{ $product->processor }}" />
                                    <div class="invalid-feedback" id="processor-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Memory</label>
                                    <input type="text" class="form-control" name="memory" id="memory" placeholder="Enter Memory" value="{{ $product->memory }}" />
                                    <div class="invalid-feedback" id="memory-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Operating System</label>
                                    <input type="text" class="form-control" name="operating_system" id="operating_system"  placeholder="Enter OS" value="{{ $product->operating_system }}" />
                                    <div class="invalid-feedback" id="operating_system-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control" name="price" id="price" placeholder="₹ 0.00"  value="{{ $product->price }}"/>
                                    <div class="invalid-feedback" id="price-error"></div>
                                </div>
                                <div class="col-md-6">
                                    
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-5">
                            <label for="product_images" class="form-label w-100 border rounded py-5 bg-light d-flex flex-column align-items-center justify-content-center text-center">
                                <i class="fas fa-upload fa-2x text-primary mb-2"></i>
                                <p class="mb-0">Click to upload product images</p>
                                <input type="file" id="product_images" name="product_images" class="form-control d-none" accept="image/*" />
                                <div class="invalid-feedback" style="margin-left: 200px;" id="product_images-error"></div>
                            </label>
                            {{-- @if($product->product_images != '')    
                                <p>Previous Image</p>                            
                                <img src="{{ asset('assets/uploads/'.$product->product_images) }}" class="img-fluid rounded shadow-sm mb-2" alt="Product Image" style="max-width: 100%; height: auto;">
                            @else
                                &nbsp;
                            @endif --}}
                            <div class="row mt-3 g-2" id="preview"></div>
                        </div>
                        <div class="mt-4 d-flex align-items center">                        
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>                    
                </form>
            </div>
        </div>  
             
    </main>
     
    <script>
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
                        showAlert()
                       setTimeout(function() {
                        window.location.href = "{{ route('products.index')}}";
                        }, 3000);

                    setTimeout(function() {
                        document.querySelector('#successAlert').remove();
                    }, 32000);

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