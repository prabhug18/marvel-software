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

                        <form method="POST" action="{{ route('locations.update', $warehouse->id) }}">
                            @csrf
                            @method('PUT')

                             <div id="warehouseStockFields">
                                <div class="row g-3 align-items-end warehouse-entry mb-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter location" name="name" required value="{{ $warehouse->name }}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Enter Invoice Prefix" name="prefix" required value="{{ $warehouse->prefix }}">                            
                                    </div>
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