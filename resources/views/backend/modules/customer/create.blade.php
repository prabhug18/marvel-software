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

                <form  class="row g-4" method="POST" enctype="multipart/form-data" action="{{ route('customer.store') }}">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" placeholder="Enter Name" required name="name"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Phone <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" placeholder="Enter Phone Number" required name="mobile_no"/>                        
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
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
                        <!-- Empty form group for layout balance -->
                    </div>
                    
                    <div class="col-2">
                        <button type="submit" class="btn btn-success btn-lg"> Submit</button>
                    </div>
                            
                </form>
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

    </script>
@endsection