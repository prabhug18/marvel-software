@extends('layouts.backend')

@section('content')
@include('backend.include.mnubar')

  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')     
        @php
        // Helper: number to words (existing logic)
        if (!function_exists('numberToWords')) {
            function numberToWords($number) {
                $hyphen      = '-';
                $conjunction = ' and ';
                $separator   = ', ';
                $negative    = 'negative ';
                $decimal     = ' point ';
                $dictionary  = [
                    0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety', 100 => 'hundred', 1000 => 'thousand', 1000000 => 'million', 1000000000 => 'billion',
                ];
                if (!is_numeric($number)) return false;
                if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) return false;
                if ($number < 0) return $negative . numberToWords(abs($number));
                $string = '';
                if ($number < 21) $string = $dictionary[$number];
                elseif ($number < 100) {
                    $tens   = ((int) ($number / 10)) * 10;
                    $units  = $number % 10;
                    $string = $dictionary[$tens];
                    if ($units) $string .= $hyphen . $dictionary[$units];
                } elseif ($number < 1000) {
                    $hundreds  = (int) ($number / 100);
                    $remainder = $number % 100;
                    $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                    if ($remainder) $string .= $conjunction . numberToWords($remainder);
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

        @php
        // Use delivery address if present, else customer address
        $delivery = $invoice->deliveryAddress;
        $addressName = $delivery->name ?? $invoice->customer->name ?? '-';
        $addressLine = $delivery->address ?? $invoice->customer->address ?? '-';
        $addressCity = $delivery->city ?? (is_object($invoice->customer->city) ? $invoice->customer->city->name : (is_array($invoice->customer->city) ? $invoice->customer->city['name'] : $invoice->customer->city));
        $addressState = $delivery->state ?? (is_object($invoice->customer->state) ? $invoice->customer->state->name : (is_array($invoice->customer->state) ? $invoice->customer->state['name'] : $invoice->customer->state));
        $addressPincode = $delivery->pincode ?? $invoice->customer->pincode ?? '';
        $addressGSTIN = $delivery->gstin ?? $invoice->customer->gstin ?? '';
        @endphp
    
    <style id="invoicePrintStyle">
        
            .invoice-box {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #000;
            }
            .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5em;
            }
            .header-table td {
            border: none;
            font-size: 14px;
            vertical-align: top;
            padding: 2px 4px;
            }
            .header-table .title {
            font-size: 20px;
            font-weight: bold;
            text-align: left;
            text-decoration: underline;
            padding-bottom: 8px;
            }
            .qr-box {
            text-align: left;
            vertical-align: top;
            }
            .summary-table, .items-table, .tax-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 0.5em;
            }
            .summary-table td, .summary-table th,
            .items-table td, .items-table th,
            .tax-table td, .tax-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            }
            .summary-table th, .items-table th, .tax-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
            }
            .items-table th, .items-table td {
            text-align: left;
            }
            .items-table td.center, .items-table th.center {
            text-align: left;
            }
            .items-table td.right, .items-table th.right {
            text-align: left;
            }
            .total-row td {
            font-weight: bold;
            }
            .amount-words {
            margin-top: 10px;
            font-size: 14px;
            }
            .declaration {
            font-size: 13px;
            margin-top: 10px;
            }
            .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 5px;
            }
            .sign-box {
            text-align: right;
            font-size: 13px;
            margin-top: 30px;
            }
            .bold { font-weight: bold; }
            .small { font-size: 12px; }
            .no-border { border: none !important; }
        </style>
   
        <div class="invoice-box">
            <div class="top-logo-section" style="text-align:center; margin-bottom: 20px;">
                @if(!empty($invoice->warehouse->image))
                    <img src="{{ asset($invoice->warehouse->image) }}" alt="Warehouse Logo" style="max-height: 80px; max-width: 150px;">
                @endif
            </div>
            <table class="header-table">
            <tr>
                <td colspan="2" class="title" style="text-align:left;text-decoration:underline;">Tax Invoice</td>
                <td class="qr-box" rowspan="3">
                <div style="text-align:right;">
                    <span class="small">e-Invoice</span><br>
                    {{-- QR code placeholder --}}
                    <img src="{{ asset('images/qr-placeholder.png') }}" alt="QR Code" style="width:110px;height:110px;">
                </div>
                </td>
            </tr>
            <!-- IRN, Ack No., and Ack Date removed as per user request -->
            </table>

            <table class="summary-table">
            <tr>
                <td style="width:40%;vertical-align:top;">
                <span class="bold">{{ $invoice->warehouse->company_name ?? $invoice->warehouse->name ?? '-' }}</span><br>
                {{ $invoice->warehouse->address ?? '-' }}<br>
                GSTIN/UIN: {{ $invoice->warehouse->gstn_uin ?? '-' }}<br>
                State Name: {{ $invoice->warehouse->state_name ?? '-' }}, Code : {{ $invoice->warehouse->state_code ?? '-' }}
                </td>
                <td style="width:30%;vertical-align:top;">
                <span class="bold">Invoice No.</span><br>
                {{ $invoice->invoice_number }}<br>
                <span class="bold">Dated</span><br>
                {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-y') : ($invoice->created_at ? $invoice->created_at->format('d-M-y') : '-') }}
                </td>
                <td style="width:30%;vertical-align:top;">
                <span class="bold">Consignee (Ship to)</span><br>
                {{ $addressName }}<br>
                {{ $addressLine }}<br>
                GSTIN/UIN: {{ $addressGSTIN ?: '-' }}<br>
                State Name: {{ $addressState }}, Code : {{ $delivery->state_code ?? $invoice->customer->state_code ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="vertical-align:top;">
                <span class="bold">Buyer (Bill to)</span><br>
                {{ $addressName }}<br>
                {{ $addressLine }}<br>
                GSTIN/UIN: {{ $addressGSTIN ?: '-' }}<br>
                State Name: {{ $addressState }}, Code : {{ $delivery->state_code ?? $invoice->customer->state_code ?? '-' }}
                </td>
                <td colspan="2" style="vertical-align:top;">
                <span class="bold">Delivery Note</span><br>
                <span class="small">DC Number: {{ $invoice->dc_number ?? '-' }}</span>
                </td>
            </tr>
            </table>

            <table class="items-table">
            <tr>
                <th class="center">Sl No.</th>
                <th>Description of Goods</th>
                <th class="center">HSN/SAC</th>
                <th class="center">Quantity</th>
                <th class="center">Rate</th>
                <th class="center">per</th>
                <th class="center">Disc. %</th>
                <th class="right">Amount</th>
            </tr>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="center">{{ $i+1 }}</td>
                <td>{{ $item->product_name ?? '-' }}</td>
                <td class="center">{{ $item->product->hsn_code ?? '-' }}</td>
                <td class="center">{{ $item->qty }}</td>
                <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="center">No</td>
                <td class="center">-</td>
                <td class="right">{{ number_format($item->unit_price * $item->qty, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="right">Total</td>
                <td class="center">{{ $invoice->items->sum('qty') }}</td>
                <td colspan="3" class="right"></td>
                <td class="right">₹{{ number_format($invoice->grand_total ?? 0, 2) }}</td>
            </tr>
            </table>

            <div class="amount-words">
            <span class="bold">Amount Chargeable (in words)</span><br>
            @if(!empty($invoice->amount_in_words))
                {{ $invoice->amount_in_words }}
            @else
                @php
                if (class_exists('NumberFormatter')) {
                    $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                    echo ucwords($f->format($invoice->grand_total ?? 0)) . ' Only';
                } else {
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
            </div>

            <table class="tax-table">
            <tr>
                <th>Tax Type</th>
                <th>Taxable Value</th>
                <th>Rate (%)</th>
                <th>Amount</th>
            </tr>
            @php
                $taxableValue = $invoice->items->sum(function($item) { return $item->unit_price * $item->qty; });
                $customerState = strtolower($addressState ?? '');
                $isTamilNadu = in_array($customerState, ['tamil nadu', 'tamilnadu', 'tn']);
            @endphp
            @if($isTamilNadu)
                <tr>
                <td>CGST</td>
                <td>{{ number_format($taxableValue, 2) }}</td>
                <td>{{ $invoice->cgst ? number_format(($invoice->cgst / ($taxableValue ?: 1)) * 100, 2) : '0.00' }}</td>
                <td>{{ number_format($invoice->cgst ?? 0, 2) }}</td>
                </tr>
                <tr>
                <td>SGST</td>
                <td>{{ number_format($taxableValue, 2) }}</td>
                <td>{{ $invoice->sgst ? number_format(($invoice->sgst / ($taxableValue ?: 1)) * 100, 2) : '0.00' }}</td>
                <td>{{ number_format($invoice->sgst ?? 0, 2) }}</td>
                </tr>
            @else
                <tr>
                <td>IGST</td>
                <td>{{ number_format($taxableValue, 2) }}</td>
                <td>{{ $invoice->igst ? number_format(($invoice->igst / ($taxableValue ?: 1)) * 100, 2) : '0.00' }}</td>
                <td>{{ number_format($invoice->igst ?? 0, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total GST</td>
                <td></td>
                <td></td>
                <td>{{ number_format(($invoice->cgst ?? 0) + ($invoice->sgst ?? 0) + ($invoice->igst ?? 0), 2) }}</td>
            </tr>
            </table>

            <div class="amount-words">
            <span class="bold">GST Amount (in words):</span>
            @php
                $customerState = strtolower($addressState ?? '');
                $isTamilNadu = in_array($customerState, ['tamil nadu', 'tamilnadu', 'tn']);
                if ($isTamilNadu) {
                $gstTotal = floatval($invoice->cgst ?? 0) + floatval($invoice->sgst ?? 0);
                } else {
                $gstTotal = floatval($invoice->igst ?? 0);
                }
                // Fallback to 0 if all are empty
                if (!$gstTotal) { $gstTotal = 0; }
                if (class_exists('NumberFormatter')) {
                $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                echo 'Indian Rupee ' . ucwords($f->format($gstTotal)) . ' Only';
                } else {
                $gstInt = (int) $gstTotal;
                $decimalPart = round(($gstTotal - $gstInt) * 100);
                $words = ucwords(numberToWords($gstInt));
                if ($decimalPart > 0) {
                    $words .= ' And ' . ucwords(numberToWords($decimalPart)) . ' Paise';
                }
                echo 'Indian Rupee ' . $words . ' Only';
                }
            @endphp
            </div>

            <div class="declaration">
            <span class="bold">Declaration</span><br>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
            </div>

            <div class="sign-box">
            for {{ $invoice->warehouse->company_name ?? $invoice->warehouse->name ?? '-' }}<br><br>
            <span class="small">Authorised Signatory</span>
            </div>

            <div class="footer">
            This is a Computer Generated Invoice
            </div>
            <div style="text-align:center; margin-top:12px;">
                <button id="printThreeCopiesBtn" class="btn btn-primary">Print 3 Copies</button>
            </div>
        </div>
    </main>
    <script>
        (function(){
            function getInvoiceInnerHtml() {
                var el = document.querySelector('.invoice-box');
                if (!el) return '';
                return el.innerHTML;
            }

            function buildPrintDoc(copiesHtml) {
                var doc = '<!doctype html><html><head><title>Print Invoice</title>';
                    // inject only the invoice style block from this page to avoid external stylesheet conflicts
                    var invoiceStyle = document.getElementById('invoicePrintStyle');
                    if (invoiceStyle) {
                        doc += invoiceStyle.outerHTML;
                    }
                    // add robust print CSS: avoid fixed heights (these often cause blank pages)
                    // use page-break-after for copies but disable it for the last copy to prevent trailing blank page
                    // hide on-page controls in printed output
                    doc += '<style>html,body{margin:0;padding:0; -webkit-print-color-adjust:exact;} body{background:#fff;} .invoice-copy{display:block; width:100% !important; box-sizing:border-box !important; page-break-after:always; break-after:page;} .invoice-copy:last-child{page-break-after:auto; break-after:auto;} .invoice-box{width:100% !important; max-width:900px !important; margin:8mm auto !important; float:none !important; box-sizing:border-box !important;} .copy-label{display:block; text-align:center; font-weight:bold; margin-bottom:8px;} table.print-layout { width: 100%; border-collapse: collapse; } thead.print-header { display: table-header-group; } @page{size:A4 portrait; margin:10mm;} #printThreeCopiesBtn, #printInstructionsOverlay { display: none !important; }</style>';
                doc += '</head><body>' + copiesHtml + '</body></html>';
                return doc;
            }

            function createHiddenIframe(docHtml) {
                var iframe = document.createElement('iframe');
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
                var idoc = iframe.contentWindow.document;
                idoc.open();
                idoc.write(docHtml);
                idoc.close();
                return iframe;
            }

            function doPrintCombined() {
                var invoiceContainer = document.querySelector('.invoice-box').cloneNode(true);
                if (!invoiceContainer) { Swal.fire({ icon: 'error', title: 'Error', text: 'Invoice content not found to print.' }); return; }
                
                // Extract Top Logo and Header Table
                var logoSection = invoiceContainer.querySelector('.top-logo-section');
                var headerTable = invoiceContainer.querySelector('.header-table');
                
                var headerHtml = '';
                if (logoSection) { headerHtml += logoSection.outerHTML; logoSection.remove(); }
                if (headerTable) { headerHtml += headerTable.outerHTML; headerTable.remove(); }
                
                var bodyHtml = invoiceContainer.innerHTML;
                var labels = ['Original', 'Duplicate for Transporter', 'Triplicate Copy'];
                var copiesHtml = '';
                
                labels.forEach(function(label){
                    var copyWrap = '<div class="invoice-copy">' +
                        '<table class="print-layout">' +
                            '<thead class="print-header">' +
                                '<tr><td>' +
                                    '<div class="copy-label">' + label + '</div>' +
                                    headerHtml +
                                    '<div style="height:10px; border-bottom:1px solid #000; margin-bottom:10px;"></div>' +
                                '</td></tr>' +
                            '</thead>' +
                            '<tbody>' +
                                '<tr><td>' +
                                    '<div class="invoice-box-inner" style="margin-top:10px;">' + bodyHtml + '</div>' +
                                '</td></tr>' +
                            '</tbody>' +
                        '</table>' +
                    '</div>';
                    copiesHtml += copyWrap;
                });

                // remove trailing page-break-after on the last copy to prevent extra blank page
                copiesHtml = copiesHtml.replace(/(<div class="invoice-copy">[\s\S]*?)<\/div>$/,'$1</div>');
                var docHtml = buildPrintDoc(copiesHtml);
                var iframe = createHiddenIframe(docHtml);

                // wait a bit for iframe to render then print once
                setTimeout(function(){
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch(e) {
                        Swal.fire({ icon: 'error', title: 'Printing Failed', text: 'Printing failed: ' + (e.message || e) });
                    }
                    // remove iframe after printing
                    setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){} }, 1200);
                }, 1200);
            }

            // bind button directly to single combined print to ensure one dialog
            document.getElementById('printThreeCopiesBtn').addEventListener('click', function(){
                // small delay to allow any UI repaints to finish
                setTimeout(doPrintCombined, 80);
            });
        })();
    </script>
@endsection
