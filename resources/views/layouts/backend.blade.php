<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>PHOENIX INFOWAYS</title>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> 

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- font-awesome icons (removed integrity and crossorigin to fix digest error) -->
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery (required by Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- bootstrap icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />  
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>  
</head>
<body>

    @yield('content')
   
    <!-- Sidebar loading script -->
    
    <script src="{{ asset('assets/js/script.js') }}"></script>   
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $('#customerTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            lengthChange: true,
            pageLength: 10,
            language: {
                searchPlaceholder: "Search details...",
                search: "", // Remove "Search:" label
                paginate: {
                previous: "Previous",
                next: "Next"
                }
        },
        dom: '<"top"lf>rt<"bottom"ip><"clear">' 
        // Explanation:
        // l = length menu
        // f = filtering input (search box)
        // r = processing display element
        // t = table
        // i = table info
        // p = pagination
        // clear = div clear floats
        });

    </script>

    @stack('scripts')
</body>
</html>
