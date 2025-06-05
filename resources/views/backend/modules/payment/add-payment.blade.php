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
                <form class="row g-4 justify-content-center" style="max-width: 900px; margin: 0 auto;" id="paymentForm" method="POST" action="{{ url('payment/add-payment') }}">
                @csrf
                <!-- Customer Name -->
                <div class="col-md-6">
                    <label for="customerName" class="form-label">CUSTOMER NAME <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter Customer Name" required />
                </div>

                <!-- Invoice No -->
                <div class="col-md-6">
                    <label for="invoiceNo" class="form-label">INVOICE NO <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" placeholder="Enter Invoice Number" required />
                </div>

                <!-- Invoice Amount -->
                <div class="col-md-6">
                    <label for="invoiceAmount" class="form-label">INVOICE AMOUNT <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="grand_total" name="grand_total" placeholder="Enter Invoice Amount" required />
                </div>

                <!-- Balance Amount -->
                <div class="col-md-6">
                    <label for="balanceAmount" class="form-label">BALANCE AMOUNT <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="balance_amount" name="balance_amount" placeholder="Enter Balance Amount" readonly />
                </div>

                <!-- Paid Amount -->
                <div class="col-md-6">
                    <label for="paidAmount" class="form-label">PAID AMOUNT <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="paid_amount" name="paid_amount" placeholder="Enter Paid Amount" required />
                </div>

                <!-- Payment Mode -->
                <div class="col-md-6">
                    <label for="paymentMode" class="form-label">PAYMENT MODE <span class="text-danger">*</span></label>
                    <select class="form-select" id="payment_mode" name="payment_mode" required>
                    <option selected disabled>Select Payment Mode</option>
                    <option>Cheque</option>
                    <option>Cash</option>
                    <option>Internet Banking</option>
                    <option>UPI</option>
                    <option>Paper Finance</option>
                    </select>
                </div>

                <!-- Payment Date -->
                <div class="col-md-6">
                    <label for="paymentDate" class="form-label">PAYMENT DATE <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" required />
                </div>

                <!-- Description -->
                <div class="col-md-12">
                    <label for="description" class="form-label">DESCRIPTION</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description here"></textarea>
                </div>

                <!-- Hidden Invoice ID -->
                <input type="hidden" id="invoice_id" name="invoice_id" value="" />
                <input type="hidden" id="customer_id" name="customer_id" value="" />

                <!-- Submit Button -->
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary px-5 py-2">
                    <i class="fas fa-check"></i> Save
                    </button>
                </div>
                </form>
            </div>
            </div>
        </div>
        
    </main>

<!-- jQuery CDN for autofill functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Autofill script loaded');
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceId = urlParams.get('invoice_id');
    console.log('invoiceId from URL:', invoiceId);
    if (invoiceId) {
        $('#invoice_id').val(invoiceId); // set hidden field for backend
        // Fetch invoice details and payment info via AJAX (single call)
        $.ajax({
            url: '/api/invoice-details',
            type: 'GET',
            data: { invoice_id: invoiceId },
            dataType: 'json',
            success: function(data) {
                console.log('API response:', data);
                if (data && data.success) {
                    $('#customer_name').val(data.customer_name);
                    $('#invoice_number').val(data.invoice_number);
                    $('#grand_total').val(data.grand_total);
                    $('#balance_amount').val(data.balance_amount);
                    // Set hidden fields for backend
                    if (data.customer_id) {
                        $('#customer_id').val(data.customer_id);
                    } else {
                        $('#customer_id').val(''); // Explicitly clear if not found
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
            }
        });
    }

    // Auto-calculate balance_amount on paid_amount change
    $('#paid_amount').on('input', function() {
        const grandTotal = parseFloat($('#grand_total').val()) || 0;
        const paidAmount = parseFloat($('#paid_amount').val()) || 0;
        // Optionally, add previous payments here if needed
        const balance = grandTotal - paidAmount;
        $('#balance_amount').val(balance >= 0 ? balance : 0);
    });

    // AJAX form submit for payment
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    // Show success message, then redirect
                    alert('Payment added successfully! Redirecting to invoice list...');
                    setTimeout(function() {
                        window.location.href = '/invoice';
                    }, 1200);
                } else {
                    alert('Error: ' + (response && response.message ? response.message : 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error saving payment: ' + xhr.responseText);
            }
        });
    });
});
</script>

@endsection