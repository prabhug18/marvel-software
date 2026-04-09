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
                    <a href="{{ url('invoice') }}" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Invoices</a>
                    <h4 class="mb-3">{{ $heading ?? 'Payment Reconciliation' }} for Invoice</h4>
                    @php
                        $invoiceId = request('invoice_id');
                        $invoice = \App\Models\Invoice::with('customer')->find($invoiceId);
                        $payments = $invoice ? \App\Models\Payment::where('invoice_id', $invoiceId)->orderBy('payment_date')->get() : collect();
                    @endphp
                    @if(!$invoice)
                        <div class="alert alert-danger">Invoice not found.</div>
                    @else
                        <div class="mb-2">
                            <strong>Customer:</strong> {{ $invoice->customer->name ?? '-' }}<br>
                            <strong>Invoice Number:</strong> {!! (string) $invoice->getOriginal('invoice_number') !!}<br>
                            <strong>Grand Total:</strong> ₹{{ number_format($invoice->grand_total, 2) }}
                        </div>
                        <!-- Add Payment Form -->
                        <div class="mb-4">
                            <h5 class="mb-2">Add Payment</h5>
                            <form id="addPaymentForm">
                                @csrf
                                <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">
                                <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
                                <input type="hidden" name="customer_name" value="{{ $invoice->customer->name ?? '' }}">
                                <input type="hidden" name="grand_total" value="{{ $invoice->grand_total }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Payment Date</label>
                                        <input type="date" name="payment_date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Amount (₹)</label>
                                        <input type="number" name="paid_amount" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Payment Mode</label>
                                        <select name="payment_mode" class="form-select" required>
                                            <option value="">Select Mode</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Card">Card</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Bank">Bank</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" id="addPaymentBtn" class="btn btn-primary">Add Payment</button>
                                           
                                        </div>
                                    </div>
                                    <!-- description removed; default will be set to 'Invoice Payment' on save -->
                                </div>
                            </form>
                        </div>

                        <!-- Pending Payments (client-side) -->
                        <div class="mb-3">
                            <h5 class="mb-2">Pending Payments (Unsaved)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="pendingPaymentsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Payment Date</th>
                                            <th>Amount</th>
                                            <th>Mode</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="6" class="text-center text-muted">No pending payments</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <button id="submitPendingPayments" class="btn btn-success">Submit All Payments</button>
                                <button id="clearPendingPayments" class="btn btn-secondary">Clear</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Payment Date</th>
                                        <th>Paid Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Description</th>
                                        <th>Recorded By</th>
                                        <th>Confirmed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $i => $payment)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                            <td>₹{{ number_format($payment->paid_amount, 2) }}</td>
                                            <td>{{ $payment->payment_mode }}</td>
                                            <td>{{ $payment->description }}</td>
                                            <td>{{ $payment->user->name ?? 'User#'.$payment->user_id }}</td>
                                            <td>
                                                @if($payment->is_confirmed)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$payment->is_confirmed)
                                                <form method="POST" action="{{ url('payment/confirm') }}" style="display:inline">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">Confirm</button>
                                                </form>
                                                @else
                                                <button class="btn btn-sm btn-success" disabled>Confirmed</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center">No payments found for this invoice.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <strong>Total Paid:</strong> ₹{{ number_format($payments->sum('paid_amount'), 2) }}<br>
                            <strong>Balance:</strong> ₹{{ number_format($invoice->grand_total - $payments->sum('paid_amount'), 2) }}
                        </div>
                        <form method="POST" action="{{ url('payment/mark-reconciliation') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="invoice_id" value="{{ $invoiceId ?? request('invoice_id') }}">
                            <button type="submit" class="btn btn-success" @if($payments->count() == 0 || $payments->where('is_confirmed', false)->count() > 0) disabled @endif>
                                <i class="fas fa-check-circle me-1"></i> Mark Reconciliation as Done
                            </button>
                        </form>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('redirect_invoice'))
                        <script>
                            setTimeout(function() {
                                window.location.href = "{{ url('invoice') }}";
                            }, 2000);
                        </script>
                    @endif
                </div>
            </div>
        </div>
        
    </main>

    <style>
    @media (max-width: 767.98px) {
      .table-responsive {
        font-size: 0.95rem;
      }
      table.table, table.table thead, table.table tbody, table.table th, table.table tr {
        display: block;
        width: 100%;
      }
      table.table thead {
        display: none;
      }
      table.table tr {
        margin-bottom: 1.2rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        background: #fff;
        padding: 0.5rem;
      }
      table.table td {
        display: block;
        padding: 0.5rem 0.5rem 0.5rem 0.5rem;
        border: none;
        border-bottom: 1px solid #eee;
        position: relative;
        min-height: 40px;
        word-break: break-word;
        white-space: normal;
        background: #fff;
      }
      table.table td:before {
        content: attr(data-label);
        display: block;
        font-weight: bold;
        color: #f47820;
        margin-bottom: 0.2rem;
        font-size: 0.98em;
        white-space: pre-line;
        word-break: break-word;
      }
      table.table td:last-child {
        border-bottom: none;
      }
    }
    </style>
    <script>
    function setPaymentTableDataLabels() {
        var headers = Array.from(document.querySelectorAll('table.table thead th')).map(th => th.innerText.trim());
        document.querySelectorAll('table.table tbody tr').forEach(function(row) {
            row.querySelectorAll('td').forEach(function(td, i) {
                td.setAttribute('data-label', headers[i] || '');
            });
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        setPaymentTableDataLabels();
    });
    // Pending payments client-side queue and bulk submit
    document.addEventListener('DOMContentLoaded', function() {
        var addForm = document.getElementById('addPaymentForm');
        if (!addForm) return;
        var addBtn = document.getElementById('addPaymentBtn');
        var submitBtn = document.getElementById('submitPendingPayments');
        var clearBtn = document.getElementById('clearPendingPayments');
        var pendingTable = document.getElementById('pendingPaymentsTable').querySelector('tbody');
        var pendingPayments = [];
        // server-side totals
        var existingPaid = parseFloat('{{ number_format($payments->sum('paid_amount'), 2, '.', '') }}') || 0;
        var grandTotal = parseFloat('{{ number_format($invoice->grand_total, 2, '.', '') }}') || 0;

        function renderPending() {
            pendingTable.innerHTML = '';
            if (pendingPayments.length === 0) {
                pendingTable.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No pending payments</td></tr>';
                return;
            }
            pendingPayments.forEach(function(p, idx) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + (idx+1) + '</td>' +
                    '<td>' + p.payment_date + '</td>' +
                    '<td>₹' + parseFloat(p.paid_amount).toFixed(2) + '</td>' +
                    '<td>' + p.payment_mode + '</td>' +
                    '<td>' + (p.description || '') + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-danger btn-remove" data-idx="'+idx+'">Remove</button></td>';
                pendingTable.appendChild(tr);
            });
        }

        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!addBtn) return;
            var fd = new FormData(addForm);
            var entry = {
                payment_date: fd.get('payment_date'),
                paid_amount: fd.get('paid_amount'),
                payment_mode: fd.get('payment_mode'),
                // description textarea removed; default to 'Invoice Payment'
                description: 'Invoice Payment'
            };
                    // Basic client-side validation
                    if (!entry.paid_amount || isNaN(parseFloat(entry.paid_amount))) {
                        Swal.fire('Error', 'Please enter a valid amount', 'error');
                        return;
                    }
                    if (!entry.payment_mode) {
                        Swal.fire('Error', 'Please select payment mode', 'error');
                        return;
                    }
                    // Prevent adding amount that would exceed invoice total (considering saved + pending)
                    var paidSoFar = existingPaid + pendingPayments.reduce(function(s, it){ return s + parseFloat(it.paid_amount || 0); }, 0);
                    var entryAmount = parseFloat(entry.paid_amount || 0);
                    if (paidSoFar + entryAmount > grandTotal + 0.0001) {
                        Swal.fire('Warning', 'Amount exceeds invoice balance. Cannot add payment larger than remaining invoice amount.', 'warning');
                        return;
                    }
            pendingPayments.push(entry);
            renderPending();
            addForm.reset();
            // reset date to today
            addForm.querySelector('input[name="payment_date"]').value = new Date().toISOString().slice(0,10);
        });

        // Remove pending item (event delegation)
        pendingTable.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('btn-remove')) {
                var idx = parseInt(e.target.getAttribute('data-idx'));
                if (!isNaN(idx)) {
                    pendingPayments.splice(idx, 1);
                    renderPending();
                }
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Clear all pending payments?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        pendingPayments = [];
                        renderPending();
                    }
                });
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                if (pendingPayments.length === 0) {
                    alert('No pending payments to submit');
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.innerText = 'Submitting...';

                var tokenEl = addForm.querySelector('input[name="_token"]');
                var token = tokenEl ? tokenEl.value : '';
                var payload = {
                    invoice_id: addForm.querySelector('input[name="invoice_id"]').value,
                    payments: pendingPayments
                };

                fetch('{{ url("/payment/bulk-store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                }).then(function(res) {
                    return res.json().then(function(json) {
                        if (!res.ok) throw json;
                        return json;
                    });
                }).then(function(data) {
                    if (data && data.success) {
                        Swal.fire('Saved!', 'Payments saved successfully', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', (data && data.message) ? data.message : 'Failed to save payments', 'error');
                    }
                }).catch(function(err) {
                    var msg = 'Error saving payments.';
                    if (err && err.message) msg = err.message;
                    Swal.fire('Error', msg, 'error');
                }).finally(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Submit All Payments';
                });
            });
        }

        // initial render
        renderPending();
    });
    </script>
@endsection