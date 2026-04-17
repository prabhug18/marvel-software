@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header')

        <div style="padding-top: 30px;"></div>
        
        <div class="container-fluid px-4">
            <!-- Page Title & breadcrumb -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0">{{ $heading }}</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/stocks') }}" class="text-decoration-none">Stocks</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bulk Data Management</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Header Instructions Section -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-primary-subtle p-3 rounded-4">
                                    <i class="fas fa-info-circle text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark">Important Instructions for Bulk Data</h5>
                                    <p class="text-muted mb-0">Please follow the steps below to ensure your stock data is updated accurately. Using the correct template is essential for data integrity.</p>
                                </div>
                            </div>
                            
                            <hr class="my-4 opacity-10">

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex gap-3">
                                        <div class="step-num bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">1</div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Download Template</h6>
                                            <p class="small text-muted mb-0">Use the <strong>Stock Matrix</strong>. It now includes <strong>Reference Tabs</strong> for valid Categories & Brands.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-3">
                                        <div class="step-num bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">2</div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Fill Sample Values</h6>
                                            <p class="small text-muted mb-0">The Matrix is now a <strong>Sample Template</strong>. Fill your Invoice No, Rate, and Warehouse quantities.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-3">
                                        <div class="step-num bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">3</div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Upload & Sync</h6>
                                            <p class="small text-muted mb-0">Upload the file below. The system will match your products and sync the stock documentation instantly.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Action Cards -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-download text-muted me-2"></i>Download Files</h5>
                            
                            <!-- Template Option 1 -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border border-light-subtle hover-shadow transition">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white p-3 rounded-4 shadow-sm">
                                            <i class="fas fa-file-excel fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Stock Matrix (Multi-Sheet)</h6>
                                            <p class="small text-muted mb-0">Includes <strong>Samples & Referene Tabs</strong></p>
                                        </div>
                                    </div>
                                    <a href="{{ route('export.stocks') }}" target="_blank" download class="btn btn-outline-primary rounded-pill px-4">
                                        Get Template
                                    </a>
                                </div>
                            </div>

                            <!-- Template Option 2 -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border border-light-subtle hover-shadow transition">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success text-white p-3 rounded-4 shadow-sm">
                                            <i class="fas fa-list-ol fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Detailed Report</h6>
                                            <p class="small text-muted mb-0">Includes Serial Numbers (View Only)</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('export.stocks.detailed') }}" target="_blank" download class="btn btn-outline-success rounded-pill px-4">
                                        Download
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4 p-3 rounded-4 bg-warning-subtle border border-warning-subtle text-warning-emphasis small">
                                <i class="fas fa-exclamation-triangle me-2"></i> Only the <strong>Stock Matrix</strong> file is supported for the Upload feature.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-upload text-muted me-2"></i>Upload Excel File</h5>
                            
                            <div class="upload-container text-center p-5 rounded-4 border-2 border-dashed border-primary-subtle bg-light-subtle mb-4">
                                <div class="mb-3">
                                    <i class="fas fa-cloud-upload-alt text-primary opacity-50" style="font-size: 4rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Select your completed template</h6>
                                <p class="small text-muted mb-4 text-center mx-auto" style="max-width: 300px;">
                                    Make sure your file is in .xlsx or .xls format and follows the Stock Matrix structure.
                                </p>
                                
                                <label for="bulk-file" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm cursor-pointer mb-3">
                                    Choose File
                                </label>
                                <input type="file" class="d-none" id="bulk-file" name="file" accept=".xlsx, .xls">
                                <div id="file-name-display" class="small fw-bold text-primary d-none mt-2"></div>
                            </div>

                            <button type="button" class="btn btn-success w-100 rounded-pill py-3 fw-bold shadow-sm" id="uploadBtn">
                                Start Stock Upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .hover-shadow { transition: all 0.3s ease; }
        .hover-shadow:hover { 
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            background-color: #fff !important;
            border-color: #0d6efd !important;
        }
        .transition { transition: all 0.3s ease; }
        .cursor-pointer { cursor: pointer; }
        .bg-light-subtle { background-color: #fcfdfe; }
        .border-dashed { border-style: dashed !important; }
        .bg-primary-subtle { background-color: #e7f1ff; }
        .bg-warning-subtle { background-color: #fff3cd; }
    </style>

    @push('scripts')
    <script>
    // File name display logic
    $('#bulk-file').on('change', function() {
        if(this.files.length) {
            $('#file-name-display').text('Selected: ' + this.files[0].name).removeClass('d-none');
        } else {
            $('#file-name-display').addClass('d-none');
        }
    });

    $('#uploadBtn').on('click', function() {
        var fileInput = $('#bulk-file')[0];
        if (!fileInput.files.length) {
            Swal.fire({
                title: 'No file selected',
                text: 'Please choose an Excel file to upload first.',
                icon: 'warning',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        var formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        // Initial Loading State
        Swal.fire({
            title: 'Uploading Stock Data',
            text: 'Please wait while we process your file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "{{ route('stocks.import') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Stock data has been synchronized successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.import) {
                    let msg = Array.isArray(xhr.responseJSON.errors.import) ? xhr.responseJSON.errors.import.join('<br>') : xhr.responseJSON.errors.import;
                    let successCount = xhr.responseJSON.errors.success_count || 0;
                    
                    Swal.fire({
                        title: 'Import Errors',
                        html: `<div class="text-start small" style="max-height: 200px; overflow-y: auto;">${msg}</div><hr><p class="text-success mb-0">${successCount} row(s) updated successfully.</p>`,
                        icon: 'error',
                        confirmButtonText: 'I will fix it'
                    });
                } else {
                    let msg = xhr.responseJSON?.message || 'Upload failed. Please check your file and try again.';
                    Swal.fire({
                        title: 'Upload Failed',
                        text: msg,
                        icon: 'error'
                    });
                }
            }
        });
    });
    </script>
    @endpush
@endsection