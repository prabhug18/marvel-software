@extends('layouts.backend')
<!-- Add in your layout or before </body> -->

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <div class="main-content" id="mainContent">
        @include('backend.include.header')

        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;"></div>
        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <!-- Description & Template Download -->
                    <p class="mb-3">
                        Excel Upload –
                        <a href="{{ route('product.export') }}" target="_blank" download class="fw-bold text-decoration-underline">
                            download template <strong>here</strong>
                        </a>
                    </p>
                    <!-- File Input -->
                    <div class="mb-3">
                        <label for="bulk-file" class="form-label">Upload Excel File</label>
                        <input type="file" class="form-control" id="bulk-file" name="file" accept=".xlsx, .xls">
                        <div class="invalid-feedback" id="file-error"></div>
                    </div>

                    <!-- Upload Button -->
                    <div class="mt-4">
                        <button type="button" class="btn btn-success btn-lg px-4" id="uploadBtn">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                    </div>

                    <!-- Import Preview Modal -->
                    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="previewModalLabel">
                                        <i class="fas fa-eye me-2"></i>Import Preview (Top 50 rows)
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="table-responsive" style="max-height: 450px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light sticky-top">
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Brand</th>
                                                    <th>Model</th>
                                                    <th>Model No</th>
                                                    <th>Price</th>
                                                    <th>Tax %</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewTableBody">
                                                <!-- Preview rows will be injected here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 bg-light border-top">
                                        <span id="rowStats" class="text-muted small"></span>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Reject / Cancel</button>
                                    <button type="button" class="btn btn-success" id="confirmImportBtn">
                                        Confirm & Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    $(document).ready(function() {
                        let preservedFile = null;

                        // 1. Click Upload -> Show Preview
                        $('#uploadBtn').on('click', function() {
                            var fileInput = $('#bulk-file')[0];
                            var file = fileInput.files[0];
                            if(!file) {
                                Swal.fire('Error', 'Please select a file first.', 'error');
                                return;
                            }
                            
                            preservedFile = file; // Save file for the second step
                            var formData = new FormData();
                            formData.append('file', file);

                            // Loading state
                            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Checking...');

                            $.ajax({
                                url: "{{ route('product.import.preview') }}",
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                success: (response) => {
                                    $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload File');
                                    
                                    let html = '';
                                    response.preview.forEach(row => {
                                        let statusBadge = row.is_valid 
                                            ? '<span class="badge bg-success-subtle text-success">Valid</span>' 
                                            : `<span class="badge bg-danger-subtle text-danger" title="${row.status_msg}">Error</span>`;
                                        
                                        html += `
                                            <tr class="${row.is_valid ? '' : 'table-danger-subtle'}">
                                                <td>${row.category}</td>
                                                <td>${row.brand}</td>
                                                <td>${row.model}</td>
                                                <td>${row.model_no}</td>
                                                <td>${row.price}</td>
                                                <td>${row.tax_percentage}%</td>
                                                <td>${statusBadge}</td>
                                            </tr>
                                        `;
                                    });

                                    $('#previewTableBody').html(html);
                                    $('#rowStats').text(`Showing ${response.preview.length} of ${response.total_rows} rows found in file.`);
                                    
                                    // If any invalid rows, warn user
                                    let hasInvalids = response.preview.some(r => !r.is_valid);
                                    if(hasInvalids) {
                                        $('#confirmImportBtn').addClass('disabled').attr('title', 'Please fix errors in Excel before importing');
                                    } else {
                                        $('#confirmImportBtn').removeClass('disabled').attr('title', '');
                                    }

                                    $('#previewModal').modal('show');
                                },
                                error: (xhr) => {
                                    $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload File');
                                    let msg = xhr.responseJSON?.error || 'Failed to parse file.';
                                    Swal.fire('Error', msg, 'error');
                                }
                            });
                        });

                        // 2. Click Confirm -> Perform Actual Import
                        $('#confirmImportBtn').on('click', function() {
                            if($(this).hasClass('disabled')) return;

                            var formData = new FormData();
                            formData.append('file', preservedFile);

                            // Loading state
                            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Importing...');

                            $.ajax({
                                url: "{{ route('product.import') }}",
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                success: function(response) {
                                    $('#previewModal').modal('hide');
                                    Swal.fire('Success!', 'Products imported successfully.', 'success');
                                    $('#bulk-file').val('');
                                    $('#confirmImportBtn').prop('disabled', false).text('Confirm & Upload');
                                },
                                error: function(xhr) {
                                    $('#confirmImportBtn').prop('disabled', false).text('Confirm & Upload');
                                    let msg = 'Import failed. Check logs.';
                                    if(xhr.status === 422) {
                                        let errors = xhr.responseJSON.errors;
                                        msg = errors.import ? errors.import.join('\n') : 'Validation failed.';
                                    }
                                    Swal.fire('Import Failed', msg, 'error');
                                }
                            });
                        });
                    });
                    </script>

                </div>
            </div>
        </div>
    </div> 

@endsection