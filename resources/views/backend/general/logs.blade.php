@extends('layouts.backend')

@section('content')
<!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')

        <!-- Push content below fixed header -->
        <div style="padding-top: 30px;"></div>

        <div class="container-fluid px-3">
            <div class="card shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <div class="p-4 border rounded shadow-sm" style="border-left: 3px solid orange; max-width: 600px; margin: 0 auto;">
                        <h5 class="text-primary text-start">All Logs</h5>

                        @if(isset($logs) && count($logs))
                            @foreach($logs as $log)
                                <div class="text-start mt-4">
                                    <strong>{{ ucfirst($log->action ?? $log->log_type) }}</strong><br>
                                    <small>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('Y-m-d h:i A') : '' }}</small><br>
                                    @if($log->log_type === 'user')
                                        User: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'customer')
                                        Customer: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'warehouse')
                                        Warehouse: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'category')
                                        Category: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'brand')
                                        Brand: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'invoice')
                                        Invoice: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @elseif($log->log_type === 'stock')
                                        Stock: <strong>{{ $log->performed_by }}</strong> — {{ $log->details }}
                                    @else
                                        {{ $log->details }}
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center mt-4">No logs found.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </main>  

@endsection