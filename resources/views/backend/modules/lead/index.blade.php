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
                            @can('lead-create')
                            <a class="btn custom-orange-btn text-white" href="{{ route('leads.create') }}">
                                <i class="fas fa-plus-circle me-2"></i>Add Lead
                            </a>
                            @endcan
                        </div>
                    </div>

                    @include('backend.include.formError')
                    @if(Session::has('create_lead'))
                        <div class="alert alert-success">
                            <strong>{{ session('create_lead') }}</strong>
                        </div>
                    @endif
                    @if(Session::has('edit_lead'))
                        <div class="alert alert-warning">
                            <strong>{{ session('edit_lead') }}</strong>
                        </div>
                    @endif
                    @if(Session::has('delete_lead'))
                        <div class="alert alert-danger">
                            <strong>{{ session('delete_lead') }}</strong>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="leadTable" class="table table-bordered table-hover align-middle">
                            <thead class="custom-thead text-center">
                                <tr>
                                    <th>S.No</th>
                                    <th>Lead #</th>
                                    <th>Name</th>
                                    <th>Mobile No</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Expected Value</th>
                                    <th>Next Follow-up</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $index => $lead)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $lead->lead_number }}</td>
                                    <td>{{ $lead->name }}</td>
                                    <td class="text-center">{{ $lead->mobile_no }}</td>
                                    <td class="text-center">
                                        @if($lead->priority == 'high')
                                            <span class="badge bg-danger">High</span>
                                        @elseif($lead->priority == 'medium')
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-success">Low</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($lead->status == 'new')
                                            <span class="badge bg-info">New</span>
                                        @elseif($lead->status == 'follow_up')
                                            <span class="badge bg-primary">Follow-up</span>
                                        @elseif($lead->status == 'negotiation')
                                            <span class="badge bg-warning text-dark">Negotiation</span>
                                        @elseif($lead->status == 'converted')
                                            <span class="badge bg-success">Converted</span>
                                        @else
                                            <span class="badge bg-secondary">Lost</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ $lead->expected_value ? '₹' . number_format($lead->expected_value, 2) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($lead->next_follow_up)
                                            <span class="{{ \Carbon\Carbon::parse($lead->next_follow_up)->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ \Carbon\Carbon::parse($lead->next_follow_up)->format('d M Y') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('lead-edit')
                                            <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('lead-delete')
                                            <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this lead?')">
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
            $('#leadTable').DataTable({
                "pageLength": 10,
                "order": [[0, "asc"]],
                "language": {
                    "search": "Filter leads:",
                }
            });
        });
    </script>
@endsection
