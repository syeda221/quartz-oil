<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - {{ $sale->invoice_no }}</title>
    <style>
        @media print {
            @page {
                margin: 2mm 3mm 2mm 2mm !important;
            }
            body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 4px 0 0 !important;
                width: 96% !important;
                max-width: 96% !important;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 5px 0;
            color: #000;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.3;
            -webkit-print-color-adjust: exact;
        }

        .receipt-container {
            width: 100%;
            max-width: 76mm;
            margin: 0 auto;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        h1, h2, h3, p { margin: 0; padding: 0; }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000;
        }

        .company-info {
            font-size: 11.5px;
            text-align: center;
            color: #000;
            font-weight: 400;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .company-info div {
            margin-bottom: 1px;
        }

        .policy-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 6px;
            margin: 6px 0 2px 0;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Meta Grid */
        .meta-grid {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-bottom: 2px;
            color: #000;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 400;
        }

        .meta-label { font-weight: 400; color: #000; }
        .meta-value { font-weight: 400; color: #000; }

        /* Items Table */
        .items-table {
            width: 98%;
            border-collapse: collapse;
            font-size: 11px;
            color: #000;
            margin: 3px 0;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 4px 0;
            vertical-align: top;
            border-bottom: 1px dashed #ccc;
        }

        .item-name {
            font-weight: 500;
            font-size: 11px;
            color: #000;
            display: block;
        }

        .item-variant {
            font-size: 10px;
            color: #000;
            font-weight: 400;
            margin-top: 1px;
            display: block;
        }

        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Totals */
        .totals-section {
            font-size: 11px;
            margin-top: 5px;
            color: #000;
        }

        .tot-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .tot-row span:first-child { font-weight: 400; color: #000; }
        .tot-row span:last-child { font-weight: 400; }

        .tot-row.grand-total {
            font-size: 13px;
            font-weight: 700;
            margin: 3px 0;
            padding: 5px 0;
            border-top: 1.5px solid #000;
            border-bottom: 2px double #000;
            color: #000;
        }

        .tot-row.grand-total span:first-child { font-weight: 700; }
        .tot-row.grand-total span:last-child { font-weight: 700; }

        /* Balances */
        .balance-section {
            font-size: 11px;
            margin-top: 4px;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        .balance-section .tot-row.closing-bal {
            font-weight: 600;
            background-color: #f8f9fa;
            padding: 3px 2px;
            border: 1px solid #000;
            margin-top: 2px;
        }

        .balance-section .tot-row.closing-bal span:first-child { font-weight: 600; }
        .balance-section .tot-row.closing-bal span:last-child { font-weight: 600; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 12px;
            color: #000;
        }

        .footer p {
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .footer .soft-credit {
            font-size: 9px;
            color: #000;
            margin-top: 3px;
            font-weight: 400;
        }

        /* Controls */
        .print-controls {
            width: 76mm;
            margin: 0 auto 15px auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary { background: #000; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
    </style>
</head>

    <div class="print-controls no-print" style="width: 76mm; margin: 10px auto 14px auto; display: flex; gap: 6px;">
        <a href="javascript:void(0)" onclick="triggerPrint()" class="btn btn-primary" style="flex: 1; padding: 7px 4px; font-size: 11px; border-radius: 5px; text-decoration: none; text-align: center; background: #000; color: #fff; font-weight: 600;">🖨️ Print Receipt</a>
        <a href="javascript:void(0)" onclick="handleGoBack()" class="btn btn-secondary" style="flex: 1; padding: 7px 4px; font-size: 11px; border-radius: 5px; text-decoration: none; text-align: center; background: #2563eb; color: #fff; font-weight: 600;">⬅️ Back</a>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="company-name">{{ \App\Models\Setting::get('company_name', 'Three Stars Medical') }}</div>
        <div class="company-info">
            <div>{{ \App\Models\Setting::get('company_address', 'Hyderabad') }}</div>
            <div>Ph: {{ \App\Models\Setting::get('company_phone', '0327-9226901') }}</div>
        </div>
        @if(\App\Models\Setting::get('facebook_link') || \App\Models\Setting::get('tiktok_link') || \App\Models\Setting::get('instagram_link') || \App\Models\Setting::get('website_link'))
        <div style="text-align: left; font-size: 10px; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word; margin-top: 4px;">
            @if(\App\Models\Setting::get('facebook_link'))
                <div style="margin-bottom: 2px;"><strong>Facebook:</strong> {{ \App\Models\Setting::get('facebook_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('tiktok_link'))
                <div style="margin-bottom: 2px;"><strong>TikTok:</strong> {{ \App\Models\Setting::get('tiktok_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('instagram_link'))
                <div style="margin-bottom: 2px;"><strong>Instagram:</strong> {{ \App\Models\Setting::get('instagram_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('website_link'))
                <div style="margin-bottom: 2px;"><strong>Website:</strong> {{ \App\Models\Setting::get('website_link') }}</div>
            @endif
        </div>
        @endif

        <div class="policy-banner">No Return, Only Exchange in 3 days</div>

        <div class="divider"></div>

        <!-- Meta Grid -->
        @php
            $isWalkin = empty($sale->customer_id);
        @endphp
        <div class="meta-grid">
            <div class="meta-row">
                <span class="meta-label">Invoice #:</span>
                <span class="meta-value">{{ $sale->invoice_no }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Date:</span>
                <span class="meta-value">{{ $sale->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Customer:</span>
                <span class="meta-value">{{ Str::limit($sale->walkin_name ?? ($sale->customer_relation->customer_name ?? 'Walking Customer'), 22) }}</span>
            </div>
            @if (auth()->check())
            <div class="meta-row">
                <span class="meta-label">Salesperson:</span>
                <span class="meta-value">{{ auth()->user()->name }}</span>
            </div>
            @endif
            @if($sale->reference)
            <div class="meta-row">
                <span class="meta-label">Remarks:</span>
                <span class="meta-value">{{ $sale->reference }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%;">S.</th>
                    <th style="width: 53%;">Description</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 13%;" class="text-end">Rate</th>
                    <th style="width: 18%;" class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleItems as $item)
                    @php
                        $sizeMode = $item['size_mode'] ?? 'std';
                        $totalPieces = (int) $item['total_pieces'];

                        $variantUnit = strtolower($item['variant_unit'] ?? (is_array($item['color'] ?? null) ? ($item['color']['unit'] ?? '') : ''));
                        $weightGrams = (float)($item['weight_per_piece'] ?? (is_array($item['color'] ?? null) ? ($item['color']['weight_per_piece'] ?? 0) : 0));

                        $qtyDisplay = $totalPieces . ' Pcs';
                        if ($variantUnit === 'pcs' || $variantUnit === 'piece' || $variantUnit === 'pieces') {
                            $qtyDisplay = $totalPieces . ' Pcs';
                            if ($weightGrams > 0) {
                                $qtyDisplay .= ' (' . ($weightGrams == (int)$weightGrams ? (int)$weightGrams : $weightGrams) . 'g)';
                            }
                        } elseif (in_array($sizeMode, ['by_kg', 'by_gm', 'by_feet', 'by_meter'])) {
                            $uomLabel = match($sizeMode) {
                                'by_kg' => 'Kg',
                                'by_gm' => 'Gm',
                                'by_feet' => 'Ft',
                                'by_meter' => 'Mtr',
                                default => '',
                            };
                            $qtyVal = (float)($item['qty_box'] ?? $item['qty'] ?? $totalPieces);
                            $qtyDisplay = ($qtyVal == (int)$qtyVal ? (int)$qtyVal : number_format($qtyVal, 3)) . ' ' . $uomLabel;
                        } elseif ($sizeMode == 'by_cartons' || $sizeMode == 'by_size') {
                            $piecesPerBox = (int)($item['pieces_per_box'] ?? 1);
                            if ($piecesPerBox <= 0) $piecesPerBox = 1;
                            $boxes = floor($totalPieces / $piecesPerBox);
                            $loose = $totalPieces % $piecesPerBox;

                            if ($boxes > 0 && $loose > 0) {
                                $qtyDisplay = "$boxes.$loose";
                            } elseif ($boxes > 0) {
                                $qtyDisplay = $boxes;
                            } else {
                                $qtyDisplay = $loose;
                            }
                        }

                        $sizeStr = '';
                        if (!empty($item['size_val']) && $item['size_val'] !== '-') {
                            $sizeStr = $item['size_val'];
                        } elseif (($item['size_mode'] ?? '') == 'by_size' && ($item['height'] ?? 0) > 0 && ($item['width'] ?? 0) > 0) {
                            $sizeStr = number_format($item['width'], 0) . 'x' . number_format($item['height'], 0);
                        }
                        $colorStr = '';
                        if (!empty($item['color_val']) && $item['color_val'] !== '-') {
                            $colorStr = $item['color_val'];
                        }
                        $variantStr = implode(' | ', array_filter([$sizeStr, $colorStr]));
                    @endphp
                    <tr>
                        <td style="width: 6%;">{{ $loop->iteration }}</td>
                        <td style="width: 53%;">
                            <span class="item-name">{{ $item['item_name'] }}</span>
                            @if($variantStr && $variantStr !== '{' && trim($variantStr) !== '')
                                <span class="item-variant">{{ $variantStr }}</span>
                            @endif
                        </td>
                        <td style="width: 10%;" class="text-center">{{ $qtyDisplay }}</td>
                        <td style="width: 13%;" class="text-end">{{ number_format($item['price'], 0) }}</td>
                        <td style="width: 18%;" class="text-end">{{ number_format($item['total'], 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $exchangeReturn = \App\Models\SaleReturn::with('items.product')->where('remarks', 'LIKE', '%Invoice #'.$sale->invoice_no.'%')->first();
            $exchangeReturnedAmount = 0;
            if ($exchangeReturn) {
                $exchangeReturnedAmount = $exchangeReturn->items->sum('line_total');
            }
        @endphp

        @if($exchangeReturn && $exchangeReturn->items->count() > 0)
        <div class="divider" style="border-top: 1px dashed #000; margin: 4px 0;"></div>
        <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px; color: #000;">Returned Items:</div>
        <table class="items-table" style="margin-bottom: 4px;">
            <tbody>
                @foreach ($exchangeReturn->items as $retItem)
                    <tr>
                        <td style="width: 6%;">{{ $loop->iteration }}</td>
                        <td style="width: 53%;">
                            @if($retItem->is_manual || empty($retItem->product_id))
                                <span class="item-name">{{ $retItem->product_name }} (Manual)</span>
                                @if(!empty($retItem->color) && $retItem->color !== '-' && $retItem->color !== '{')
                                    <span class="item-variant">{{ $retItem->color }}</span>
                                @endif
                            @else
                                <span class="item-name">{{ $retItem->product->item_name ?? 'Unknown' }}</span>
                                @php
                                    $retColorStr = '';
                                    if (!empty($retItem->color)) {
                                        $decoded = base64_decode($retItem->color, true);
                                        if ($decoded !== false && is_string($decoded) && str_starts_with(trim($decoded), '{')) {
                                            $parsed = json_decode($decoded, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                                                $parts = [];
                                                if (!empty($parsed['size']) && $parsed['size'] !== '-') $parts[] = $parsed['size'];
                                                if (!empty($parsed['color']) && $parsed['color'] !== '-') $parts[] = $parsed['color'];
                                                $retColorStr = implode(' | ', $parts);
                                            } else {
                                                $retColorStr = $retItem->color;
                                            }
                                        } else {
                                            $retColorStr = $retItem->color;
                                        }
                                    }
                                @endphp
                                @if($retColorStr)
                                    <span class="item-variant">{{ $retColorStr }}</span>
                                @endif
                            @endif
                        </td>
                        <td style="width: 10%;" class="text-center">{{ (float)$retItem->qty }}</td>
                        <td style="width: 13%;" class="text-end">{{ number_format($retItem->price, 0) }}</td>
                        <td style="width: 18%;" class="text-end">-{{ number_format($retItem->line_total, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Totals -->
        <div class="totals-section">
            <div class="tot-row">
                <span>Sub Total:</span>
                <span>{{ number_format($sale->total_bill_amount, 0) }}</span>
            </div>

            @if ($sale->total_extradiscount > 0)
                <div class="tot-row">
                    <span>Discount:</span>
                    <span>- {{ number_format($sale->total_extradiscount, 0) }}</span>
                </div>
            @endif

            @if ($exchangeReturnedAmount > 0)
                <div class="tot-row">
                    <span>Return Value:</span>
                    <span>- {{ number_format($exchangeReturnedAmount, 0) }}</span>
                </div>
            @endif

            @php
                $finalPayable = $sale->total_net - $exchangeReturnedAmount;
            @endphp
            <div class="tot-row grand-total">
                @if($finalPayable < 0)
                    <span>REFUND TO CUSTOMER:</span>
                    <span>{{ number_format(abs($finalPayable), 0) }}</span>
                @else
                    <span>TOTAL PAYABLE:</span>
                    <span>{{ number_format($finalPayable, 0) }}</span>
                @endif
            </div>
        </div>

        <!-- Ledger -->
        <div class="balance-section">
            @if(!$isWalkin)
            <div class="tot-row">
                <span>Prev Balance:</span>
                <span>{{ number_format(abs($previousBalance), 0) }} {{ $previousBalance >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
            @endif
            <div class="tot-row">
                <span>Paid Amount:</span>
                <span>{{ number_format($sale->cash, 0) }}</span>
            </div>
            @if($sale->change > 0)
            <div class="tot-row">
                <span>Change{{ $sale->change_account ? ' ('.$sale->change_account->title.')' : '' }}:</span>
                <span>{{ number_format($sale->change, 0) }}</span>
            </div>
            @endif

            {{-- @if(!$isWalkin)
            @php
                $finalBalance = $previousBalance + $sale->total_net - $sale->cash;
            @endphp
            <div class="tot-row closing-bal">
                <span>CLOSING BALANCE:</span>
                <span>{{ number_format(abs($finalBalance), 0) }} {{ $finalBalance >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
            @endif --}}
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <div class="soft-credit">Powered by Prowave Technologies<br>📞 +92 317 3836 223</div>
        </div>
    </div>

    <script>
        function triggerPrint() {
            window.print();
        }

        function handleGoBack() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('from') === 'pos' || (document.referrer && document.referrer.indexOf('/pos') !== -1)) {
                window.location.href = "{{ route('pos.index') }}";
                return;
            }

            if (window.opener && !window.opener.closed) {
                window.close();
                setTimeout(function() {
                    window.location.href = "{{ route('sale.index') }}";
                }, 150);
                return;
            }

            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1 && !document.referrer.includes('/sales/store')) {
                window.history.back();
                return;
            }

            window.location.href = "{{ route('sale.index') }}";
        }

        window.addEventListener('DOMContentLoaded', () => {
            // Automatically open print dialog on page load
            setTimeout(() => {
                try {
                    window.print();
                } catch(e){}
            }, 400);
        });
    </script>
</body>

</html>
