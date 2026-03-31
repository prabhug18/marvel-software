@extends('layouts.backend')

@section('content')
    @include('backend.include.mnubar')

    <div class="main-content" id="mainContent">
        @include('backend.include.header')
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-4">
                        <a href="{{ route('vendors.index') }}" class="btn btn-link link-dark p-0 me-3 shadow-none">
                            <i class="fas fa-arrow-left fa-lg"></i>
                        </a>
                        <h4 class="mb-0 fw-bold">{{ $heading }}</h4>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form id="vendorForm">
                                @csrf
                                <div class="row g-4">
                                    <!-- Vendor Name -->
                                    <div class="col-12">
                                        <label for="name" class="form-label fw-semibold text-muted small text-uppercase">Vendor Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-store text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 py-3" name="name" id="name" placeholder="Enter Full Company Name" required>
                                        </div>
                                    </div>

                                    <!-- Contact Info -->
                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label fw-semibold text-muted small text-uppercase">Mobile Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-phone-alt text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 py-3" name="mobile" id="mobile" placeholder="+91 00000 00000">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold text-muted small text-uppercase">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control bg-light border-0 py-3" name="email" id="email" placeholder="vendor@example.com">
                                        </div>
                                    </div>

                                    <!-- GST No -->
                                    <div class="col-12">
                                        <label for="gst_no" class="form-label fw-semibold text-muted small text-uppercase">GST Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-id-card text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 py-3" name="gst_no" id="gst_no" placeholder="22AAAAA0000A1Z5">
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12">
                                        <label for="address" class="form-label fw-semibold text-muted small text-uppercase">Full Address</label>
                                        <textarea class="form-control bg-light border-0 py-3" name="address" id="address" rows="3" placeholder="Street, City, State, Zip"></textarea>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12 text-end pt-3">
                                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-sm" id="submitBtn">
                                            Save Vendor Details
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 if not already in layout -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#vendorForm').on('submit', function(e) {
                e.preventDefault();
                
                const $btn = $('#submitBtn');
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: "{{ route('vendors.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('vendors.index') }}";
                        });
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Save Vendor Details');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let msg = Object.values(errors).flat().join('<br>');
                            Swal.fire('Validation Error', msg, 'error');
                        } else {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        }
                    }
                });
            });
        });
    </script>

    <style>
        .form-control:focus {
            background-color: #f0f2f5 !important;
            box-shadow: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05);
        }
        .input-group-text {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
    </style>
@endsection
