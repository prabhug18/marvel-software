@php
if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
        ];
        if (!is_numeric($number)) {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            return false;
        }
        if ($number < 0) {
            return $negative . numberToWords(abs($number));
        }
        $string = '';
        if ($number < 21) {
            $string = $dictionary[$number];
        } elseif ($number < 100) {
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
        } elseif ($number < 1000) {
            $hundreds  = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . numberToWords($remainder);
            }
        } else {
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
        }
        return $string;
    }
}
@endphp

@extends('layouts.backend')

@section('content')
    <!-- Sidebar will be injected here -->
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="container-fluid px-3 d-flex justify-content-center align-items-center" style="min-height: 100vh; padding-top: 30px;">
            <div class="invoice-box w-100" id="invoice" style="max-width: 900px; margin: 0 auto;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="mb-0" style="font-size:16px;">Tax Invoice</h1>
                @if(!empty($invoice->warehouse->image))
                    <img src="{{ asset($invoice->warehouse->image) }}" alt="Warehouse Logo" style="max-height: 80px; max-width: 150px; margin-bottom: 5px;">
                @endif
                </div>
                <div class="container border p-3 mb-4 rounded bg-light">
                        
                <div class="row">
                    <!-- Column 1: Company Info -->
                    <div class="col-md-4 border-end">
                    <h6 class="fw-bold" style="color: #f47820;">{{ $invoice->warehouse->company_name ?? $invoice->warehouse->name ?? '-' }}</h6>
                    @if(!empty($invoice->warehouse->sub_heading))
                        <p class="mb-1 small">{{ $invoice->warehouse->sub_heading }}</p>
                    @endif
                    @if(!empty($invoice->warehouse->address))
                        <p class="mb-1 small">{{ $invoice->warehouse->address }}</p>
                    @endif
                    @if(!empty($invoice->warehouse->gstn_uin))
                        <p class="mb-1 small">GSTIN/UIN: {{ $invoice->warehouse->gstn_uin }}</p>
                    @endif
                    @if(!empty($invoice->warehouse->mobile))
                        <p class="mb-1 small">Mobile: {{ $invoice->warehouse->mobile }}</p>
                    @endif
                    @if(!empty($invoice->warehouse->email))
                        <p class="mb-1 small">Email: <a href="mailto:{{ $invoice->warehouse->email }}">{{ $invoice->warehouse->email }}</a></p>
                    @endif
                   
                    </div>

                    <!-- Column 2: Billing Info -->
                    <div class="col-md-4 border-end">
                        <h6 class="fw-bold" style="color: #f47820;">Bill To</h6>
                        <p class="mb-1 small"><strong>Name:</strong> {{ $invoice->customer->name ?? '-' }}</p>
                        <p class="mb-1 small"><strong>Address:</strong> 
                            {{ $invoice->customer->address ?? '-' }}
                            @if(is_array($invoice->customer->city))
                                , {{ $invoice->customer->city['name'] ?? '-' }}
                            @elseif(is_object($invoice->customer->city))
                                , {{ $invoice->customer->city->name ?? '-' }}
                            @else
                                , {{ $invoice->customer->city ?? '-' }}
                            @endif
                            @if(!empty($invoice->customer->pincode))
                                - {{ $invoice->customer->pincode }}
                            @endif
                        </p>
                        <p class="mb-1 small"><strong>Phone:</strong> {{ $invoice->customer->mobile_no ?? '-' }}</p>
                        <p class="mb-1 small"><strong>Email:</strong> {{ $invoice->customer->email ?? '-' }}</p>
                        <p class="mb-0 small"><strong>State:</strong> 
                            @if(is_array($invoice->customer->state))
                            {{ $invoice->customer->state['name'] ?? '-' }}
                            @elseif(is_object($invoice->customer->state))
                            {{ $invoice->customer->state->name ?? '-' }}
                            @else
                            {{ $invoice->customer->state ?? '-' }}
                            @endif
                            {{ $invoice->customer->state_code ? ' (Code: ' . $invoice->customer->state_code . ')' : '' }}
                        </p>
                        </div>

                    <!-- Column 3: Invoice Info -->
                    <div class="col-md-4">
                    <h6 class="fw-bold" style="color: #f47820;">Invoice Details</h6>
                    <p class="mb-1 small"><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
                    <p class="mb-1 small"><strong>Date:</strong> {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : ($invoice->created_at ? $invoice->created_at->format('d-m-Y') : '-') }}</p>
                    {{-- <p class="mb-0 small"><strong>Dispatched Through:</strong> {{ $invoice->dispatch_mode ?? '-' }}</p> --}}
                    </div>
                </div>
                </div>

                <div class="container mt-3">
                <h3 class="mb-1 fw-bold">Invoice Details</h3>

                <!-- Invoice Table -->
                <div class="table-responsive" id="responsive-table">
                <table id="view-invoice" class="table table-striped table-bordered align-middle">
                    <thead class="custom-thead text-center">
                    <tr>
                        <th>S.No</th>
                        <th>Description</th>
                        <th>HSN</th>                       
                        <th>Qty</th>
                        <th class="text-end">Rate</th>
                        <th>Tax %</th>
                        <th class="text-end">Tax Amt</th>
                        <th class="text-end">Total Amt</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->items as $i => $item)
                    <tr>
                    <td data-label="S.No"><span class="mobile-value">{{ $i+1 }}</span></td>
                    <td data-label="Description"><span class="mobile-value">{{ $item->product_name ?? '-' }} <br> s/n: {{ $item->serial_no ?? '-' }}</span></td>
                    <td data-label="HSN"><span class="mobile-value">{{ $item->product->hsn_code ?? '-' }}</span></td>
                    
                    <td data-label="Qty"><span class="mobile-value">{{ $item->qty }}</span></td>
                    <td data-label="Rate" class="text-end"><span class="mobile-value">{{ number_format($item->unit_price, 2) }}</span></td>
                    <td data-label="Tax %"><span class="mobile-value">{{ $item->tax_percentage ? $item->tax_percentage.'%' : '-' }}</span></td>
                    <td data-label="Tax Amt" class="text-end"><span class="mobile-value">{{ number_format($item->tax_amount ?? 0, 2) }}</span></td>
                    <td data-label="Total Amt" class="text-end"><span class="mobile-value">{{ number_format($item->total, 2) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>

                <!-- Summary Row -->
                <div class="row mt-2 g-3">

                <!-- CGST & SGST or IGST Box -->
                @php
                  $stateId = is_array($invoice->customer->state) ? ($invoice->customer->state['id'] ?? null) : (is_object($invoice->customer->state) ? ($invoice->customer->state->id ?? null) : null);
                @endphp
                
                  <!-- CGST Box -->
                  <div class="col-md-4">
                    <div
                      class="p-2 rounded-3 h-100"
                      style="background: linear-gradient(to right, #fef7f1, #fff2e6);           ">
                      <h6 class="fw-bold text-warning mb-3">
                        <i class="fas fa-wallet me-2"></i>Payment Info
                      </h6>
                      <p class="mb-2">
                        <strong class="text-secondary">Payment Modes:</strong>
                        {{ $invoice->payments->pluck('payment_mode')->unique()->filter()->implode(' + ') ?: '-' }}
                      </p>
                      <div class="mb-2 ms-3">
                        @foreach($invoice->payments->groupBy('payment_mode') as $mode => $payments)
                          <p class="mb-1">
                            <strong class="text-secondary">{{ $mode }}:</strong> ₹
                            {{ number_format($payments->sum('paid_amount'), 2) }}
                          </p>
                        @endforeach
                      </div>
                      <p class="mb-2">
                        <strong class="text-secondary">Total Paid:</strong> ₹
                        {{ number_format($invoice->payments->sum('paid_amount'), 2) }}
                      </p>
                      <p>
                        <strong class="text-secondary">Balance Due:</strong>
                        <span class="text-danger fw-bold">₹ {{ number_format(($invoice->grand_total ?? 0) - $invoice->payments->sum('paid_amount'), 2) }}</span>
                      </p>
                    </div>
                  </div>
                  @if($stateId == 35)
                  <!-- SGST Box -->
                  <div class="col-md-2">&nbsp;</div>
                  <div class="col-md-2">
                    <div class="border rounded p-3 shadow-sm bg-light mt-1">
                      <div class="d-flex justify-content-between mb-2 fw-bold">
                        <span class="text-primary">CGST: </span>
                        <span>₹{{ number_format($invoice->cgst ?? 0, 2) }}</span>                        
                      </div>                     
                    </div>
                    <div class="border rounded p-3 shadow-sm bg-light mt-4">
                      <div class="d-flex justify-content-between mb-2 fw-bold">                        
                        <span class="text-success">SGST: </span>
                        <span>₹{{ number_format($invoice->sgst ?? 0, 2) }}</span>
                      </div>                      
                    </div>
                  </div>
                  
                @else
                  <div class="col-md-2">&nbsp;</div>
                  <div class="col-md-2">
                    <div class="border rounded p-3 shadow-sm bg-light">
                      <div class="d-flex justify-content-between mb-2 fw-bold text-danger">                        
                        <span>IGST: </span>
                        <span>₹{{ number_format($invoice->igst?? 0, 2) }}</span>
                      </div>
                      
                    </div>
                    
                  </div>
                  <div class="col-md-4 ">&nbsp;</div>
                @endif

                <!-- Grand Total Box -->
                <div class="col-md-4 ">
                    <div class="border rounded p-4 shadow-sm bg-warning bg-opacity-25">
                        <div class="text-end">
                        <p class="fw-bold text-dark fs-6 mb-2">Grand Total</p>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold text-dark">Subtotal</span>
                            <span class="fw-semibold text-dark">
                            ₹{{ number_format($invoice->items->sum(function($item) {
                                return $item->unit_price * $item->qty;
                            }), 2) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold text-dark">Tax Amount</span>
                            <span class="fw-semibold text-dark">
                              ₹{{ number_format($invoice->items->sum(function($item) {
                                return $item->tax_amount ?? 0;
                              }), 2) }}
                            </span>
                        </div>
                        <hr class="my-3" />
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5 text-dark">Total</span>
                            <span class="fw-bold fs-5 text-success">
                              @if(!empty($invoice->amount_in_words))
                                {{ $invoice->amount_in_words }}
                              @else
                                {{ number_format($invoice->grand_total ?? 0, 2) }}
                              @endif
                            </span>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="container mb-3 mt-3" id="invoice">
                  <div class="p-3 rounded-4 shadow-sm border bg-white">
                    
                    <div class="row g-4">
                      <!-- Bank Details -->
                      <div class="col-md-12">
                        <div class="p-2 rounded-3 h-auto"
                            style="background: linear-gradient(to right, #f0f4ff, #e2e6f0); border-left: 4px solid #0d6efd;">
                          <h6 class="fw-bold text-primary mb-2" style="font-size: 1rem;">
                            <i class="fas fa-university me-2"></i>Bank Details
                          </h6>
                          <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size: 0.95rem;">
                            <div>
                              <strong class="text-secondary">Account Holder:</strong> {{ $invoice->warehouse->account_name ?? '-' }}
                            </div>                            
                            <div>
                              <strong class="text-secondary">A/C Number:</strong> {{ $invoice->warehouse->account_number ?? '-' }}
                            </div>
                            <div>
                              <strong class="text-secondary">Bank Name:</strong> {{ $invoice->warehouse->bank_name ?? '-' }}
                            </div>
                            <div>
                              <strong class="text-secondary">IFSC Code:</strong> {{ $invoice->warehouse->ifsc_code ?? '-' }}
                            </div>
                            <div>
                              <strong class="text-secondary">Branch:</strong> {{ $invoice->warehouse->branch ?? '-' }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="row mt-2 border-top pt-4">
                      <div class="col-md-6">
                        <p><strong>Amount in Words: </strong>Rupees  
                            @if(!empty($invoice->amount_in_words))
                                {{ "Rupees ". $invoice->amount_in_words }}
                            @else
                                @php
                                    if (class_exists('NumberFormatter')) {
                                        $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                                        echo ucwords($f->format($invoice->grand_total ?? 0));
                                    } else {
                                        // Pure PHP fallback for number to words
                                        $grandTotal = $invoice->grand_total ?? 0;
                                        $grandTotalInt = (int) $grandTotal;
                                        $decimalPart = round(($grandTotal - $grandTotalInt) * 100);
                                        $words = ucwords(numberToWords($grandTotalInt));
                                        if ($decimalPart > 0) {
                                            $words .= ' And ' . ucwords(numberToWords($decimalPart)) . ' Paise';
                                        }
                                        echo $words . ' Only';
                                    }
                                @endphp
                            @endif
                        </p>
                        <p><strong>Declaration:</strong><br>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</p>
                      </div>
                      <div class="col-md-6">
                        <div class="text-end mt-2">
                          <p>For {{ $invoice->warehouse->company_name ?? $invoice->warehouse->name ?? '-' }}</p><br>
                          <p><em>Authorised Signatory</em></p>
                          
                        </div>
                      </div>
                    </div>

                    <p class="mt-1" style="text-align:center;">This is a Computer Generated Invoice</p>
                  </div>
                </div>
             
                    
            </div>
          </div> <!-- end of .invoice-box -->
        </div>
        <div class="row mt-4 no-print">
            <div class="col-md-6 offset-md-3 col-12">
                <div class="d-flex flex-row justify-content-center align-items-center gap-3">
                    <button class="btn btn-primary btn-download w-100" style="min-width:180px;max-width:100%;" onclick="downloadPDF()">Download Invoice</button>
                    <button class="btn btn-warning text-white btn-print w-100 bold" style="min-width:190px;max-width:100%; background-color: #f47820; border-color: #f47820; font-size:1.05rem; padding-top:0.6rem; padding-bottom:0.6rem; te;" onclick="printInvoice()">Print Invoice</button>
                    <button class="btn btn-success w-100" id="sendEmailBtn" style="min-width:190px;max-width:100%; font-size:1.05rem; padding-top:0.6rem; padding-bottom:0.6rem;" onclick="sendInvoiceEmail()">Send Email</button>
                </div>
            </div>
        </div>
    </main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
  async function downloadPDF() {
  const element = document.getElementById('invoice');
  const invoiceNumber = @json($invoice->invoice_number);
  const noPrintEls = document.querySelectorAll('.no-print');
  const prevDisplay = [];
  noPrintEls.forEach((el, i) => {
    prevDisplay[i] = el.style.display;
    el.style.display = 'none';
  });

  const canvas = await html2canvas(element, {
    scale: 2,
    allowTaint: false,
  });

  noPrintEls.forEach((el, i) => {
    el.style.display = prevDisplay[i];
  });

  const imgData = canvas.toDataURL('image/png');
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'mm', 'a4');
  const pageWidth = 210;
  const pageHeight = 297;

  const imgProps = pdf.getImageProperties(imgData);
  const imgWidth = pageWidth;
  const imgHeight = (imgProps.height * imgWidth) / imgProps.width;

  let y = 0;
  if (imgHeight < pageHeight) {
    y = (pageHeight - imgHeight) / 2; // center vertically
  }

  if (imgHeight <= pageHeight) {
    pdf.addImage(imgData, 'PNG', 0, y, imgWidth, imgHeight);
  } else {
    let position = 0;
    while (position < imgHeight) {
      pdf.addImage(imgData, 'PNG', 0, -position, imgWidth, imgHeight);
      position += pageHeight;
      // Only add a new page if more content remains
      if (position < imgHeight) pdf.addPage();
    }
  }

  pdf.save(invoiceNumber + '.pdf');
}


function printInvoice() {
    const invoice = document.getElementById('invoice');
    // Get invoice number from Blade variable (passed to JS)
    const invoiceNumber = @json($invoice->invoice_number);
    // Hide all elements with the no-print class before capturing
    const noPrintEls = document.querySelectorAll('.no-print');
    const prevDisplay = [];
    noPrintEls.forEach((el, i) => {
      prevDisplay[i] = el.style.display;
      el.style.display = 'none';
    });

    html2canvas(invoice, {
      scale: 2,
      allowTaint: false,
    }).then(canvas => {
      // Restore display after capture
      noPrintEls.forEach((el, i) => {
        el.style.display = prevDisplay[i];
      });
      const imgData = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF('p', 'mm', 'a4');
      const pageWidth = pdf.internal.pageSize.getWidth();
      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pageWidth;
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
      pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save(invoiceNumber + '.pdf');
      // Open the PDF in a new window and trigger print
      const pdfBlob = pdf.output('bloburl');
      const printWindow = window.open(pdfBlob, '_blank');
      if (printWindow) {
        printWindow.onload = function() {
          printWindow.focus();
          printWindow.print();
        };
      }
    });
}

// Send Email function
async function sendInvoiceEmail() {
  const element = document.getElementById('invoice');
  const invoiceNumber = @json($invoice->invoice_number);
  const customerEmail = @json($invoice->customer->email);
  if (!customerEmail) {
    alert('No customer email found.');
    return;
  }
  // Hide all elements with the no-print class before capturing
  const noPrintEls = document.querySelectorAll('.no-print');
  const prevDisplay = [];
  noPrintEls.forEach((el, i) => {
    prevDisplay[i] = el.style.display;
    el.style.display = 'none';
  });
  const canvas = await html2canvas(element, { scale: 2, allowTaint: false });
  noPrintEls.forEach((el, i) => {
    el.style.display = prevDisplay[i];
  });
  const imgData = canvas.toDataURL('image/png');
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'mm', 'a4');
  const pageWidth = pdf.internal.pageSize.getWidth();
  const imgProps = pdf.getImageProperties(imgData);
  const pdfWidth = pageWidth;
  const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
  pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
  const pdfBlob = pdf.output('blob');
  const formData = new FormData();
  formData.append('pdf', new Blob([pdfBlob], { type: 'application/pdf' }), invoiceNumber + '.pdf');
  formData.append('invoice_id', @json($invoice->id));
  formData.append('email', customerEmail);
  // CSRF token
  formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
  document.getElementById('sendEmailBtn').disabled = true;
  document.getElementById('sendEmailBtn').innerText = 'Sending...';

  // Debug logs
  console.log('[InvoiceEmail] Sending invoice email...');
  console.log('[InvoiceEmail] Invoice number:', invoiceNumber);
  console.log('[InvoiceEmail] Customer email:', customerEmail);
  // Log FormData keys/values
  for (let pair of formData.entries()) {
    if (pair[0] === 'pdf') {
      console.log('[InvoiceEmail] FormData:', pair[0], pair[1] instanceof Blob ? `Blob(${pair[1].size} bytes)` : pair[1]);
    } else {
      console.log('[InvoiceEmail] FormData:', pair[0], pair[1]);
    }
  }
  try {
    const response = await fetch('/invoice/send-email', {
      method: 'POST',
      body: formData
    });
    console.log('[InvoiceEmail] Response status:', response.status);
    let responseBody = null;
    try {
      responseBody = await response.clone().json();
      console.log('[InvoiceEmail] Response JSON:', responseBody);
    } catch (jsonErr) {
      try {
        responseBody = await response.clone().text();
        console.log('[InvoiceEmail] Response Text:', responseBody);
      } catch (textErr) {
        console.log('[InvoiceEmail] Could not parse response body.', textErr);
      }
    }
    if (response.ok) {
      alert('Invoice sent to ' + customerEmail);
    } else {
      let errorMsg = 'Failed to send email.';
      if (responseBody && typeof responseBody === 'object' && responseBody.message) {
        errorMsg += '\nServer: ' + responseBody.message;
      } else if (typeof responseBody === 'string' && responseBody.length < 500) {
        errorMsg += '\nServer: ' + responseBody;
      }
      alert(errorMsg);
    }
  } catch (e) {
    console.error('[InvoiceEmail] Network or JS error:', e);
    alert('Error sending email. ' + (e && e.message ? e.message : ''));
  }
  document.getElementById('sendEmailBtn').disabled = false;
  document.getElementById('sendEmailBtn').innerText = 'Send Email';
}

// Auto-print and/or email if requested via query param
(function() {
    function getQueryParam(name) {
        const url = window.location.search;
        const params = new URLSearchParams(url);
        return params.get(name);
    }
    if (getQueryParam('auto') === 'all' && typeof sendInvoiceEmail === 'function' && typeof printInvoice === 'function') {
        // Wait for DOM and resources
        window.addEventListener('load', function() {
            // First print, then send email (start email after print dialog is likely open)
            setTimeout(function() {
                printInvoice();
                // Start sending email after a shorter delay (e.g., 200ms)
                setTimeout(function() {
                    sendInvoiceEmail();
                }, 200); // reduced delay for faster email start
            }, 400);
        });
    } else if (getQueryParam('auto_print') === '1' && typeof printInvoice === 'function') {
        window.addEventListener('load', function() {
            setTimeout(function() {
                printInvoice();
            }, 400);
        });
    }
})();
</script>

<style>
#invoice, #invoice * {
  font-size: 14px !important;
}
#invoice h1 {
  font-size: 16px !important;
}
@media print {
    /* Hide all web layout inside .invoice-box for print */
    .invoice-box > *:not(.print-header-row):not(.print-summary-row):not(.print-footer-row):not(.print-declaration) {
        display: none !important;
    }
    /* Show print-only layout */
    .print-header-row,
    .print-summary-row,
    .print-footer-row,
    .print-declaration {
        display: flex !important;
    }
    .print-declaration {
        display: block !important;
    }
}
@media print {
    .no-print { display: none !important; }
    body, html {
        background: #fff !important;
        color: #222 !important;
        font-family: 'Segoe UI', Arial, sans-serif !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        margin: 0;
        padding: 0;
    }
    .container, .container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    .invoice-box {
        max-width: 900px !important;
        margin: 0 auto !important;
        box-shadow: none !important;
        background: #fff !important;
        border-radius: 10px !important;
        padding: 0 !important;
    }
    /* PRINT-ONLY: Modern header layout */
    .print-header-row {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 16px !important;
        margin-bottom: 18px !important;
    }
    .print-header-card {
        flex: 1 1 0;
        background: #f8f9fa !important;
        border-radius: 8px !important;
        padding: 18px 16px 12px 16px !important;
        border: 1px solid #eee !important;
        min-height: 120px;
        font-size: 1rem;
    }
    .print-header-card h6 {
        color: #f47820 !important;
        font-weight: bold !important;
        margin-bottom: 8px !important;
    }
    /* PRINT-ONLY: Modern table */
    #responsive-table table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        background: #fff !important;
        margin-bottom: 0 !important;
    }
    #responsive-table th, #responsive-table td {
        border: 1px solid #dee2e6 !important;
        padding: 8px 10px !important;
        font-size: 1rem !important;
        text-align: center !important;
        background: #fff !important;
    }
    #responsive-table thead th {
        background: #f47820 !important;
        color: #fff !important;
        font-weight: bold !important;
        font-size: 1.05rem !important;
        border-bottom: 2px solid #f47820 !important;
    }
    #responsive-table tbody tr {
        background: #fff !important;
    }
    #responsive-table td:before { display: none !important; }
    .mobile-value { display: inline !important; font-weight: 400 !important; color: inherit !important; margin: 0 !important; text-align: inherit !important; }
    /* PRINT-ONLY: Summary cards below table */
    .print-summary-row {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 16px !important;
        margin-top: 24px !important;
        margin-bottom: 0 !important;
    }
    .print-summary-card {
        flex: 1 1 0;
        background: #f8f9fa !important;
        border-radius: 8px !important;
        padding: 16px 12px !important;
        border: 1px solid #eee !important;
        text-align: center !important;
        font-size: 1.05rem !important;
        font-weight: 500 !important;
    }
    .print-summary-card.cgst { color: #0d6efd !important; }
    .print-summary-card.sgst { color: #198754 !important; }
    .print-summary-card.igst { color: #dc3545 !important; }
    .print-summary-card.grand-total {
        background: #fff3cd !important;
        border: 2px solid #ffe082 !important;
        color: #222 !important;
        font-size: 1.15rem !important;
        font-weight: bold !important;
    }
    .print-summary-card.grand-total .total-amount {
        color: #388e3c !important;
        font-size: 1.35rem !important;
        font-weight: bold !important;
    }
    /* PRINT-ONLY: Footer */
    .print-footer-row {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 16px !important;
        margin-top: 32px !important;
        border-top: 2px solid #222 !important;
        padding-top: 12px !important;
    }
    .print-footer-left {
        flex: 2 1 0;
        font-size: 1.05rem !important;
    }
    .print-footer-right {
        flex: 1 1 0;
        text-align: right !important;
        font-size: 1.05rem !important;
    }
    .print-footer-right em {
        color: #888 !important;
        font-size: 0.98rem !important;
    }
    .print-declaration {
        margin-top: 8px !important;
        font-size: 0.98rem !important;
    }
    /* PRINT-ONLY: Hide web layout, show only print layout */
    .invoice-box > .d-flex,
    .invoice-box > .container,
    .invoice-box > .row,
    .invoice-box > .col-md-4,
    .invoice-box > .col-md-6,
    .invoice-box > .col-12,
    .invoice-box > .table-responsive:not(.d-print-block),
    .invoice-box > .row.mt-4,
    .invoice-box > .row.mt-5,
    .invoice-box > .container.mt-5,
    .invoice-box > .border,
    .invoice-box > .rounded,
    .invoice-box > .shadow-sm,
    .invoice-box > .bg-light,
    .invoice-box > .bg-warning,
    .invoice-box > .bg-opacity-25,
    .invoice-box > .d-flex,
    .invoice-box > .d-print-none,
    .invoice-box > .no-print,
    .main-content > .row,
    .main-content > .container,
    .main-content > .container-fluid > .row,
    .main-content > .container-fluid > .container,
    .main-content > .container-fluid > .container.mt-5,
    .main-content > .container-fluid > .row.mt-4,
    .main-content > .container-fluid > .row.mt-5 {
        display: none !important;
    }
    .print-header-row,
    .print-summary-row,
    .print-footer-row,
    .print-declaration {
        display: flex !important;
    }
    .print-declaration {
        display: block !important;
    }
}
@media (max-width: 767.98px) {
  /* Remove mobile/stacked styles for print */
  #responsive-table table, #responsive-table thead, #responsive-table tbody, #responsive-table th, #responsive-table tr {
    display: block !important;
    width: 100% !important;
  }
  #responsive-table thead {
    display: none !important;
  }
  #responsive-table tr {
    margin-bottom: 1.2rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    background: #fff;
    padding: 0.5rem;
  }
  #responsive-table td {
    display: block !important;
    padding: 0.5rem 0.5rem 0.5rem 0.5rem;
    border: none !important;
    position: relative;
    min-height: 40px;
    word-break: break-word;
    white-space: normal;
    background: #fff;
    text-align: left !important;
  }
  #responsive-table td:before {
    content: attr(data-label) !important;
    display: block !important;
    font-weight: bold !important;
    color: #f47820 !important;
    margin-bottom: 0.2rem !important;
    font-size: 0.98em !important;
    white-space: pre-line !important;
    word-break: break-word !important;
    text-align: left !important;
  }
  #responsive-table td:last-child {
    border-bottom: none !important;
  }
  .mobile-value {
    display: block !important;
    font-weight: 500 !important;
    color: #222 !important;
    margin-left: 0 !important;
    margin-bottom: 0.2rem !important;
    text-align: right !important;
  }
}
</style>

