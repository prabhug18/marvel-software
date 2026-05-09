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

                    <form action="{{ route('leads.store') }}" method="POST" class="row g-4">
                        @csrf
                        @if(isset($enquiry))
                            <input type="hidden" name="enquiry_id" value="{{ $enquiry->id }}">
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Name" required value="{{ old('name', $enquiry->name ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile_no" placeholder="Enter 10 Digit Mobile No" required value="{{ old('mobile_no', $enquiry->mobile_no ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter Email Address" value="{{ old('email', $enquiry->email ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-select select2">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->name }}" {{ old('source', $enquiry->source ?? '') == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Enter Address">{{ old('address', $enquiry->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state" id="state" class="form-select select2" required>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}" {{ old('state', $enquiry->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select name="city" id="city" class="form-select select2" required>
                                <option value="">Select City</option>
                                @if(isset($enquiry) && $enquiry->city_id)
                                    <option value="{{ $enquiry->city_id }}" selected>{{ $enquiry->city->name }}</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="col-md-12"><hr class="my-0"></div>

                        <div class="col-md-4">
                            <label class="form-label">Expected Value (₹)</label>
                            <input type="number" step="0.01" class="form-control fw-bold text-success" name="expected_value" placeholder="0.00" value="{{ old('expected_value') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select select2" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select select2" required>
                                <option value="new" {{ old('status', 'new') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="follow_up" {{ old('status') == 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="negotiation" {{ old('status') == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                                <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_follow_up" value="{{ old('next_follow_up') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select select2">
                                <option value="">Select Staff</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $enquiry->assigned_to ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->hasRole('Admin'))
                        <div class="col-md-4">
                            <label class="form-label">Location (Warehouse)</label>
                            <select name="warehouse_id" class="form-select select2">
                                <option value="">Select Location</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $enquiry->warehouse_id ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Product Interest</label>
                            <textarea name="product_interest" class="form-control" rows="2" placeholder="Products interested in...">{{ old('product_interest', $enquiry->product_interest ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand Interest</label>
                            <input type="text" class="form-control" name="brand_interest" placeholder="e.g. Amaron, Exide" value="{{ old('brand_interest', $enquiry->brand_interest ?? '') }}">
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <button type="submit" class="btn btn-success px-5 rounded-pill">
                                <i class="fas fa-save me-2"></i>Save Lead
                            </button>
                            <a href="{{ route('leads.index') }}" class="btn btn-light px-5 rounded-pill ms-2">Cancel</a>
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
            
            @if(isset($enquiry) && $enquiry->state_id)
                // If city is not already loaded by the initial value check
                if($('#city option').length <= 1) {
                    $('#state').trigger('change');
                }
            @endif
        });
    </script>
@endsection
