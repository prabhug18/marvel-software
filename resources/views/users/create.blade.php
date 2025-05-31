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
       
                    <div class="row">
                        <div class="col text-end mb-3">
                            <a class="btn custom-orange-btn text-white" href="{{ route('users.index') }}">
                                <i class="fas fa-user-plus me-2"></i>View User
                            </a>
                        </div>                
                    </div>

            
                    <form method="POST" class="row g-4" action="{{ route('users.store') }}">
                        @csrf
                        
                       
                            <div class="col-md-6">    
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="Name" class="form-control">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>                            
                                <input type="email" name="email" placeholder="Email" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>                            
                                <input type="password" name="password" placeholder="Password" class="form-control">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>                            
                                <input type="password" name="confirm-password" placeholder="Confirm Password" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mobile <span class="text-danger">*</span></label>                            
                                <input type="text" name="mobile_no" placeholder="Enter Mobile No" class="form-control" maxlength="10">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>                           
                                <select name="roles[]" class="form-control" multiple="multiple">
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>                            
                            
                            <div class="col-xs-8 col-sm-8 col-md-8 text-center">
                                <button type="submit" class="btn-primary"> Save</button>
                            </div>
                      
                    </form>
                </div> 
            </div>   
        </div>

    </main>



@endsection