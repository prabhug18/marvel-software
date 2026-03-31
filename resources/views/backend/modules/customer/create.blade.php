@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
    
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
          
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">

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

                <form id="customerForm" class="row g-4" method="POST" enctype="multipart/form-data" action="{{ route('customer.store') }}">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Customer Type</label>
                        <select class="form-select" name="customer_type">
                            <option value="">Select Type</option>
                            <option value="Customer">Customer</option>
                            <option value="Dealer">Dealer</option>                            
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" placeholder="Enter Name" required name="name"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Phone <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" placeholder="Enter Phone Number" required name="mobile_no"/>                        
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Alternative Phone</label>
                        <input class="form-control" type="text" placeholder="Enter Alternative Phone Number" name="alternative_no"/>                        
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" placeholder="Enter Email Address" name="email"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input class="form-control" type="text" placeholder="Enter Address" name="address"/>                        
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">State</label>
                        
                        <select name="state" class="form-select select2" id=state>
                            <option value="">Select State</option>
                            @foreach($state as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>                                                
                        <select name="city" class="form-select select2" id=city>
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pincode</label>
                        <input class="form-control" type="text" placeholder="Enter Pincode" name="pincode"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">GST No</label>
                        <input class="form-control" type="text" placeholder="Enter GST No" name="gst_no"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Source</label>
                        <select class="form-select select2" name="source">
                            <option value="">Select Source</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->name }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" placeholder="Enter Remarks" name="remarks"></textarea>
                    </div>
                    <div class="col-2 mt-5">
                        <button type="submit" class="btn btn-success btn-lg"> Submit</button>
                    </div>
                    {{-- ensure warehouse_id is submitted; prefer authenticated user's warehouse_id --}}
                    <input type="hidden" name="warehouse_id" value="{{ auth()->user()->warehouse_id ?? 1 }}">
                </form>
                <div id="formErrorAjax"></div>
            </div>
        </div>

      </div>
        
    </main>
   
   <script>
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

    $(document).ready(function() {
      $('#state').select2({
        placeholder: "Select State",
        tags: true,
        width: '100%'
      });
    });

    $(document).ready(function() {
      $('#city').select2({
        placeholder: "Select City",
        tags: true,
        width: '100%'
      });
    });

    $(document).ready(function() {
        $('#customerForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                headers: {'X-CSRF-TOKEN': $('input[name="_token"]').val()},
                success: function(response) {
                    $('#formErrorAjax').html('<div class="alert alert-success">Customer created successfully</div>');
                    form.trigger('reset');
                    setTimeout(function() {
                        window.location.href = '/customer';
                    }, 1200);
                },
                error: function(xhr) {
                    if(xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorHtml = '<div class="alert alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        $('#formErrorAjax').html(errorHtml);
                    } else {
                        let msg = 'An error occurred. Please try again.';
                        if(xhr.responseText) {
                            try {
                                let json = JSON.parse(xhr.responseText);
                                if(json.message) msg = json.message;
                            } catch(e) {
                                msg = xhr.responseText;
                            }
                        }
                        $('#formErrorAjax').html('<div class="alert alert-danger">'+msg+'</div>');
                    }
                }
            });
        });
    });
    </script>
@endsection