{{-- PRINT-ONLY header row --}}
<div class="print-header-row d-none d-print-flex">
    <div class="print-header-card">
        <h6>Phoenix Infoways</h6>
        <div>#1, Avinashi Neerthekka Nilayam Tirupur Municipal Corporation Building Bungalow Stop, Avinashi Road, Tirupur - 641602</div>
        <div>GSTIN/UIN: <strong>33ABGFP9424Q1ZL</strong></div>
        <div>Email: <a href="mailto:phoenixdigitalhr@gmail.com">phoenixdigitalhr@gmail.com</a></div>
    </div>
    <div class="print-header-card">
        <h6>Bill To</h6>
        <div><strong>Name:</strong> {{ $invoice->customer->name ?? '-' }}</div>
        <div><strong>Address:</strong> {{ $invoice->customer->address ?? '-' }}, {{ is_object($invoice->customer->city) ? $invoice->customer->city->name : (is_array($invoice->customer->city) ? $invoice->customer->city['name'] : $invoice->customer->city) }}, {{ is_object($invoice->customer->state) ? $invoice->customer->state->name : (is_array($invoice->customer->state) ? $invoice->customer->state['name'] : $invoice->customer->state) }}</div>
        <div><strong>Phone:</strong> {{ $invoice->customer->mobile_no ?? '-' }}</div>
        <div><strong>State:</strong> {{ is_object($invoice->customer->state) ? $invoice->customer->state->name : (is_array($invoice->customer->state) ? $invoice->customer->state['name'] : $invoice->customer->state) }}</div>
    </div>
    <div class="print-header-card">
        <h6>Invoice Details</h6>
        <div><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</div>
        <div><strong>Date:</strong> {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') : ($invoice->created_at ? $invoice->created_at->format('d-m-Y') : '-') }}</div>
    </div>
</div>

<div class="print-summary-row d-none d-print-flex">
    @php $stateId = is_array($invoice->customer->state) ? ($invoice->customer->state['id'] ?? null) : (is_object($invoice->customer->state) ? ($invoice->customer->state->id ?? null) : null); @endphp
    @if($stateId == 35)
        <div class="print-summary-card cgst">CGST<br>₹{{ number_format($invoice->cgst ?? 0, 2) }}</div>
        <div class="print-summary-card sgst">SGST<br>₹{{ number_format($invoice->sgst ?? 0, 2) }}</div>
    @else
        <div class="print-summary-card igst">IGST<br>₹{{ number_format($invoice->igst ?? 0, 2) }}</div>
        <div class="print-summary-card" style="background:transparent;border:none;"></div>
    @endif
    <div class="print-summary-card grand-total">
        <div>Grand Total</div>
        <div>Subtotal<br>₹{{ number_format($invoice->items->sum(function($item) { return $item->unit_price * $item->qty; }), 2) }}</div>
        <div>Tax Amount<br>₹{{ number_format($invoice->items->sum(function($item) { return $item->tax_amount ?? 0; }), 2) }}</div>
        <hr style="margin:8px 0;" />
        <div class="total-amount">Total ₹{{ number_format($invoice->grand_total ?? 0, 2) }}</div>
    </div>
</div>
<div class="print-footer-row d-none d-print-flex">
    <div class="print-footer-left">
        <strong>Amount in Words:</strong> @if(!empty($invoice->amount_in_words)) {{ $invoice->amount_in_words }} @else @php if (class_exists('NumberFormatter')) { $f = new NumberFormatter('en', NumberFormatter::SPELLOUT); echo ucwords($f->format($invoice->grand_total ?? 0)); } else { $grandTotal = $invoice->grand_total ?? 0; $grandTotalInt = (int) $grandTotal; $decimalPart = round(($grandTotal - $grandTotalInt) * 100); $words = ucwords(numberToWords($grandTotalInt)); if ($decimalPart > 0) { $words .= ' And ' . ucwords(numberToWords($decimalPart)) . ' Paise'; } echo $words . ' Only'; } @endphp @endif
    </div>
    <div class="print-footer-right">
        Phoenix Digital<br>
        <em>This is a Computer Generated Invoice</em>
    </div>
</div>
<div class="print-declaration d-none d-print-block">
    <strong>Declaration:</strong> We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
</div>
@endsection