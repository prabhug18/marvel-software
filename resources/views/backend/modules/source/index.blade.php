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
                    <div class="table-responsive">
                        <table id="sourceTable" class="table table-bordered table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>                                
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sources as $source)
                                    <tr>
                                        <td>{{ $source->id }}</td>
                                        <td>{{ $source->name }}</td>
                                        <td>
                                            @if($source->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>                                    
                                        <td class="text-center">
                                            <a href="{{ route('source.show', $source->id) }}" class="btn btn-info btn-sm text-white" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('source.edit', $source->id) }}" class="btn btn-warning btn-sm text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form id="delete-form-{{ $source->id }}" action="{{ route('source.destroy', $source->id) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="confirmDelete({{ $source->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    $(document).ready(function() {
        $('#sourceTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            lengthChange: true,
            pageLength: 10,
            language: {
                searchPlaceholder: "Search sources...",
                search: "",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            },
            dom: '<"top"lf>rt<"bottom"ip><"clear">'
        });
    });
</script>
@endpush
