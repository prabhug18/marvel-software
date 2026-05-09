@extends('layouts.backend')

@section('content')
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <h4 class="mb-0">{{ $heading }}</h4>
                        </div>
                    </div>

                    @include('backend.include.formError')

                    <form action="{{ route('enquiries.store') }}" method="POST" class="row g-4">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Name" required value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile_no" placeholder="Enter 10 Digit Mobile No" required value="{{ old('mobile_no') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter Email Address" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select select2">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->name }}" {{ old('source') == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Enter Address">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state" id="state" class="form-select select2" required>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}" {{ old('state') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city" id="city" class="form-select select2" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Interest (Auto-suggest)</label>
                            <div class="position-relative">
                                <input type="text" id="productSearch" class="form-control" placeholder="Search products..." autocomplete="off">
                                <div id="productSuggestions" class="list-group position-absolute w-100 z-index-1000 d-none" style="max-height: 200px; overflow-y: auto;"></div>
                            </div>
                            <textarea name="product_interest" id="productInterest" class="form-control mt-2" rows="2" placeholder="Selected products will appear here...">{{ old('product_interest') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand Interest</label>
                            <input type="text" class="form-control" name="brand_interest" placeholder="e.g. Amaron, Exide" value="{{ old('brand_interest') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select select2">
                                <option value="">Select Staff</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->hasRole('Admin'))
                        <div class="col-md-6">
                            <label class="form-label">Location (Warehouse)</label>
                            <select name="warehouse_id" class="form-select select2">
                                <option value="">Select Location</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2" placeholder="Any additional notes...">{{ old('remarks') }}</textarea>
                        </div>
                        <div class="col-md-12 mt-4">
                            <button type="submit" class="btn btn-success px-5 rounded-pill">
                                <i class="fas fa-save me-2"></i>Save Enquiry
                            </button>
                            <a href="{{ route('enquiries.index') }}" class="btn btn-light px-5 rounded-pill ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#state').change(function() {
                var stateID = $(this).val();
                if(stateID) {
                    $.ajax({
                        url: '/get-city',
                        type: 'GET',
                        data: {state_id: stateID},
                        success: function(data) {
                            $('#city').empty().append('<option value="">Select City</option>');
                            $.each(data, function(key, value) {
                                $('#city').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                            });
                        }
                    });
                } else {
                    $('#city').empty().append('<option value="">Select City</option>');
                }
            });

            // Product Auto-suggest
            $('#productSearch').on('input', function() {
                var q = $(this).val();
                if (q.length < 2) {
                    $('#productSuggestions').addClass('d-none').empty();
                    return;
                }

                $.ajax({
                    url: '/product-search',
                    data: { q: q },
                    success: function(products) {
                        var html = '';
                        products.forEach(function(p) {
                            var display = [p.brand, p.series, p.model].filter(Boolean).join(' - ');
                            html += '<button type="button" class="list-group-item list-group-item-action product-item" data-name="'+display+'">'+display+'</button>';
                        });
                        if (html) {
                            $('#productSuggestions').removeClass('d-none').html(html);
                        } else {
                            $('#productSuggestions').addClass('d-none').empty();
                        }
                    }
                });
            });

            $(document).on('click', '.product-item', function() {
                var name = $(this).data('name');
                var current = $('#productInterest').val();
                if (current) {
                    $('#productInterest').val(current + ', ' + name);
                } else {
                    $('#productInterest').val(name);
                }
                $('#productSearch').val('');
                $('#productSuggestions').addClass('d-none').empty();
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('#productSearch, #productSuggestions').length) {
                    $('#productSuggestions').addClass('d-none');
                }
            });
        });
    </script>
@endsection
