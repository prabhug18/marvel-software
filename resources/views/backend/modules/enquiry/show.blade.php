@extends('layouts.backend')

@section('content')
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="container-fluid px-3">
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary fw-bold">{{ $heading }}</h5>
                            @if($enquiry->status !== 'converted')
                            <form id="convertEnquiryForm" action="{{ route('enquiries.convertToLead', $enquiry->id) }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" id="convertEnquiryBtn">
                                    <i class="fas fa-arrow-right me-2"></i>Convert to Lead
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success px-3 py-2 rounded-pill">Already Converted</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Contact Name</label>
                                    <p class="fs-5 mb-0">{{ $enquiry->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Mobile Number</label>
                                    <p class="fs-5 mb-0 text-primary fw-bold">{{ $enquiry->mobile_no }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Email Address</label>
                                    <p class="fs-6 mb-0">{{ $enquiry->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Source</label>
                                    <span class="badge bg-light text-dark border px-3 py-2">{{ $enquiry->source ?? 'Direct Enquiry' }}</span>
                                </div>
                                <div class="col-md-12">
                                    <hr class="my-2 opacity-10">
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Location</label>
                                    <p class="mb-0">{{ $enquiry->city ? $enquiry->city->name : '' }}, {{ $enquiry->state ? $enquiry->state->name : '' }}</p>
                                    <p class="text-muted small mb-0">{{ $enquiry->address }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Warehouse</label>
                                    <p class="mb-0">{{ $enquiry->warehouse ? $enquiry->warehouse->name : 'Global' }}</p>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Product/Brand Interest</label>
                                    <div class="bg-light p-3 rounded-3 mt-1">
                                        <p class="mb-2"><strong>Products:</strong> {{ $enquiry->product_interest ?: 'None specified' }}</p>
                                        <p class="mb-0"><strong>Brands:</strong> {{ $enquiry->brand_interest ?: 'None specified' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Remarks</label>
                                    <p class="mt-1 p-2 border-start border-3 border-warning bg-light">{{ $enquiry->remarks ?: 'No remarks available.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 border-0 mb-4">
                        <div class="card-body p-4">
                            <h6 class="text-muted small text-uppercase fw-bold mb-3">Enquiry Status</h6>
                            <div class="d-flex align-items-center mb-4">
                                @if($enquiry->status == 'new')
                                    <div class="bg-info text-white p-3 rounded-circle me-3">
                                        <i class="fas fa-star fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">New</h5>
                                        <p class="text-muted small mb-0">Newly created enquiry</p>
                                    </div>
                                @elseif($enquiry->status == 'contacted')
                                    <div class="bg-warning text-dark p-3 rounded-circle me-3">
                                        <i class="fas fa-phone fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Contacted</h5>
                                        <p class="text-muted small mb-0">Initial contact made</p>
                                    </div>
                                @elseif($enquiry->status == 'converted')
                                    <div class="bg-success text-white p-3 rounded-circle me-3">
                                        <i class="fas fa-check fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Converted</h5>
                                        <p class="text-muted small mb-0">Promoted to Lead</p>
                                    </div>
                                @else
                                    <div class="bg-secondary text-white p-3 rounded-circle me-3">
                                        <i class="fas fa-times fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Closed</h5>
                                        <p class="text-muted small mb-0">Lost or discarded</p>
                                    </div>
                                @endif
                            </div>

                            <h6 class="text-muted small text-uppercase fw-bold mb-3">Staff Details</h6>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Assigned To:</label>
                                <p class="fw-bold mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>{{ $enquiry->assignee ? $enquiry->assignee->name : 'Unassigned' }}</p>
                            </div>
                            <div class="mb-0">
                                <label class="text-muted small d-block">Created By:</label>
                                <p class="mb-0 small text-muted">{{ $enquiry->user ? $enquiry->user->name : 'System' }} on {{ $enquiry->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        @can('enquiry-edit')
                        <a href="{{ route('enquiries.edit', $enquiry->id) }}" class="btn btn-outline-warning rounded-pill py-2">
                            <i class="fas fa-edit me-2"></i>Edit Enquiry
                        </a>
                        @endcan
                        <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#convertEnquiryBtn').on('click', function() {
                Swal.fire({
                    title: 'Convert to Lead?',
                    text: 'Are you sure you want to convert this enquiry into a qualified lead?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Convert It!',
                    cancelButtonText: 'Not Yet',
                    customClass: {
                        popup: 'rounded-4 shadow-lg',
                        confirmButton: 'btn btn-success px-4 rounded-pill me-2',
                        cancelButton: 'btn btn-secondary px-4 rounded-pill'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#convertEnquiryForm').submit();
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
