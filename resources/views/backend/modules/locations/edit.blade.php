@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
          
       <div style="padding-top: 30px;"></div>
            <div class="container-fluid px-3">
                <div class="card shadow-sm rounded-4 mt-4">
                    <div class="card-body">

                        <div class="col-md-6">
                            @include('backend.include.formError')
                            
                            @if(Session::has('delete_success'))
                                <div class="alert alert-success col-md-12">
                                    <strong>{{session('delete_success')}}</strong>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('locations.update', $warehouse->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                             <div id="warehouseStockFields">
                                <div class="row g-3 align-items-end warehouse-entry mb-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter location" name="name" required value="{{ $warehouse->name }}">
                                    </div>                                    

                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter Company Name" name="company_name" value="{{ $warehouse->company_name }}">
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter Sub Heading" name="sub_heading" value="{{ $warehouse->sub_heading }}">
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter Invoice Prefix" name="prefix" required value="{{ $warehouse->prefix }}">                            
                                    </div>
                                
                                    <div class="col-md-4">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter email" value="{{ $warehouse->email }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="mobile" class="form-label">Mobile</label>
                                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Enter Mobile" value="{{ $warehouse->mobile }}">
                                    </div>                                    
                                
                                    <div class="col-md-4">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" name="bank_name" id="bank_name" placeholder="Enter Bank Name" value="{{ old('bank_name', $warehouse->bank_name) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="account_name" class="form-label">Account Name</label>
                                        <input type="text" class="form-control" name="account_name" id="account_name" placeholder="Enter Account Name" value="{{ old('account_name', $warehouse->account_name) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="text" class="form-control" name="account_number" id="account_number" placeholder="Enter Account Number" value="{{ old('account_number', $warehouse->account_number) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="ifsc_code" class="form-label">IFSC Code</label>
                                        <input type="text" class="form-control" name="ifsc_code" id="ifsc_code" placeholder="Enter IFSC Code" value="{{ old('ifsc_code', $warehouse->ifsc_code) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="branch" class="form-label">Branch</label>
                                        <input type="text" class="form-control" name="branch" id="branch" placeholder="Enter Branch" value="{{ old('branch', $warehouse->branch) }}">
                                    </div>
                                
                                    <div class="col-md-4">
                                        <label for="gstn_uin" class="form-label">GSTN/UIN</label>
                                        <input type="text" class="form-control" name="gstn_uin" id="gstn_uin" placeholder="Enter GSTN/UIN" value="{{ old('gstn_uin', $warehouse->gstn_uin) }}">
                                    </div>

                                    <div class="col-md-8">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" name="address" id="address" rows="2" placeholder="Enter address">{{ $warehouse->address }}</textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" class="form-control" name="image" id="image">
                                        @if($warehouse->image)
                                            <img src="{{ asset($warehouse->image) }}" alt="Warehouse Image" class="img-thumbnail mt-2" style="max-width:120px;">
                                        @endif
                                        @if(session('image_error'))
                                            <div class="alert alert-danger mt-2">{{ session('image_error') }}</div>
                                        @endif
                                    </div>

                                </div>
                                <div class="row g-3 align-items-end warehouse-entry mb-3">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success btn-lg">Submit</button>
                                    </div>
                                </div>
                            </div>

                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    </main>
    
@endsection