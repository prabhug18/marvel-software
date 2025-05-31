@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="col-md-6">
            @include('backend.include.formError')
            @if(Session::has('create_brand'))
                <div class="alert alert-success col-md-12">
                    <strong>{{session('create_brand')}}</strong>
                </div>
            @endif
            @if(Session::has('delete_brand'))
                <div class="alert alert-danger col-md-12">
                    <strong>{{session('delete_brand')}}</strong>
                </div>
            @endif
            @if(Session::has('edit_brand'))
                <div class="alert alert-warning col-md-12">
                    <strong>{{session('edit_brand')}}</strong>
                </div>
            @endif
        </div>

        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <form class="row g-4" method="POST" action="{{ route('brands.update', $brand->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <input type="text" name="name" placeholder="Enter brand name" class="form-control" value="{{ $brand->name }}" required>
                        </div>    
                        <div class="col-md-4">                
                            <button type="submit" class="btn btn-success btn-lg"> Update</button>                
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
      </div>
        
    </main>
   
@endsection