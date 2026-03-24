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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Source List</h4>
                        <a href="{{ route('source.create') }}" class="btn btn-primary">Add Source</a>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>                                
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sources as $source)
                                <tr>
                                    <td>{{ $source->id }}</td>
                                    <td>{{ $source->name }}</td>
                                    <td>{{ $source->status ? 'Active' : 'Inactive' }}</td>                                    
                                    <td>
                                        <a href="{{ route('source.show', $source->id) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('source.edit', $source->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('source.destroy', $source->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this source?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection
