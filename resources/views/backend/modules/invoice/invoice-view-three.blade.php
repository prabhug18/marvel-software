@extends('layouts.backend')

@section('content')
    @include('backend.include.mnubar')
    <main class="main-content" id="mainContent">
        @include('backend.include.header')
        <div class="container-fluid px-0" style="padding-top:6px;">
            <style>
                /* A4 print and compact layout */
                @page { size: A4; margin: 10mm; }
                @media print {
                    body { margin: 0; }
                    /* hide UI elements that are not part of the printed invoice */
                    .no-print { display: none !important; }
                    /* hide the shared header bar and other fixed UI so only invoice prints */
                    #mainHeader { display: none !important; }
                    .position-fixed { display: none !important; }
                }
                .invoice-page {
                    width: 210mm;
                    /* leave printable height slightly smaller than full A4 so margins/padding don't push footer to a new page */
                    min-height: calc(297mm - 20mm);
                    margin: 0 auto;
                    padding: 8mm;
                    background: #fff;
                    box-sizing: border-box;
                    position: relative;
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 12px;
                    line-height: 1; /* compact */
                    color: #000;
                    /* make the invoice a column so footer can be pushed to the bottom with margin-top:auto */
                    display: flex;
                    flex-direction: column;
                }
                .invoice-page h4, .invoice-page h5 { margin: 0; line-height: 1; }
                .invoice-page table { font-size: 12px; }
                .invoice-page table th, .invoice-page table td { padding: 4px 6px; line-height: 1; }
                .invoice-page .small { font-size: 11px; }
            </style>
                <div class="invoice-page">
                @php
                    // prepare logo url early so we can place logo at the top area
                    $candidateLogoTop = $invoice->warehouse->image ?? $invoice->warehouse->logo ?? $invoice->warehouse->logo_url ?? $invoice->warehouse->image_url ?? null;
                    $logoTopUrl = null;
                    if (!empty($candidateLogoTop)) {
                        if (preg_match('/^https?:\/\//i', $candidateLogoTop)) {
                            $logoTopUrl = $candidateLogoTop;
                        } else {
                            $possibleTop = public_path('storage/' . ltrim($candidateLogoTop, '/'));
                            if (file_exists($possibleTop)) {
                                $logoTopUrl = asset('storage/' . ltrim($candidateLogoTop, '/'));
                            } else {
                                $possibleTop2 = public_path(ltrim($candidateLogoTop, '/'));
                                if (file_exists($possibleTop2)) {
                                    $logoTopUrl = asset(ltrim($candidateLogoTop, '/'));
                                } else {
                                    $logoTopUrl = $candidateLogoTop;
                                }
                            }
                        }
                    }
                @endphp

                <!-- Top logo container (placed above the main header) -->
                <div class="top-logo" style="position:relative; height:80px;">
                    @if(!empty($logoTopUrl))
                        <div style="position:absolute; top:0; left:50%; transform:translateX(-50%);">
                            <img src="{{ $logoTopUrl }}" alt="logo" style="max-height:72px; object-fit:contain;" />
                        </div>
                    @endif
                </div>
                <!-- Header: company centered, QR on right -->
                <div style="width:100%; text-align:center;">
                    @php
                        $companyName = $invoice->warehouse->company_name ?? $invoice->warehouse->name ?? config('app.name');
                        // possible logo fields
                        $candidateLogo = $invoice->warehouse->image ?? $invoice->warehouse->logo ?? $invoice->warehouse->logo_url ?? $invoice->warehouse->image_url ?? null;
                        $logoUrl = null;
                        if (!empty($candidateLogo)) {
                            // if the field already contains a full URL
                            if (preg_match('/^https?:\/\//i', $candidateLogo)) {
                                $logoUrl = $candidateLogo;
                            } else {
                                // try storage path first
                                $possible = public_path('storage/' . ltrim($candidateLogo, '/'));
                                if (file_exists($possible)) {
                                    $logoUrl = asset('storage/' . ltrim($candidateLogo, '/'));
                                } else {
                                    // try public path direct
                                    $possible2 = public_path(ltrim($candidateLogo, '/'));
                                    if (file_exists($possible2)) {
                                        $logoUrl = asset(ltrim($candidateLogo, '/'));
                                    } else {
                                        // fallback: use as-is (may work if already a relative URL)
                                        $logoUrl = $candidateLogo;
                                    }
                                }
                            }
                        }
                    @endphp
                    {{-- logo moved to top container to use available top space --}}
                    <div style="font-weight:700; font-size:18px;">{{ $companyName }}</div>
                    @if(!empty($invoice->warehouse->sub_heading))
                        <div style="font-size:13px; margin-top:4px;">{{ $invoice->warehouse->sub_heading }}</div>
                    @endif
                    <div style="margin-top:6px; font-size:12px;">{{ $invoice->warehouse->address ?? '' }}</div>
                    <div style="margin-top:4px; font-size:12px;">Mobile: {{ $invoice->warehouse->mobile ?? '' }} &nbsp; Email: {{ $invoice->warehouse->email ?? '' }}</div>
                    <div style="margin-top:4px; font-size:12px;">GSTIN / UIN: {{ $invoice->warehouse->gstn_uin ?? $invoice->warehouse->gst_no ?? '' }}</div>
                </div>

                <hr style="border-top:2px solid #000; margin:10px 0;" />

                <!-- Bill To / Ship To and Eway / Transport small columns -->
                <div style="display:flex; gap:12px;">
                    <div style="flex:1; border:1px solid #000; padding:8px;">
                        <div style="font-weight:700;">Bill To : {{ $invoice->customer_name ?? ($invoice->customer->name ?? '') }}</div>
                        <div style="font-size:12px; margin-top:6px;">{{ $invoice->customer->address ?? '' }}</div>
                        <div style="font-size:12px; margin-top:4px;">{{ $invoice->customer->city->name ?? '' }}, {{ $invoice->customer->state->name ?? '' }} - {{ $invoice->customer->pincode ?? '' }}</div>
                        <div style="font-size:12px; margin-top:4px;">GSTIN: {{ $invoice->customer->gst_no ?? '' }}</div>
                        <div style="font-size:12px; margin-top:4px;">Ph: {{ $invoice->customer->mobile_no ?? '' }}</div>
                    </div>
                    <div style="flex:1; border:1px solid #000; padding:8px;">
                        <div style="font-weight:700;">Ship To :</div>
                        @php
                            $ship = $invoice->deliveryAddress ?? null;
                            if (!$ship) { $ship = $invoice->customer; }
                        @endphp
                        <div style="font-size:12px; margin-top:6px;">{{ $ship->address ?? ($invoice->customer->address ?? '') }}</div>
                        <div style="font-size:12px; margin-top:4px;">{{ $ship->city->name ?? '' }}, {{ $ship->state->name ?? '' }} - {{ $ship->pincode ?? '' }}</div>
                        <div style="font-size:12px; margin-top:4px;">GSTIN: {{ $ship->gst_no ?? ($invoice->customer->gst_no ?? '') }}</div>
                        <div style="font-size:12px; margin-top:4px;">Ph: {{ $ship->mobile_no ?? ($invoice->customer->mobile_no ?? '') }}</div>
                    </div>
                    <div style="flex:0 0 260px; border:1px solid #000; padding:8px; text-align:left;">
                        <div style="font-weight:700; font-size:14px;">TAX INVOICE</div>
                        <div style="margin-top:6px; font-size:13px;">Invoice No: <strong>{{ $invoice->invoice_number }}</strong></div>
                        <div style="margin-top:6px; font-size:12px;">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
                        <div style="margin-top:6px; font-size:12px;">State: {{ $invoice->customer->state->name ?? '' }}</div>
                    
                    </div>
                </div>

                <!-- Products table -->
                <div style="margin-top:12px;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                                <tr style="background:#f5f5f5; border:1px solid #000;">
                                <th style="border:1px solid #000; padding:6px; width:40px; text-align:left;">S/N</th>
                                <th style="border:1px solid #000; padding:6px; text-align:left;">PRODUCT DESCRIPTION</th>
                                <th style="border:1px solid #000; padding:6px; width:90px; text-align:left;">HSN</th>
                                <th style="border:1px solid #000; padding:6px; width:60px; text-align:left;">GST%</th>
                                <th style="border:1px solid #000; padding:6px; width:60px; text-align:right;">QTY</th>
                                <th style="border:1px solid #000; padding:6px; width:90px; text-align:right;">BASE PRICE</th>
                                <th style="border:1px solid #000; padding:6px; width:90px; text-align:right;">TOTAL PRICE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sn = 1;
                                $grandTaxable = 0;
                                $grandTax = 0;
                                $grandTotal = 0;
                                $hsnSummary = [];
                                // Determine intra/inter state once for consistent calculations.
                                // Prefer invoice-level stored tax totals (if CGST/SGST present then it's intra-state),
                                // otherwise fall back to state comparison when available.
                                $invoiceCgst = isset($invoice->cgst) ? (float)$invoice->cgst : 0.0;
                                $invoiceSgst = isset($invoice->sgst) ? (float)$invoice->sgst : 0.0;
                                $invoiceIgst = isset($invoice->igst) ? (float)$invoice->igst : 0.0;
                                if (($invoiceCgst + $invoiceSgst) > 0) {
                                    $isIntraState = true;
                                } elseif ($invoiceIgst > 0) {
                                    $isIntraState = false;
                                } else {
                                    $warehouseStateName = optional(optional($invoice->warehouse)->state)->name ?? null;
                                    $customerStateName = optional(optional($invoice->customer)->state)->name ?? null;
                                    $isIntraState = ($warehouseStateName && $customerStateName && ($warehouseStateName === $customerStateName));
                                }
                            @endphp
                            @foreach($invoice->items as $item)
                                    @php
                                    $qty = $item->qty ?? 1;
                                    $unit = $item->unit_price ?? 0; // unit_price is expected to be GST-exclusive base price
                                    $gstPerc = $item->tax_percentage ?? 0;
                                    // tax_amount may be stored; if not, compute from base price
                                    $taxAmt = $item->tax_amount ?? (($unit * $qty) * ($gstPerc / 100));
                                    // total is expected to be GST-inclusive total (gst_inclusive_price * qty) saved as 'total'
                                    $total = $item->total ?? (($unit * $qty) + $taxAmt);
                                    $net = $total; // net amount per line (inclusive)
                                    // Taxable (base) value should be unit * qty (GST-exclusive)
                                    $taxable = ($unit * $qty);
                                    // Resolve HSN: prefer explicit item.hsn_code, then item->hsn, then linked product hsn
                                    $hsn = $item->hsn_code ?? ($item->hsn ?? null);
                                    if (empty($hsn)) {
                                        // attempt to read from linked product relation if available
                                        $hsn = optional($item->product)->hsn_code ?? optional($item->product)->hsn ?? '';
                                        if (empty($hsn) && !empty($item->product_id)) {
                                            $prod = \App\Models\Product::find($item->product_id);
                                            $hsn = $prod->hsn_code ?? $prod->hsn ?? '';
                                        }
                                    }
                                    $gstPerc = $item->tax_percentage ?? 0;
                                    $grandTaxable += $taxable;
                                    $grandTax += $taxAmt;
                                    $grandTotal += $net;
                                    // Per-item CGST/SGST/IGST: prefer stored values, else compute from taxable and rate
                                    $item_cgst = $item->cgst_amount ?? null;
                                    $item_sgst = $item->sgst_amount ?? null;
                                    $item_igst = $item->igst_amount ?? null;
                                    if ($item_cgst === null && $item_sgst === null && $item_igst === null) {
                                        if ($isIntraState) {
                                            $item_cgst = $item_sgst = ($taxable * ($gstPerc / 100)) / 2;
                                            $item_igst = 0;
                                        } else {
                                            $item_igst = ($taxable * ($gstPerc / 100));
                                            $item_cgst = $item_sgst = 0;
                                        }
                                    }
                                    if (!isset($hsnSummary[$hsn])) {
                                        $hsnSummary[$hsn] = ['taxable' => 0, 'tax_amount' => 0, 'gst_rate' => $gstPerc, 'cgst' => 0, 'sgst' => 0, 'igst' => 0];
                                    }
                                    $hsnSummary[$hsn]['taxable'] += $taxable;
                                    $hsnSummary[$hsn]['tax_amount'] += $taxAmt;
                                    $hsnSummary[$hsn]['cgst'] += $item_cgst;
                                    $hsnSummary[$hsn]['sgst'] += $item_sgst;
                                    $hsnSummary[$hsn]['igst'] += $item_igst;
                                @endphp
                                <tr>
                                    <td style="border:1px solid #000; padding:6px;">{{ $sn++ }}</td>
                                    <td style="border:1px solid #000; padding:6px;">
                                        {{ $item->product_name ?? $item->model ?? '' }}
                                        <div style="font-size:11px; color:#666">{!! nl2br(e($item->serial_no ?? $item->serial ?? '')) !!}</div>
                                    </td>
                                    <td style="border:1px solid #000; padding:6px; text-align:left;">{{ $hsn }}</td>
                                    <td style="border:1px solid #000; padding:6px; text-align:left;">{{ $gstPerc }}</td>
                                    <td style="border:1px solid #000; padding:6px; text-align:right;">{{ $qty }}</td>
                                    <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($unit,2) }}</td>
                                    <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($total,2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="border:1px solid #000; padding:6px; text-align:right; font-weight:700;">GRAND TOTAL</td>
                                <td style="border:1px solid #000; padding:6px; text-align:right; font-weight:700;">{{ number_format($grandTotal,2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals and amount-in-words row -->
                <div style="display:flex; gap:12px; margin-top:10px;">
                    <div style="flex:1; border:1px solid #000; padding:8px;">
                        <div style="font-weight:700;">Amount in words</div>
                        <div style="margin-top:6px; font-size:12px; font-style:italic;">
                            @php
                                function numberToWords($num) {
                                    $num = (int) $num;
                                    if ($num === 0) return 'Zero';
                                    $units = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
                                    $tens = ['','', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
                                    $scales = [10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'];
                                    $words = '';
                                    foreach ($scales as $div => $name) {
                                        if ($num >= $div) {
                                            $quot = intdiv($num, $div);
                                            $num = $num % $div;
                                            $words .= numberToWords($quot) . ' ' . $name . ' ';
                                        }
                                    }
                                    if ($num >= 20) {
                                        $words .= $tens[intdiv($num, 10)];
                                        if ($num % 10) $words .= ' ' . $units[$num % 10];
                                    } elseif ($num > 0) {
                                        $words .= $units[$num];
                                    }
                                    return trim($words);
                                }
                            @endphp
                            {{ numberToWords(round($grandTotal)) }} Only
                        </div>
                    </div>

                    <div style="flex:0 0 360px;">
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <tbody>
                                @php
                                    // Prefer invoice-level stored tax totals when available (saved during create/update)
                                    $invoiceCgst = isset($invoice->cgst) ? (float)$invoice->cgst : null;
                                    $invoiceSgst = isset($invoice->sgst) ? (float)$invoice->sgst : null;
                                    $invoiceIgst = isset($invoice->igst) ? (float)$invoice->igst : null;
                                    $computedTotalTax = $grandTax; // from computed aggregation
                                    $useInvoiceTotals = ($invoiceCgst !== null || $invoiceSgst !== null || $invoiceIgst !== null) && (($invoiceCgst + $invoiceSgst + $invoiceIgst) > 0);
                                    $displayTotalTax = $useInvoiceTotals ? ($invoiceCgst + $invoiceSgst + $invoiceIgst) : $computedTotalTax;
                                    $displayGrandTotal = $grandTotal; // net amount (inclusive) - keep computed
                                    $displayInvoiceValue = $grandTaxable + $displayTotalTax;
                                    $displayRoundOff = round($displayInvoiceValue,2) - $displayInvoiceValue;
                                    $displayNetPayable = round($displayInvoiceValue,2);
                                @endphp
                                <tr>
                                    <td style="padding:6px; text-align:right;">Net Amount :</td>
                                    <td style="padding:6px; width:140px; text-align:right;">{{ number_format($displayGrandTotal,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px; text-align:right;">Total Tax :</td>
                                    <td style="padding:6px; text-align:right;">{{ number_format($displayTotalTax,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px; text-align:right;">Total Invoice Value :</td>
                                    <td style="padding:6px; text-align:right;">{{ number_format($displayInvoiceValue,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px; text-align:right;">Round Off :</td>
                                    <td style="padding:6px; text-align:right;">{{ number_format($displayRoundOff,2) }}</td>
                                </tr>
                                <tr style="font-weight:700; background:#f5f5f5;">
                                    <td style="padding:6px; text-align:right;">Net Payable Amount :</td>
                                    <td style="padding:6px; text-align:right;">{{ number_format($displayNetPayable,2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bank & HSN summary row -->
                <div style="display:flex; gap:12px; margin-top:12px;">
                    <div style="flex:1; border:1px solid #000; padding:8px;">
                            <div style="font-weight:700;">Bank Details</div>
                            @php
                                $wh = $invoice->warehouse ?? null;
                                $bank_details_raw = $wh->bank_details ?? null;
                                $bank_name = $wh->bank_name ?? $wh->bank ?? null;
                                $account_name = $wh->account_name ?? $wh->account_holder ?? null;
                                $account_number = $wh->account_number ?? $wh->account_no ?? $wh->acc_no ?? null;
                                $ifsc = $wh->ifsc_code ?? $wh->ifsc ?? null;
                            @endphp
                            <div style="font-size:12px; margin-top:6px;">
                                @if(!empty($bank_details_raw))
                                    {!! nl2br(e($bank_details_raw)) !!}
                                @else
                                    @if($bank_name)
                                        <div style="margin-top:3px;"><strong>Bank:</strong> {{ $bank_name }}</div>
                                    @endif
                                    @if($account_name)
                                        <div style="margin-top:3px;"><strong>A/C Name:</strong> {{ $account_name }}</div>
                                    @endif
                                    @if($account_number)
                                        <div style="margin-top:3px;"><strong>A/C No:</strong> {{ $account_number }}</div>
                                    @endif
                                    @if($ifsc)
                                        <div style="margin-top:3px;"><strong>IFSC:</strong> {{ $ifsc }}</div>
                                    @endif
                                    @if(empty($bank_name) && empty($account_number) && empty($ifsc) && empty($account_name))
                                        BANK NAME: --- A/C: --- IFSC: ---
                                    @endif
                                @endif
                            </div>
                    </div>
                    <div style="flex:0 0 520px; border:1px solid #000; padding:8px;">
                        <div style="font-weight:700;">HSN Summary</div>
                        <table style="width:100%; border-collapse:collapse; font-size:12px; margin-top:6px;">
                            <thead>
                                    <tr style="background:#f5f5f5;">
                                        <th style="border:1px solid #000; padding:6px; text-align:left;">HSN Code</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">Taxable Value</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">CGST Rate</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">CGST Amt</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">SGST Rate</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">SGST Amt</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">IGST Rate</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">IGST Amt</th>
                                        <th style="border:1px solid #000; padding:6px; text-align:right;">Total Tax Amt</th>
                                    </tr>
                            </thead>
                            <tbody>
                                @php
                                        $totalTaxable = 0;
                                        $totalTaxSum = 0;
                                        $totalCgst = 0;
                                        $totalSgst = 0;
                                        $totalIgst = 0;
                                        // compute total taxable from summary for proportional distribution
                                        $totalTaxableFromSummary = 0;
                                        foreach($hsnSummary as $r) { $totalTaxableFromSummary += ($r['taxable'] ?? 0); }
                                @endphp
                                @foreach($hsnSummary as $code => $row)
                                        @php
                                        $taxable = $row['taxable'];
                                        $taxamt = $row['tax_amount'];
                                        $gstRate = $row['gst_rate'] ?? 0; // total GST % for this HSN

                                        // If invoice-level totals are present, distribute them proportionally across HSN rows
                                        if (($invoiceCgst + $invoiceSgst + $invoiceIgst) > 0 && $totalTaxableFromSummary > 0) {
                                            $cgst = ($invoiceCgst * ($taxable / $totalTaxableFromSummary));
                                            $sgst = ($invoiceSgst * ($taxable / $totalTaxableFromSummary));
                                            $igst = ($invoiceIgst * ($taxable / $totalTaxableFromSummary));
                                        } else {
                                            // Use aggregate stored cgst/sgst/igst if present
                                            $cgst = $row['cgst'] ?? 0;
                                            $sgst = $row['sgst'] ?? 0;
                                            $igst = $row['igst'] ?? 0;
                                            // If stored aggregates are zero, fallback to computed rates
                                            if (empty($cgst) && empty($sgst) && empty($igst)) {
                                                if ($isIntraState) {
                                                    $cgst = $sgst = ($taxable * ($gstRate / 100)) / 2;
                                                    $igst = 0;
                                                } else {
                                                    $igst = ($taxable * ($gstRate / 100));
                                                    $cgst = $sgst = 0;
                                                }
                                            }
                                        }

                                        $total_line_tax = $cgst + $sgst + $igst;

                                        $totalTaxable += $taxable;
                                        $totalTaxSum += $total_line_tax;
                                        $totalCgst += $cgst;
                                        $totalSgst += $sgst;
                                        $totalIgst += $igst;
                                    @endphp
                                    <tr>
                                        <td style="border:1px solid #000; padding:6px;">{{ $code }}</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($taxable,2) }}</td>
                                            @php
                                                // compute rate display values for this HSN row
                                                $gstRateForRow = $gstRate ?? ($row['gst_rate'] ?? 0);
                                                if ($isIntraState) {
                                                    $cgst_rate = $sgst_rate = ($gstRateForRow / 2);
                                                    $igst_rate = 0;
                                                } else {
                                                    $cgst_rate = $sgst_rate = 0;
                                                    $igst_rate = $gstRateForRow;
                                                }
                                            @endphp
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($cgst_rate,2) }}%</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($cgst,2) }}</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($sgst_rate,2) }}%</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($sgst ?? 0,2) }}</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($igst_rate,2) }}%</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($igst ?? 0,2) }}</td>
                                            <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($total_line_tax,2) }}</td>
                                    </tr>
                                @endforeach
                                    <tr style="font-weight:700; background:#f5f5f5;">
                                        <td style="border:1px solid #000; padding:6px;">Total</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($totalTaxable,2) }}</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">&nbsp;</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($totalCgst,2) }}</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">&nbsp;</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($totalSgst,2) }}</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">&nbsp;</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($totalIgst,2) }}</td>
                                        <td style="border:1px solid #000; padding:6px; text-align:right;">{{ number_format($totalTaxSum,2) }}</td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer signature -->
                    <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:flex-end;">
                        <div></div>
                        <div style="text-align:center;">
                            <div style="font-size:12px">For : {{ $invoice->warehouse->name ?? config('app.name') }}</div>
                            <div style="margin-top:28px;">(Authorized Signatory)</div>
                        </div>
                    </div>

                <!-- Footer: make inline so it participates in flow and can be pushed to page bottom -->
                <div class="invoice-footer" style="position:static; margin-top:12px;">
                    <div style="font-size:11px; text-align:left;">The particulars given above are true and correct. The amount indicated represents the price actually charged and there is no other consideration directly or indirectly from the buyer.</div>
                </div>

                </div>
            </div>

            <!-- Bottom print control (hidden when printing) -->
            <div class="no-print" style="text-align:center; margin-top:12px;">
                <button id="printThreeCopiesBtn" class="btn btn-primary" style="padding:6px 12px; font-size:13px;">Print</button>
            </div>

            <script>
                (function(){
                    function getInvoiceHtml() {
                        var el = document.querySelector('.invoice-page');
                        if (!el) return '';
                        return el.outerHTML;
                    }

                    function buildPrintDoc(copiesHtml) {
                        var doc = '<!doctype html><html><head><title>Print Invoice</title>';
                        // include the invoice style block from this page (the first style inside container)
                        var invoiceStyle = document.querySelector('.container-fluid > style');
                        if (invoiceStyle) {
                            doc += invoiceStyle.outerHTML;
                        }
                        doc += '<style>html,body{margin:0;padding:0; -webkit-print-color-adjust:exact;} body{background:#fff;} .invoice-copy{display:block; width:100% !important; box-sizing:border-box !important; page-break-after:always; break-after:page;} .invoice-copy:last-child{page-break-after:auto; break-after:auto;} /* ensure printed invoice uses tight margins and fixed page-height so footer anchors correctly */ @page{size:A4 portrait; margin:6mm;} /* printable content height = A4 height (297mm) minus page margins (6mm top+6mm bottom) = 285mm */ .invoice-page{width:100% !important; max-width:900px !important; margin:0 auto !important; box-sizing:border-box !important; position:relative !important; padding:6mm !important; height:285mm !important; overflow:visible !important; display:flex !important; flex-direction:column !important;} /* ensure main content grows and footer sits at bottom */ .invoice-page > .invoice-footer{margin-top:auto !important; position:static !important; padding-top:4mm; } /* tighten top logo space for printed copies */ .top-logo{height:14mm !important; overflow:visible !important;} .top-logo img{max-height:12mm !important;} /* print label top-right */ .copy-label{display:block; text-align:right; font-weight:bold; margin-bottom:8px; position:absolute; top:6mm; right:8mm; z-index:999; font-size:12px;} </style>';
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

                    function buildPrintDoc(copiesHtml) {
                        var doc = '<!doctype html><html><head><title>Print Invoice</title>';
                        var invoiceStyle = document.querySelector('.container-fluid > style');
                        if (invoiceStyle) { doc += invoiceStyle.outerHTML; }
                        doc += '<style>@media print { .no-print { display:none; } } html,body{margin:0;padding:0; -webkit-print-color-adjust:exact;} body{background:#fff;} .invoice-copy { page-break-after: always; break-after: page; } .invoice-copy:last-child { page-break-after: auto; break-after: auto; } table.print-layout { width: 100%; border-collapse: collapse; } thead.print-header { display: table-header-group; } .copy-label { text-align: right; font-weight: bold; font-size: 12px; margin-bottom: 5px; } </style>';
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

                    function doPrintThreeCopies() {
                        var invoiceContainer = document.querySelector('.invoice-page').cloneNode(true);
                        if (!invoiceContainer) { Swal.fire({ icon: 'error', title: 'Error', text: 'Invoice content not found to print.' }); return; }
                        
                        // Extract Top Logo and Company Header
                        var topLogo = invoiceContainer.querySelector('.top-logo');
                        var companyHeader = invoiceContainer.querySelector('div[style*="width:100%; text-align:center;"]');
                        
                        var headerHtml = '';
                        if (topLogo) { headerHtml += topLogo.outerHTML; topLogo.remove(); }
                        if (companyHeader) { headerHtml += companyHeader.outerHTML; companyHeader.remove(); }
                        
                        var bodyHtml = invoiceContainer.innerHTML;
                        var labels = ['Original', 'Duplicate for Transporter', 'Triplicate Copy'];
                        var copiesHtml = '';
                        
                        labels.forEach(function(label, idx){
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
                                            '<div class="invoice-page-print">' + bodyHtml + '</div>' +
                                        '</td></tr>' +
                                    '</tbody>' +
                                '</table>' +
                            '</div>';
                            copiesHtml += copyWrap;
                        });

                        var docHtml = buildPrintDoc(copiesHtml);
                        var iframe = createHiddenIframe(docHtml);

                        setTimeout(function(){
                            try {
                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                            } catch(e) {
                                Swal.fire({ icon: 'error', title: 'Printing Failed', text: 'Printing failed: ' + (e.message || e) });
                            }
                            setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){} }, 1200);
                        }, 800);
                    }

                    var btn = document.getElementById('printThreeCopiesBtn');
                    if (btn) {
                        btn.addEventListener('click', function(e){ e.preventDefault(); setTimeout(doPrintThreeCopies, 50); });
                    }
                })();
            </script>
    </main>
@endsection