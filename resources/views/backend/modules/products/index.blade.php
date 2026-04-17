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
                        {{-- Global SweetAlert2 handles session messages --}}
                    </div>
                    <div class="d-flex justify-content-end gap-2 mb-3">                      
                        @if(auth()->user() && auth()->user()->hasRole('Admin'))
                            <a href="{{ url('/products/create') }}" class="btn btn-primary">Add Product</a>                        
                            <a href="{{ route('export.product') }}" class="btn btn-secondary">Bulk Upload</a>
                        @endif
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
                            <th>Model No</th>
                            <th>Warranty</th>
                            <th>Price</th>
                            <th>Offer Price</th>
                            <th>Remarks</th>
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

// Infinite scroll removed as it conflicts with DataTables

        function ConfirmDelete()
        {
            var x = confirm("Are you sure you want to delete?");
            if (x)
                return true;
            else
                return false;
        }
        // Enable Bootstrap 5 tooltips for dynamically rendered buttons
        function initTooltips() {
          document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
          });
        }
        document.addEventListener('DOMContentLoaded', function () {
          setTimeout(initTooltips, 500);
        });
        // Also re-initialize tooltips after AJAX or pagination if needed
    </script>
      
    
@endsection