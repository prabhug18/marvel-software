@extends('layouts.backend')

@section('content')

    <div class="toggle-btn" id="toggleBtn">
            <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

       @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col text-end mb-3">
                            <a class="btn custom-orange-btn text-white" href="{{ route('users.index') }}">
                                <i class="fas fa-user-plus me-2"></i>View User
                            </a>
                        </div>                
                    </div>
            
                <form class="row g-4" method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" placeholder="Name" class="form-control" value="{{ $user->name }}">
                    </div>
                        
                    <div class="col-md-6"> 
                        <label class="form-label">Email <span class="text-danger">*</span></label>                            
                        <input type="email" name="email" placeholder="Email" class="form-control" value="{{ $user->email }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password</label>                            
                        <input type="password" name="password" placeholder="Password" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>  
                        <input type="password" name="confirm-password" placeholder="Confirm Password" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile <span class="text-danger">*</span></label> 
                        <input type="text" name="mobile_no" placeholder="Enter Mobile No" class="form-control" value="{{ $user->mobile_no }}">
                    </div>
                    
                    <div class="col-md-6"> 
                        <label class="form-label">Role <span class="text-danger">*</span></label>   
                        <select name="roles[]" class="form-control" multiple="multiple">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ isset($userRole[$value]) ? 'selected' : ''}}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                        
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-sm mt-2 mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
                    </div>
                    
                </form> 
            </div>   
        </div>

    </main>



@endsection