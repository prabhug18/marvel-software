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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="mb-0">Create New Role</h4>
                    </div>
                    <div class="col-md-3 text-end float-end">
                        <a class="btn btn-primary btn-sm" href="{{ route('roles.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('roles.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label"><strong>Name</strong></label>
                                <input type="text" name="name" placeholder="Name" class="form-control" value="{{ old('name') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label"><strong>Permissions</strong></label>
                                <div class="row">
                                    @foreach($permission as $value)
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="permission[{{$value->id}}]" value="{{$value->id}}" class="form-check-input" id="perm{{$value->id}}">
                                                <label class="form-check-label" for="perm{{$value->id}}">{{ $value->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-sm mb-3"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection