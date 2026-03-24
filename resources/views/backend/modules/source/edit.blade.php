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
                    <form class="row g-4" method="POST" action="{{ route('source.update', $source->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <input type="text" name="name" placeholder="Enter source name" class="form-control" value="{{ $source->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <select name="status" class="form-select">
                                <option value="1" {{ $source->status ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$source->status ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
