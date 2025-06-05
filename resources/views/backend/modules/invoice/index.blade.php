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

                <!-- Header Button -->
                <div class="row">
                    <div class="col text-end mb-3">
                        <a class="btn custom-orange-btn text-white" href="{{ url('/invoice/create') }}">
                            <i class="fas fa-user-plus me-2"></i>Add Invoice
                        </a>
                    </div>
                </div>

                <!-- Responsive Table -->
                <div class="table-responsive" id="responsive-table">
                    <table id="invoiceTable" class="table table-striped table-bordered align-middle text-center">
                        <thead class="custom-thead text-center align-middle">
                            <tr>
                            <th scope="col">S.NO</th>
                            <th scope="col">DATE</th>
                            <th scope="col">CUSTOMER</th>
                            <th scope="col">INVOICE NO</th>
                            <th scope="col">DESCRIPTION</th>
                            <th scope="col">AMOUNT</th>
                            <th scope="col">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $index => $invoice)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $invoice->date ?? $invoice->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $invoice->customer_name ?? ($invoice->customer->name ?? '-') }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->description ?? 'Product Added' }}</td>
                                    <td>₹{{ number_format($invoice->grand_total, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('invoice.show', $invoice->id) }}" title="View"><i class="fas fa-eye text-primary mx-1"></i></a>
                                        {{-- <a href="{{ route('invoice.edit', $invoice->id) }}" title="Edit"><i class="fas fa-edit text-warning mx-1"></i></a> --}}
                                        <a href="{{ url('payment/add-payment?invoice_id=' . $invoice->id) }}" title="Make TDS Payment"><i class="fas fa-rupee-sign text-success mx-1"></i></a>
                                        {{-- <form action="{{ route('invoice.destroy', $invoice->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete" onclick="return confirm('Are you sure to delete?')" style="border:none;background:none;padding:0;">
                                                <i class="fas fa-trash-alt text-danger mx-1"></i>
                                            </button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                </div>
            </div>
        </div>
        
    </main>
   
@endsection