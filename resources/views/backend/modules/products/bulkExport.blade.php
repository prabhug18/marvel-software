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
                        <button type="button" class="btn btn-success btn-lg" id="uploadBtn">Upload</button>
                        <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                            Bulk Product Uploaded successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>

                    <script>
                    $(document).ready(function() {
                        $('#uploadBtn').on('click', function() {
                            var fileInput = $('#bulk-file')[0];
                            var file = fileInput.files[0];
                            var formData = new FormData();
                            formData.append('file', file);
                            // Clear previous errors
                            $('#file-error').text('');
                            $('#bulk-file').removeClass('is-invalid');
                            $.ajax({
                                url: "{{ route('product.import') }}",
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    // Remove any previous import error alerts
                                    $('.alert-danger').remove();
                                    // Only show success if there is no error in response
                                    if (response && response.errors && response.errors.import) {
                                        let msg = Array.isArray(response.errors.import) ? response.errors.import.join('<br>') : response.errors.import;
                                        let alertHtml = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                                        $(alertHtml).insertAfter('#uploadBtn');
                                        return;
                                    }
                                    $('#successAlert').removeClass('d-none');
                                    setTimeout(function() {
                                        $('#successAlert').addClass('d-none');
                                    }, 3000);
                                    $('#bulk-file').val('');
                                },
                                error: function(xhr) {
                                    // Remove any previous import error alerts
                                    $('.alert-danger').remove();
                                    if (xhr.status === 422) {
                                        var errors = xhr.responseJSON.errors;
                                        if(errors.file) {
                                            $('#file-error').text(errors.file[0]);
                                            $('#bulk-file').addClass('is-invalid');
                                        }
                                        // Show import errors (category/brand not found)
                                        if(errors.import) {
                                            let msg = Array.isArray(errors.import) ? errors.import.join('<br>') : errors.import;
                                            let alertHtml = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                                            $(alertHtml).insertAfter('#uploadBtn');
                                        }
                                    } else if (xhr.status === 500) {
                                        alert(xhr.responseJSON.error || 'An error occurred. Please try again.');
                                    } else {
                                        alert('An unexpected error occurred. Please try again.');
                                    }
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