@extends('layouts.backend')
<!-- Add in your layout or before </body> -->

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header')

        <div style="padding-top: 30px;"></div>
            <div class="container-fluid px-3">
                <div class="card shadow-sm rounded-4 mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2 mb-3">
                            <a href="{{ url('/stocks/create') }}" class="btn btn-primary">Add Stock</a>
                            <a href="{{ route('stock.export') }}" class="btn btn-secondary">Bulk Upload</a>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive" id="responsive-table">
                            <table id="customerTable" class="table table-bordered table-hover align-middle">
                                <thead class="custom-thead text-center">
                                <tr>
                                    <th>ID</th>
                                    {{-- <th>Location</th> --}}
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Model</th>                                    
                                    <th>Stock</th>
                                    {{-- <th>Action</th> --}}
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i    =   1; ?>
                                @foreach($stock as $stockVal)
                                <tr>
                                    <td data-title="ID">{{ $i }}</td>
                                    {{-- <td data-title="Location">{{ $stockVal->warehouse->name }}</td> --}}
                                    <td data-title="Category">{{ $stockVal->category->name }}</td>
                                    <td data-title="Brand">{{ $stockVal->brand->name }}</td>
                                    <td data-title="Model">{{ $stockVal->model }}</td>                                    
                                    <td data-title="Stock">
                                        <button class="btn btn-outline-info btn-sm"
                                            onclick="openModal('{{ $stockVal->category_id }}', '{{ $stockVal->brand_id }}', '{{ $stockVal->model }}')">
                                            {{ $stockVal->total_qty }}
                                        </button>
                                    </td>
                                    {{-- <td data-title="Action">
                                        <button class="btn btn-warning btn-sm me-1"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                    </td> --}}
                                </tr>
                                <?php $i++; ?>
                                @endforeach 
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockModalLabel">Location Stock Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-hover align-middle" id="productTable">
                        <thead class="custom-thead text-center">
                            <tr>
                                <th>Location</th>
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
    </div>

    <script>
        function openModal(category_id, brand_id, model) {
            // Clear previous rows
            $('#productTable tbody').html('<tr><td colspan="2">Loading...</td></tr>');

            // Fetch data via AJAX
            $.get('/api/warehouse-stock', {
                category_id: category_id,
                brand_id: brand_id,
                model: model
            }, function(data) {
                let rows = '';
                if (data.length > 0) {
                    data.forEach(function(item) {
                        rows += `<tr><td>${item.warehouse}</td><td>${item.qty}</td></tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="2">No stock found.</td></tr>';
                }
                $('#productTable tbody').html(rows);
            });

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('stockModal'));
            modal.show();
        }
    </script>

@endsection