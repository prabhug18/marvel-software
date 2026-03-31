@extends('layouts.backend')

@section('content')
    @include('backend.include.mnubar')

    <div class="main-content" id="mainContent">
        @include('backend.include.header')
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ $heading }}</h4>
                <a href="{{ route('vendors.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-plus me-2"></i>Add Vendor
                </a>
            </div>

            @if(session('success'))
                <div id="successAlert" class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="vendorTable">
                            <thead class="bg-light bg-gradient">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase fs-xs fw-bold text-muted">Vendor Name</th>
                                    <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Contact Info</th>
                                    <th class="py-3 text-uppercase fs-xs fw-bold text-muted">GST No</th>
                                    <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Status</th>
                                    <th class="pe-4 py-3 text-uppercase text-end fs-xs fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $vendor)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $vendor->name }}</div>
                                            <div class="text-muted small">{{ Str::limit($vendor->address, 60) }}</div>
                                        </td>
                                        <td>
                                            <div class="small"><i class="fas fa-phone-alt me-2 text-muted"></i>{{ $vendor->mobile ?? 'N/A' }}</div>
                                            <div class="small"><i class="fas fa-envelope me-2 text-muted"></i>{{ $vendor->email ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fw-normal">{{ $vendor->gst_no ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($vendor->status_id == 1)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                                <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-sm btn-white text-primary px-3" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-white text-danger px-3" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted py-4">
                                                <i class="fas fa-info-circle fa-2x mb-3 text-muted opacity-25"></i>
                                                <p>No vendors found. Click "Add Vendor" to create one.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fs-xs { font-size: 0.70rem; letter-spacing: 0.05em; }
        .btn-white { background: #fff; border: none; }
        .btn-white:hover { background: #f8f9fa; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.01); }
        .badge { font-weight: 500; font-size: 0.8rem; }
    </style>
@endsection
