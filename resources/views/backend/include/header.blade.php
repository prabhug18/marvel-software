
<div id="mainHeader" class="header d-flex justify-content-between align-items-center px-4 py-2 bg-white border-bottom shadow-sm w-100">
    <h4 class="fw-bold mb-0">{{ $heading }}</h4>
    <div>
        <i id="notificationBell" class="bi bi-bell fs-4 me-3"></i>
        <i id="profileIcon" class="bi bi-person-circle fs-4" style="cursor: pointer;"></i>
    </div>
</div>

<!-- Profile Icon and Dropdown -->
<!-- Profile Icon and Dropdown Positioned Top Right -->
<div class="position-fixed top-0 end-0 p-3 z-3">
  <div class="position-relative d-inline-block">
    <!-- Dropdown Menu -->
    <div id="profileDropdown" class="dropdown-menu show shadow" style="display: none; position: absolute; right: 0; top: 50px;">
      <a class="dropdown-item" href="#">Welcome {{ Auth::user()->name }}</a>
      <a class="dropdown-item" href="#">Settings</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item text-danger" href="{{ url('logout') }}">Logout</a>
    </div>
  </div>
</div>


<!-- Right-side Log Panel -->
{{-- <div class="log-panel" id="logPanel">
    <div class="log-panel-header">
        <h3>Recent Activity Logs</h3>
        <span class="close-log" id="closeLogBtn">&times;</span>
    </div>
        @php
            $logs = App\Http\Controllers\GeneralController::logs();
        @endphp
    <ul class="log-list">
        @foreach($logs as $logsval)
            <li><span class="log-time">{{ $logsval->created_at->diffForHumans(); }}</span> - {{ $logsval->description }}</li>
        @endforeach
    </ul>
</div> --}}


<!-- Notification Panel -->
<div id="notificationPanel" class="position-fixed top-0 end-0 bg-white border shadow z-3 p-3" style="display: none; width: 300px; height: 100vh;">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
        <h5 class="mb-0">Notifications</h5>
        <button id="closeNotification" class="btn-close"></button>
    </div>
    @php
        $logs = App\Http\Controllers\GeneralController::logs();
    @endphp
    <div class="notification-body px-3 py-2">
        <!-- Notification 1 -->
        @foreach($logs as $logsval)
        @php

            $details = json_decode($logsval->details, true);
            if(is_array($details)) {
                unset($details['created_at'], 
                $details['status_id'], 
                $details['id'],
                $details['user_id'],
                $details['updated_at'],
                $details['deleted_at']);
            }

            $user = \App\Models\User::find($logsval->performed_by);
            
            // Get related names if present in details
            $customerName = null;
            if(isset($details['customer_id'])) {
                $customer = \App\Models\Customer::find($details['customer_id']);
                $customerName = $customer ? $customer->name : $details['customer_id'];
                $details['customer_id'] = $customerName;
            }

            $warehouseName = null;
            if(isset($details['warehouse_id'])) {
                $warehouse = \App\Models\Warehouse::find($details['warehouse_id']);
                $warehouseName = $warehouse ? $warehouse->name : $details['warehouse_id'];
                $details['warehouse_id'] = $warehouseName;
            }

            $userIdName = null;
            if(isset($details['user_id'])) {
                $userObj = \App\Models\User::find($details['user_id']);
                $userIdName = $userObj ? $userObj->name : $details['user_id'];
                $details['user_id'] = $userIdName;
            }
            
        @endphp

        <div class="mb-3 border-bottom pb-2">
            <h6> Page : {{ $logsval->log_type === 'warehouse' ? 'location' : $logsval->log_type }}</h6>
            <h6 class="mb-1">
                @if(is_array($details))
                    <ul class="mb-1">
                        @foreach($details as $key => $value)
                            <li><strong>{{ ucfirst($key) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
                        @endforeach
                    </ul>
                @else
                    <span>{{ $logsval->details }}</span>
                @endif
            </h6>
            <h6> Action : {{ $logsval->action }}</h6>
            <h6> Performed By : {{ $user ? $user->name : 'Unknown User' }}</h6>
            <small class="text-muted">{{ \Carbon\Carbon::parse($logsval->created_at)->diffForHumans(); }}</small>
           
        </div>
        @endforeach
    </div>
</div>