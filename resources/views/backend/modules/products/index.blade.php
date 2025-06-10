@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        
        
        <div style="padding-top: 30px;"></div>
        <div style="padding-top: 30px;"></div>
            <div class="container-fluid px-3">
                <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                  <div class="col-md-6">
                            @include('backend.include.formError')
                            @if(Session::has('create_product'))
                                <div class="alert alert-success col-md-12">
                                    <strong>{{session('create_product')}}</strong>
                                </div>
                            @endif
                            @if(Session::has('delete_product'))
                                <div class="alert alert-danger col-md-12">
                                    <strong>{{session('delete_product')}}</strong>
                                </div>
                            @endif
                            @if(Session::has('edit_product'))
                                <div class="alert alert-warning col-md-12">
                                    <strong>{{session('edit_product')}}</strong>
                                </div>
                            @endif
                        </div>
                    <div class="d-flex justify-content-end gap-2 mb-3">
                      
                        <a href="{{ url('/products/create') }}" class="btn btn-primary">Add Product</a>
                        {{-- <a href="./Bulk-upload/Bulk-upload.html" class="btn btn-secondary">Bulk Upload</a> --}}
                    </div>

                    <!-- Filters -->
                    {{-- <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search product..." style="max-width: 180px;">
                    <select id="brandFilter" class="form-select" style="max-width: 180px;">
                        <option value="">All Brands</option>
                        <option value="Vivobook">Vivobook</option>
                        <option value="Lenova">Lenova</option>
                        <option value="Asus">Asus</option>
                    </select>
                    </div> --}}

                    <!-- Table -->
                    <div class="table-responsive" id="responsive-table">
                     <table id="customerTable" class="table table-bordered table-hover align-middle">
                        <thead class="custom-thead text-center">
                        <tr>
                            <th>S.No</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Price</th>
                            {{-- <th>Stock</th> --}}
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="productTableBody">
                            @include('backend.modules.products.partials.product_rows', ['products' => $products])
                        </tbody>
                </table>        
            </div>
        </div>


        <!-- Modal -->
        {{-- <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockModalLabel">Warehouse Stock Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-hover align-middle" id="productTable">
                            <thead class="custom-thead text-center">
                            <tr>
                                <th>Warehouse</th>
                                <th>Stock</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr><td>WH1</td><td>2</td></tr>
                            <tr><td>WH2</td><td>3</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> --}}


        </div> 

    </div>

    <script>
        function openModal() {
            const modal = new bootstrap.Modal(document.getElementById('stockModal'));
            alert("Stock details will be shown here.");
            modal.show();
        }

        let page = 1;
        let loading = false;
        let lastPage = false;

        function loadMoreProducts() {
            if (loading || lastPage) return;
            loading = true;
            page++;
            $.ajax({
                url: '?page=' + page,
                type: 'GET',
                success: function(data) {
                    if (data.trim() === '') {
                        lastPage = true;
                    } else {
                        $('#productTableBody').append(data);
                    }
                    loading = false;
                },
                error: function() {
                    loading = false;
                }
            });
        }

        $(window).on('scroll', function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
                loadMoreProducts();
            }
        });
    </script>
      
    
@endsection