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
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <h4 class="mb-0">{{ $heading }}</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            @can('enquiry-create')
                            <a class="btn custom-orange-btn text-white" href="{{ route('enquiries.create') }}">
                                <i class="fas fa-plus-circle me-2"></i>Add Enquiry
                            </a>
                            @endcan
                        </div>
                    </div>

                    @include('backend.include.formError')
                    @if(Session::has('create_enquiry'))
                        <div class="alert alert-success">
                            <strong>{{ session('create_enquiry') }}</strong>
                        </div>
                    @endif
                    @if(Session::has('edit_enquiry'))
                        <div class="alert alert-warning">
                            <strong>{{ session('edit_enquiry') }}</strong>
                        </div>
                    @endif
                    @if(Session::has('delete_enquiry'))
                        <div class="alert alert-danger">
                            <strong>{{ session('delete_enquiry') }}</strong>
                        </div>
                    @endif
                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            <strong>{{ session('success') }}</strong>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="enquiryTable" class="table table-bordered table-hover align-middle">
                            <thead class="custom-thead text-center">
                                <tr>
                                    <th>S.No</th>
                                    <th>Enquiry #</th>
                                    <th>Name</th>
                                    <th>Mobile No</th>
                                    <th>City</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enquiries as $index => $enquiry)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $enquiry->enquiry_number }}</td>
                                    <td>{{ $enquiry->name }}</td>
                                    <td class="text-center">{{ $enquiry->mobile_no }}</td>
                                    <td>{{ $enquiry->city ? $enquiry->city->name : 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $enquiry->source ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($enquiry->status == 'new')
                                            <span class="badge bg-info text-white px-3 py-2 rounded-pill">New</span>
                                        @elseif($enquiry->status == 'contacted')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Contacted</span>
                                        @elseif($enquiry->status == 'converted')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">Converted</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill">Closed</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('enquiries.show', $enquiry->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('enquiry-edit')
                                            <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @if($enquiry->status !== 'converted')
                                            <form action="{{ route('enquiries.convertToLead', $enquiry->id) }}" method="POST" class="d-inline" id="convert-form-{{ $enquiry->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-success btn-convert-enquiry" data-id="{{ $enquiry->id }}" title="Convert to Lead">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @can('enquiry-delete')
                                            <form action="{{ route('enquiries.destroy', $enquiry->id) }}" method="POST" class="d-inline" id="delete-form-{{ $enquiry->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-enquiry" data-id="{{ $enquiry->id }}" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
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

    <script>
        $(document).ready(function() {
            $('#enquiryTable').DataTable({
                "pageLength": 10,
                "order": [[0, "asc"]],
                "language": {
                    "search": "Filter records:",
                }
            });

            $('.btn-convert-enquiry').on('click', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Convert to Lead?',
                    text: 'Move this enquiry to the sales pipeline?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Convert'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#convert-form-' + id).submit();
                    }
                });
            });

            $('.btn-delete-enquiry').on('click', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#delete-form-' + id).submit();
                    }
                });
            });
        });
    </script>
@endsection
