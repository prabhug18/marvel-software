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
                        <a href="{{ route('export.stocks') }}" target="_blank" download class="fw-bold text-decoration-underline">
                            download template <strong>here</strong>
                        </a>
                    </p>

                    <!-- File Input -->
                    <div class="mb-3">
                        <label for="bulk-file" class="form-label">Upload Excel File</label>
                        <input type="file" class="form-control" id="bulk-file" name="file" accept=".xlsx, .xls">
                    </div>

                    <!-- Upload Button -->
                    <div class="mt-4">
                        <button type="button" class="btn btn-success btn-lg" id="uploadBtn">Upload</button>
                        <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                            Bulk Stock Uploaded successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <script>
    $('#uploadBtn').on('click', function() {
        var fileInput = $('#bulk-file')[0];
        if (!fileInput.files.length) {
            alert('Please select a file.');
            return;
        }
        var formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ route('stocks.import') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Remove any previous import error alerts
                $('.alert-danger').remove();
                $('#successAlert').removeClass('d-none');
                setTimeout(function() {
                    document.querySelector('#successAlert').remove();
                }, 3000);
                setTimeout(function() {
                    window.location.reload();
                }, 4000);
            },
            error: function(xhr) {
                // Remove any previous import error alerts
                $('.alert-danger').remove();
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.import) {
                    let msg = Array.isArray(xhr.responseJSON.errors.import) ? xhr.responseJSON.errors.import.join('<br>') : xhr.responseJSON.errors.import;
                    let successCount = xhr.responseJSON.errors.success_count || 0;
                    let alertHtml = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">${msg}<br><span class='text-success'>${successCount} row(s) updated successfully.</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $(alertHtml).insertAfter('#uploadBtn');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    let alertHtml = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">${xhr.responseJSON.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $(alertHtml).insertAfter('#uploadBtn');
                } else {
                    alert('Upload failed. Please check your file and try again.');
                }
            }
        });
    });
    </script>    

@endsection