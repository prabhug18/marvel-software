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
                        @if(auth()->user() && auth()->user()->hasRole('Admin'))
                        <div class="d-flex justify-content-end gap-2 mb-3">
                            <a href="{{ url('/stocks/create') }}" class="btn btn-primary">Add Stock</a>
                            <a href="{{ route('stock.export') }}" class="btn btn-secondary">Bulk Upload</a>
                        </div>
                        @endif

                        <!-- Table -->
                        <div class="table-responsive" id="responsive-table">
                            <table id="customerTable" class="table table-bordered table-hover align-middle">
                                <thead class="custom-thead text-center">
                                <tr>
                                    <th>S.No</th>
                                    {{-- <th>Location</th> --}}
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Model</th>                                    
                                    <th>Model No</th>
                                    <th>Stock</th>
                                    {{-- <th>Action</th> --}}
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i    =   1; ?>
                                @foreach($stock as $stockVal)
                                <tr>
                                    <td data-title="S.No">{{ $i }}</td>
                                    {{-- <td data-title="Location">{{ $stockVal->warehouse->name }}</td> --}}
                                    <td data-title="Category">{{ $stockVal->category->name }}</td>
                                    <td data-title="Brand">{{ $stockVal->brand->name }}</td>
                                    <td data-title="Model">{{ $stockVal->model }}</td>                                    
                                    <td data-title="Model No">{{ $stockVal->model_no }}</td>
                                    <td data-title="Stock">
                                        <button class="btn btn-outline-info btn-sm"
                                            onclick="openModal('{{ $stockVal->category_id }}', '{{ $stockVal->brand_id }}', '{{ $stockVal->model }}', '{{ $stockVal->model_no }}')">
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
                    <div id="stockSuccessMsg" class="alert alert-success" style="display:none;position:absolute;top:10px;left:50%;transform:translateX(-50%);z-index:1051;min-width:200px;text-align:center;" role="alert" aria-live="polite"></div>
                    <table class="table table-bordered table-hover align-middle" id="productTable">
                        <thead class="custom-thead text-center">
                            <tr>
                                <th>Location</th>
                                <th>Stock</th>
                                @if(auth()->user() && auth()->user()->hasRole('Admin'))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate rows -->
                        </tbody>
                    </table>
                </div>            
            </div>
        </div>
    </div>

    <script>
        // Global variables to store current product context
        var currentCategoryId = null;
        var currentBrandId = null;
        var currentModel = null;
        var currentModelNo = null;

        function openModal(category_id, brand_id, model, model_no) {
            // Store current product identifiers globally
            currentCategoryId = category_id;
            currentBrandId = brand_id;
            currentModel = model;
            currentModelNo = model_no;

            // Clear previous rows
            $('#productTable tbody').html('<tr><td colspan="3">Loading...</td></tr>');

            // Determine if user is admin (from blade)
            var isAdmin = {{ auth()->user() && auth()->user()->hasRole('Admin') ? 'true' : 'false' }};

            // Fetch data via AJAX
            $.get('/api/warehouse-stock', {
                category_id: category_id,
                brand_id: brand_id,
                model: model,
                model_no: model_no
            }, function(data) {
                let rows = '';
                if (data.length > 0) {
                    data.forEach(function(item) {
                        if (isAdmin) {
                            rows += `<tr data-warehouse-id="${item.warehouse_id}">
                                <td>${item.warehouse}</td>
                                <td>
                                    <span class="stock-qty" style="cursor:pointer;" onclick="editStockQty(this, ${item.qty})">${item.qty}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-serials-btn" onclick="viewSerials('${item.serials || ''}')" title="View Serials"><i class="fas fa-barcode"></i></button>
                                    <button class="btn btn-sm btn-success save-stock-btn" style="display:none;">Save</button>
                                </td>
                            </tr>`;
                        } else {
                            rows += `<tr data-warehouse-id="${item.warehouse_id}">
                                <td>${item.warehouse}</td>
                                <td>
                                    <span class="stock-qty" style="cursor:default;">${item.qty}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-serials-btn" onclick="viewSerials('${item.serials || ''}')" title="View Serials"><i class="fas fa-barcode"></i></button>
                                </td>
                            </tr>`;
                        }
                    });
                } else {
                    if (isAdmin) {
                        rows = '<tr><td colspan="3">No stock found.</td></tr>';
                    } else {
                        rows = '<tr><td colspan="2">No stock found.</td></tr>';
                    }
                }
                $('#productTable tbody').html(rows);
            });

            // Show modal only if not already open
            var $modal = $('#stockModal');
            var bsModal = bootstrap.Modal.getInstance($modal[0]);
            if (!bsModal) {
                bsModal = new bootstrap.Modal($modal[0]);
            }
            bsModal.show();
            // Move focus to modal body for accessibility
            setTimeout(function() {
                $modal.find('.modal-body').attr('tabindex', '-1').focus();
            }, 300);
        }

        // Make stock quantity editable
        function editStockQty(span, qty) {
            // Only allow edit if admin
            var isAdmin = {{ auth()->user() && auth()->user()->hasRole('Admin') ? 'true' : 'false' }};
            if (!isAdmin) return;
            var $span = $(span);
            var $td = $span.closest('td');
            var $tr = $span.closest('tr');
            var warehouse_id = $tr.data('warehouse-id');
            // Replace span with input
            $span.hide();
            $td.append(`<input type="number" class="form-control stock-qty-input" value="${qty}" min="0" style="width:80px;display:inline-block;">`);
            $tr.find('.save-stock-btn').show();
        }

        // Save edited stock quantity
        $(document).on('click', '.save-stock-btn', function() {
            // Only allow save if admin
            var isAdmin = {{ auth()->user() && auth()->user()->hasRole('Admin') ? 'true' : 'false' }};
            if (!isAdmin) return;
            var $tr = $(this).closest('tr');
            var warehouse_id = $tr.data('warehouse-id');
            var newQty = $tr.find('.stock-qty-input').val();
            // Use global variables for product context
            var category_id = currentCategoryId;
            var brand_id = currentBrandId;
            var model = currentModel;
            var model_no = currentModelNo;
            // AJAX update
            $.ajax({
                url: '/api/update-warehouse-stock',
                type: 'POST',
                data: {
                    warehouse_id: warehouse_id,
                    category_id: category_id,
                    brand_id: brand_id,
                    model: model,
                    model_no: model_no,
                    qty: newQty,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(resp) {
                    // After update, refresh the modal table using global product context
                    $.get('/api/warehouse-stock', {
                        category_id: category_id,
                        brand_id: brand_id,
                        model: model,
                        model_no: model_no
                    }, function(data) {
                        let rows = '';
                        if (data.length > 0) {
                            data.forEach(function(item) {
                                if (isAdmin) {
                                    rows += `<tr data-warehouse-id="${item.warehouse_id}">
                                        <td>${item.warehouse}</td>
                                        <td>
                                            <span class="stock-qty" style="cursor:pointer;" onclick="editStockQty(this, ${item.qty})">${item.qty}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-serials-btn" onclick="viewSerials('${item.serials || ''}')" title="View Serials"><i class="fas fa-barcode"></i></button>
                                            <button class="btn btn-sm btn-success save-stock-btn" style="display:none;">Save</button>
                                        </td>
                                    </tr>`;
                                } else {
                                    rows += `<tr data-warehouse-id="${item.warehouse_id}">
                                        <td>${item.warehouse}</td>
                                        <td>
                                            <span class="stock-qty" style="cursor:default;">${item.qty}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-serials-btn" onclick="viewSerials('${item.serials || ''}')" title="View Serials"><i class="fas fa-barcode"></i></button>
                                        </td>
                                    </tr>`;
                                }
                            });
                        } else {
                            rows = '<tr><td colspan="3">No stock found.</td></tr>';
                        }
                        $('#productTable tbody').html(rows);
                        // Move focus to modal body for accessibility after update
                        var $modal = $('#stockModal');
                        setTimeout(function() {
                            $modal.find('.modal-body').attr('tabindex', '-1').focus();
                        }, 300);
                    });
                    // Show success message in modal
                    var $msg = $('#stockSuccessMsg');
                    $msg.text('Stock updated successfully!').fadeIn();
                    setTimeout(function() {
                        $msg.fadeOut();
                    }, 2000);
                    // Refresh main stock table after modal update
                    $.get(window.location.pathname, function(pageHtml) {
                        var newTable = $(pageHtml).find('#customerTable tbody').html();
                        $('#customerTable tbody').html(newTable);
                    });
                },
                error: function(xhr) {
                    var $msg = $('#stockSuccessMsg');
                    $msg.removeClass('alert-success').addClass('alert-danger');
                    $msg.text('Error updating stock.').fadeIn();
                    setTimeout(function() {
                        $msg.fadeOut(function(){
                            $msg.removeClass('alert-danger').addClass('alert-success');
                        });
                    }, 2000);
                }
            });
        });

        // Function to show serial numbers in a SweetAlert2 popup
        function viewSerials(serials) {
            if (!serials || serials.trim() === "" || serials.trim() === "null") {
                Swal.fire({
                    icon: 'info',
                    title: 'No Serials Found',
                    text: 'No serial numbers are recorded for this stock.'
                });
                return;
            }
            // Clean up serials string (remove extra commas/spaces)
            let cleanedSerials = serials.split(',').map(s => s.trim()).filter(s => s !== "" && s !== "null").join(', ');
            
            if (!cleanedSerials) {
                 Swal.fire({
                    icon: 'info',
                    title: 'No Serials Found',
                    text: 'No serial numbers are recorded for this stock.'
                });
                return;
            }

            Swal.fire({
                title: 'Available Serial Numbers',
                html: '<div style="max-height: 250px; overflow-y: auto; text-align: left; padding: 10px; border: 1px solid #eee; border-radius: 5px; font-family: monospace; font-size: 0.9rem; line-height: 1.5; background: #f9f9f9;">' + cleanedSerials + '</div>',
                icon: 'info',
                confirmButtonText: 'Close',
                customClass: {
                    container: 'my-swal'
                }
            });
        }
    </script>

@endsection