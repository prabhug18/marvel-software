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
                    <form id="ajaxPaymentForm">
                        <div class="row">
                            <!-- Customer Name -->
                            <div class="col-md-6 mb-3 position-relative">
                                <label for="customerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Enter Name / Mobile / Email" required autocomplete="off">
                                <div id="customerSuggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none; top: 100%; left: 0;"></div>
                            </div>

                            <!-- Balance Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="balanceAmount" class="form-label">Balance Amount</label>
                                <input type="text" class="form-control" id="balanceAmount" name="balance_amount" placeholder="" readonly>
                            </div>

                            <!-- Paid Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="paidAmount" class="form-label">Paid Amount</label>
                                <input type="number" class="form-control" id="paidAmount" name="paid_amount" required>
                            </div>

                            <!-- Payment Mode -->
                            <div class="col-md-6 mb-3">
                                <label for="paymentMode" class="form-label">Payment Mode</label>
                                <select class="form-select" id="paymentMode" name="payment_mode" required>
                                    <option value="">Select Mode</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="netbanking">Net Banking</option>
                                </select>
                            </div>

                            <!-- Payment Date -->
                            <div class="col-md-6 mb-3">
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                            </div>

                            <!-- Empty column to balance row -->
                            <div class="col-md-6 mb-3"></div>

                            <!-- Description full row -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-lg">Save</button>

                            <!-- Bootstrap Alert (Initially hidden) -->
                            <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                                Payment Added successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>

 
  <script>
  function showAlert() {
    const alertBox = document.getElementById("successAlert");
    alertBox.classList.remove("d-none"); // show the alert
  }
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    
$(document).ready(function() {   
    var $customerInput = $('#customerName');
    var $suggestions = $('#customerSuggestions');
    if ($customerInput.length === 0) {
        console.error('customerName input not found!');
        return;
    }
    $customerInput.on('input', function() {
        const val = $(this).val();
        $suggestions.empty();
        if (val.length > 0) {
            $.ajax({
                url: '/customer-search', // Update if your Laravel public path is different
                type: 'GET',
                data: { q: val },
                dataType: 'json',
                success: function(customers) {
                    if (Array.isArray(customers) && customers.length > 0) {
                        $suggestions.empty();
                        customers.forEach(function(c) {
                            if (!c || !c.name) return;
                            $suggestions.append('<button type="button" class="list-group-item list-group-item-action text-start" data-name="'+c.name+'" data-mobile="'+c.mobile_no+'" data-email="'+c.email+'">'+c.name+' ('+c.mobile_no+', '+c.email+')</button>');
                        });
                        $suggestions.show();
                    } else {
                        $suggestions.html('<div class="list-group-item">No customers found</div>').show();
                    }
                },
                error: function(xhr, status, error) {
                    $suggestions.html('<div class="list-group-item">Error fetching customers</div>').show();
                }
            });
        } else {
            $suggestions.hide();
        }
    });
    $(document).on('click', '#customerSuggestions button', function() {
        $customerInput.val($(this).data('name'));
        $suggestions.hide();
        // Fetch balance amount for selected customer
        const customerName = $(this).data('name');
        const customerMobile = $(this).data('mobile');
        const customerEmail = $(this).data('email');
        // You can use any unique identifier, but fallback to name/mobile/email for now
        $.ajax({
            url: '/customer-balance',
            type: 'GET',
            data: { name: customerName, mobile: customerMobile, email: customerEmail },
            dataType: 'json',
            success: function(res) {
                if(res && typeof res.balance !== 'undefined') {
                    $('#balanceAmount').val(res.balance);
                } else {
                    $('#balanceAmount').val('');
                }
            },
            error: function() {
                $('#balanceAmount').val('');
            }
        });
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#customerName, #customerSuggestions').length) {
            $suggestions.hide();
        }
    });
    // AJAX form submit
    $('#ajaxPaymentForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = {
            customer_name: $('#customerName').val(),
            balance_amount: $('#balanceAmount').val(),
            paid_amount: $('#paidAmount').val(),
            payment_mode: $('#paymentMode').val(),
            payment_date: $('#paymentDate').val(),
            description: $('#description').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        // Simple validation
        if (!data.customer_name || !data.paid_amount || !data.payment_mode || !data.payment_date) {
            alert('Please fill all required fields.');
            return;
        }
        $.ajax({
            url: '/payment/ajax-store',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#successAlert').removeClass('d-none');
                    form[0].reset();
                    setTimeout(function() {
                        window.location.href = '/payment/view';
                    }, 1000); // 1 second delay for alert
                } else {
                    alert('Failed to add payment.');
                }
            },
            error: function(xhr) {
                if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let msg = Object.values(xhr.responseJSON.errors).map(function(arr){ return arr.join(', '); }).join('\n');
                    alert(msg);
                } else {
                    let msg = 'Error saving payment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            }
        });
    });
});
</script>

   </div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endpush



