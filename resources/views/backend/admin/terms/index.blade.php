@extends('layouts.backend')

@section('content')
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        <div class="container-fluid">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Terms & Conditions</h5>
                    <a href="{{ route('terms.create') }}" class="btn btn-sm btn-primary">Create Term</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Content</th>
                                <th>Active</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($terms as $term)
                                <tr>
                                    <td>{{ $term->id }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($term->content ?? ''), 80) }}</td>
                                    <td>{{ $term->active ? 'Yes' : 'No' }}</td>
                                    <td>{{ $term->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this term?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $terms->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
