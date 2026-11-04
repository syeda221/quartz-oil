<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_no }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #2563eb;
            --accent-soft: #eff6ff;
            --border: #cbd5e1;
            --border-light: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-page: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Action Bar */
        .action-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .action-bar .btn {
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        /* A4 Page Container */
        .invoice-page {
            max-width: 210mm;
            margin: 24px auto;
            background: #ffffff;
            padding: 28px 32px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            position: relative;
        }

        /* Header Styling */
        .invoice-header {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .company-name {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .company-details {
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .company-details i {
            width: 14px;
            color: var(--primary-light);
        }

        .invoice-badge-title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--primary);
            text-align: right;
            margin-bottom: 4px;
        }

        .invoice-meta-top {
            font-size: 12px;
            text-align: right;
            color: var(--text-muted);
        }

        .invoice-meta-top .meta-highlight {
            font-weight: 700;
            color: var(--primary);
            font-size: 13px;
        }

        /* Info Boxes */
        .info-card {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 14px;
            background-color: #ffffff;
            height: 100%;
        }

        .info-card-header {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--primary);
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-row {
            display: flex;
            font-size: 11.5px;
            margin-bottom: 3px;
            line-height: 1.35;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 75px;
            flex-shrink: 0;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-main);
            flex-grow: 1;
            word-break: break-word;
        }

        /* Return Note Banner */
        .return-note-banner {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11.5px;
            color: #92400e;
            margin-bottom: 14px;
        }

        /* Main Table */
        .table-container {
            margin-top: 14px;
            margin-bottom: 16px;
            width: 100%;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .invoice-table thead th {
            background-color: var(--primary);
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            border: 1px solid var(--primary);
            vertical-align: middle;
        }

        .invoice-table tbody td {
            border: 1px solid var(--border);
            padding: 6px 8px;
            vertical-align: middle;
            color: var(--text-main);
        }

        .invoice-table tbody tr:nth-of-type(even) {
            background-color: #f8fafc;
        }

        .item-title {
            font-weight: 700;
            font-size: 12px;
            color: var(--text-main);
            line-height: 1.3;
        }

        .item-subtitle {
            font-size: 10.5px;
            color: var(--text-muted);
            margin-top: 2px;
            line-height: 1.2;
        }

        .item-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid var(--border-light);
            color: #334155;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 5px;
            border-radius: 3px;
            margin-right: 4px;
        }

        /* Exchange Return Table */
        .exchange-table thead th {
            background-color: #475569;
            color: #ffffff;
            border: 1px solid #475569;
        }

        /* Summary & Footer */
        .bottom-section {
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .terms-card {
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 8px 12px;
            background: #fafafa;
            height: 100%;
        }

        .terms-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .terms-list {
            padding-left: 16px;
            margin-bottom: 0;
            font-size: 10.5px;
            color: #475569;
            line-height: 1.4;
        }

        .terms-list li {
            margin-bottom: 2px;
        }

        .words-amount-box {
            margin-top: 8px;
            padding: 5px 10px;
            background-color: #f1f5f9;
            border-left: 3px solid var(--primary);
            border-radius: 0 4px 4px 0;
            font-size: 11px;
            color: #334155;
            line-height: 1.3;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sig-block {
            text-align: center;
            width: 170px;
        }

        .sig-line {
            border-top: 1px solid #000000;
            margin-bottom: 5px;
        }

        .sig-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-main);
        }

        .printed-time {
            font-size: 9.5px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* Financial Totals Table */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }

        .totals-table td {
            padding: 5px 10px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .tot-label {
            color: var(--text-muted);
            font-weight: 600;
        }

        .tot-val {
            text-align: right;
            font-weight: 600;
            color: var(--text-main);
        }

        .grand-total-row td {
            background-color: #f8fafc;
            border-top: 2px solid var(--primary);
            border-bottom: 2px solid var(--primary);
            font-weight: 800 !important;
            font-size: 13.5px !important;
            color: var(--primary) !important;
            padding: 7px 10px;
        }

        .closing-balance-row td {
            background-color: #0f172a;
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 13px !important;
            padding: 7px 10px;
        }

        .closing-balance-row .badge-balance {
            background-color: #ffffff;
            color: #0f172a;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 800;
            margin-left: 4px;
        }

        .avoid-page-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Print Specifics for A4 */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 11.5px !important;
            }

            .no-print,
            .action-bar {
                display: none !important;
            }

            .invoice-page {
                max-width: 100% !important;
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            .invoice-table thead th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .invoice-table tbody tr:nth-of-type(even) {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .grand-total-row td {
                background-color: #f1f5f9 !important;
                border-top: 2px solid #000 !important;
                border-bottom: 2px solid #000 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .closing-balance-row td {
                background-color: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .closing-balance-row .badge-balance {
                background-color: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .words-amount-box,
            .return-note-banner,
            .terms-card {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <!-- Action Bar (Screen Only) -->
    <div class="action-bar no-print d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-file-invoice me-1 text-primary"></i>
                Invoice Preview
            </span>
            <span class="badge bg-secondary">A4 Size</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-print"></i>
                Print Invoice (A4)
            </button>

            @if(Route::has('sales.receipt'))
                <a href="{{ route('sales.receipt', $sale->id) }}" target="_blank" class="btn btn-outline-dark shadow-sm">
                    <i class="fa-solid fa-receipt"></i>
                    Thermal Receipt
                </a>
            @endif

            <a href="javascript:void(0)" onclick="handleGoBack()" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>

    <!-- A4 Invoice Sheet -->
    <div class="invoice-page">

        <!-- Header -->
        <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-7">
                        @if(!($isEstimate ?? false))
                            <div class="company-name">
                                {{ \App\Models\Setting::get('company_name', 'Prowave Technologies') }}
                            </div>
                            <div class="company-details">
                                <div><i class="fa-solid fa-location-dot"></i> {{ \App\Models\Setting::get('company_address', 'Hyderabad') }}</div>
                                <div><i class="fa-solid fa-phone"></i> {{ \App\Models\Setting::get('company_phone', '0327-9226901') }}</div>
                                @if(\App\Models\Setting::get('company_email'))
                                    <div><i class="fa-solid fa-envelope"></i> {{ \App\Models\Setting::get('company_email') }}</div>
                                @endif
                            </div>
                        @else
                            <div class="company-name">
                                {{ \App\Models\Setting::get('company_name', 'Prowave Technologies') }}
                            </div>
                            <div class="company-details">
                                <div>{{ \App\Models\Setting::get('company_address', 'Hyderabad') }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="col-5">
                        <div class="invoice-badge-title">
                            {{ ($isEstimate ?? false) ? 'Estimate' : 'Sales Invoice' }}
                        </div>
                        <div class="invoice-meta-top">
                            <div>Invoice #: <span class="meta-highlight">{{ $sale->invoice_no }}</span></div>
                            <div>Date: <span class="meta-highlight">{{ $sale->created_at ? $sale->created_at->format('d/m/Y') : date('d/m/Y') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Invoice Info Cards -->
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="info-card">
                        <div class="info-card-header">
                            <span><i class="fa-solid fa-user me-1"></i> Customer Details</span>
                            @if($sale->customer_relation?->customer_id)
                                <span class="badge bg-light text-dark border">Code: {{ $sale->customer_relation->customer_id }}</span>
                            @endif
                        </div>

                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ $sale->walkin_name ?? ($sale->customer_relation->customer_name ?? 'Walk-in Customer') }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value">{{ $sale->customer_relation->mobile ?? '—' }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value">{{ $sale->customer_relation->address ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="info-card">
                        <div class="info-card-header">
                            <span><i class="fa-solid fa-file-lines me-1"></i> Invoice Details</span>
                            <span class="badge bg-light text-dark border">{{ $sale->created_at ? $sale->created_at->format('h:i A') : date('h:i A') }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Invoice #:</span>
                            <span class="info-value fw-bold text-primary">{{ $sale->invoice_no }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Issue Date:</span>
                            <span class="info-value">{{ $sale->created_at ? $sale->created_at->format('d M, Y') : date('d M, Y') }}</span>
                        </div>

                        @if(!empty($sale->customer_relation?->salesOfficer?->name) || auth()->check())
                            <div class="info-row">
                                <span class="info-label">Sales Rep:</span>
                                <span class="info-value">{{ $sale->customer_relation->salesOfficer->name ?? auth()->user()->name }}</span>
                            </div>
                        @endif

                        @if($sale->reference)
                            <div class="info-row">
                                <span class="info-label">Remarks:</span>
                                <span class="info-value text-muted">{{ $sale->reference }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Return Note Banner if present -->
            @if ($sale->return_note)
                <div class="return-note-banner">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Return Note:</strong> {{ $sale->return_note }}
                </div>
            @endif

            <!-- Main Items Table -->
            <div class="table-container">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">Sr.</th>
                            <th class="text-start" style="width: 40%;">Item Description</th>
                            <th class="text-center" style="width: 14%;">Qty / Shipped</th>
                            <th class="text-center" style="width: 10%;">UOM</th>
                            <th class="text-end" style="width: 11%;">Rate</th>
                            <th class="text-end" style="width: 9%;">Disc</th>
                            <th class="text-end" style="width: 11%;">Net Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalCartonsCount = 0;
                            $totalLooseCount = 0;
                            $totalPiecesCount = 0;
                        @endphp

                        @foreach ($saleItems as $item)
                            @php
                                $height = (float)($item['height'] ?? 0);
                                $width = (float)($item['width'] ?? 0);
                                $m2PerPiece = ($height > 0 && $width > 0) ? ($height * $width) / 10000 : 0;
                                
                                $piecesPerBox = (int)($item['pieces_per_box'] ?? 1);
                                if ($piecesPerBox <= 0) $piecesPerBox = 1;
                                
                                $totalPieces = (int)($item['total_pieces'] ?? 0);
                                $totalPiecesCount += $totalPieces;

                                $boxes = floor($totalPieces / $piecesPerBox);
                                $loosePieces = $totalPieces % $piecesPerBox;
                                $totalM2Line = $m2PerPiece * $totalPieces;

                                $sizeMode = $item['size_mode'] ?? 'by_size';
                                $variantUnit = strtolower($item['variant_unit'] ?? '');
                                $weightGrams = (float)($item['weight_per_piece'] ?? 0);

                                if ($sizeMode === 'by_cartons' || $variantUnit === 'carton' || $variantUnit === 'ctn') {
                                    $totalCartonsCount += $boxes;
                                    $totalLooseCount += $loosePieces;
                                }

                                // Format item titles & labels
                                $vName = $item['variant_name'] ?? '';
                                $vSize = (!empty($item['size_val']) && $item['size_val'] !== '-') ? $item['size_val'] : '';
                                $vColor = (!empty($item['color_val']) && $item['color_val'] !== '-') ? $item['color_val'] : '';
                                
                                $vExtra = [];
                                if ($vSize) $vExtra[] = $vSize;
                                if ($vColor) $vExtra[] = $vColor;
                                $vExtraStr = count($vExtra) > 0 ? ' (' . implode(', ', $vExtra) . ')' : '';

                                $productTitle = $item['item_name'];
                                if ($vName && strtolower(trim($vName)) !== strtolower(trim($productTitle))) {
                                    $productTitle .= ' — ' . $vName;
                                }
                                $productTitle .= $vExtraStr;
                            @endphp

                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                
                                <td class="text-start">
                                    <div class="item-title">{{ $productTitle }}</div>
                                    <div class="item-subtitle">
                                        @if ($sizeMode == 'by_size' && $height > 0 && $width > 0)
                                            <span class="item-badge">Dims: {{ number_format($width, 0) }}x{{ number_format($height, 0) }}</span>
                                        @endif
                                        @if ($piecesPerBox > 1)
                                            <span class="item-badge">Pack: {{ $piecesPerBox }} pcs/box</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if ($variantUnit === 'pcs' || $variantUnit === 'piece' || $variantUnit === 'pieces')
                                        <span class="fw-bold">{{ $totalPieces }} Pcs</span>
                                        @if ($weightGrams > 0)
                                            <small class="text-muted d-block" style="font-size: 10px;">({{ $weightGrams == (int)$weightGrams ? (int)$weightGrams : $weightGrams }}g)</small>
                                        @endif
                                    @elseif (in_array($sizeMode, ['by_kg', 'by_gm', 'by_feet', 'by_meter']))
                                        @php
                                            $uomLabel = match($sizeMode) {
                                                'by_kg' => 'Kg',
                                                'by_gm' => 'Gm',
                                                'by_feet' => 'Ft',
                                                'by_meter' => 'Meter',
                                                default => '',
                                            };
                                            $qtyVal = (float)($item['qty_box'] ?? $item['qty'] ?? $totalPieces);
                                            $displayQty = ($qtyVal == (int)$qtyVal) ? (int)$qtyVal : number_format($qtyVal, 3);
                                        @endphp
                                        <span class="fw-bold">{{ $displayQty }} {{ $uomLabel }}</span>
                                    @else
                                        <div class="fw-bold">
                                            @if ($sizeMode == 'by_pieces')
                                                {{ $totalPieces }} Pcs
                                            @else
                                                @if ($boxes > 0 && $loosePieces > 0)
                                                    {{ $boxes }} {{ $sizeMode == 'by_cartons' ? 'Ctn' : 'Box' }} + {{ $loosePieces }} Pc
                                                @elseif ($boxes > 0)
                                                    {{ $boxes }} {{ $sizeMode == 'by_cartons' ? 'Ctn' : 'Box' }}
                                                @else
                                                    {{ $loosePieces }} Pcs
                                                @endif
                                            @endif
                                        </div>
                                        <small class="text-muted" style="font-size: 10px;">({{ $totalPieces }} pcs)</small>
                                    @endif
                                </td>

                                <td class="text-center fw-semibold">
                                    @if (!empty($item['variant_unit']))
                                        {{ ucfirst($item['variant_unit']) }}
                                    @elseif ($sizeMode == 'by_pieces')
                                        Pieces
                                    @elseif ($sizeMode == 'by_cartons')
                                        Cartons
                                    @elseif ($sizeMode == 'by_size')
                                        {{ number_format($totalM2Line, 2) }} m²
                                    @else
                                        Pieces
                                    @endif
                                </td>

                                <td class="text-end">
                                    {{ number_format($item['price'], 2) }}
                                </td>

                                <td class="text-end">
                                    @php
                                        $discAmt = (float)($item['discount_amount'] ?? 0);
                                        $discPct = (float)($item['discount_percent'] ?? 0);
                                    @endphp
                                    @if ($discAmt > 0)
                                        <span class="text-danger fw-semibold">{{ number_format($discAmt, 2) }}</span>
                                        @if ($discPct > 0)
                                            <small class="text-muted d-block" style="font-size: 9.5px;">({{ number_format($discPct, 1) }}%)</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-end fw-bold">
                                    {{ number_format($item['total'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Exchange Return Section if exists -->
            @php
                $exchangeReturn = \App\Models\SaleReturn::with('items.product')
                    ->where('remarks', 'LIKE', '%Invoice #'.$sale->invoice_no.'%')
                    ->first();

                $exchangeReturnedAmount = 0;
                if ($exchangeReturn) {
                    $exchangeReturnedAmount = $exchangeReturn->items->sum('line_total');
                }
            @endphp

            @if($exchangeReturn && $exchangeReturn->items->count() > 0)
                <div class="mb-3 avoid-page-break">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-danger">
                            <i class="fa-solid fa-arrow-rotate-left me-1"></i>
                            Returned Items (Exchange Deduction)
                        </span>
                        <span class="badge bg-danger">Return #{{ $exchangeReturn->return_invoice ?? $exchangeReturn->id }}</span>
                    </div>

                    <table class="invoice-table exchange-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">Sr.</th>
                                <th class="text-start" style="width: 40%;">Item Description</th>
                                <th class="text-center" style="width: 14%;">Qty</th>
                                <th class="text-center" style="width: 10%;">UOM</th>
                                <th class="text-end" style="width: 11%;">Rate</th>
                                <th class="text-end" style="width: 9%;">Disc</th>
                                <th class="text-end" style="width: 11%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($exchangeReturn->items as $retItem)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">
                                        <span class="item-title">{{ $retItem->product->item_name ?? ($retItem->product_name ?? 'Unknown') }}</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ (float)$retItem->qty }} Pcs</td>
                                    <td class="text-center">Pieces</td>
                                    <td class="text-end">{{ number_format($retItem->price, 2) }}</td>
                                    <td class="text-end text-muted">—</td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($retItem->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Footer / Financial Breakdown Section -->
            <div class="bottom-section avoid-page-break">
            <div class="row g-3">
                <!-- Terms & Signatures (Left) -->
                <div class="col-7 d-flex flex-column justify-content-between">
                    <div>
                        <div class="terms-card">
                            <div class="terms-title">
                                <i class="fa-solid fa-shield-halved me-1"></i> Terms & Conditions
                            </div>
                            <ul class="terms-list">
                                @php
                                    $invoiceTerms = \App\Models\Setting::get(
                                        'invoice_terms',
                                        "10% will be deducted on return of purchased goods within 7 days.\nLoose & Water Soaked products will not be RETURNED.\nPlease bring this invoice for any returns or exchanges."
                                    );
                                    $termLines = explode("\n", $invoiceTerms);
                                @endphp
                                @foreach($termLines as $line)
                                    @if(trim($line))
                                        <li>{{ trim($line) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        @if(!empty($sale->total_amount_Words))
                            <div class="words-amount-box">
                                <strong>Amount in Words:</strong> {{ $sale->total_amount_Words }}
                            </div>
                        @endif
                    </div>

                    <div class="signature-section">
                        <div class="sig-block">
                            <div class="sig-line"></div>
                            <div class="sig-title">Customer Signature</div>
                        </div>

                        <div class="sig-block">
                            <div class="sig-line"></div>
                            <div class="sig-title">Authorized Signature</div>
                            <div class="printed-time">Printed: {{ date('d/m/Y h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Financial Totals (Right) -->
                <div class="col-5">
                    @php
                        $grossTotal = collect($saleItems)->sum('total');
                        $totalDisc = collect($saleItems)->sum('discount_amount') + (float)($sale->total_extradiscount ?? 0);
                        $netBill = (float)$sale->total_net;
                        $paidAmount = (float)($sale->cash ?? 0);
                        $finalPayable = $netBill - $exchangeReturnedAmount;
                        $finalBal = $previousBalance + $finalPayable - $paidAmount;
                    @endphp

                    <table class="totals-table">
                        @if ($totalCartonsCount > 0)
                            <tr>
                                <td class="tot-label">Total Cartons</td>
                                <td class="tot-val text-primary">
                                    @if ($totalLooseCount > 0)
                                        {{ $totalCartonsCount }} Ctn + {{ $totalLooseCount }} Pcs
                                    @else
                                        {{ $totalCartonsCount }} Cartons
                                    @endif
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td class="tot-label">Gross Subtotal</td>
                            <td class="tot-val">{{ number_format($grossTotal + $totalDisc, 2) }}</td>
                        </tr>

                        @if ($totalDisc > 0)
                            <tr>
                                <td class="tot-label text-danger">Total Discount</td>
                                <td class="tot-val text-danger">- {{ number_format($totalDisc, 2) }}</td>
                            </tr>
                        @endif

                        @if ($exchangeReturnedAmount > 0)
                            <tr>
                                <td class="tot-label text-danger">Exchange Return</td>
                                <td class="tot-val text-danger">- {{ number_format($exchangeReturnedAmount, 2) }}</td>
                            </tr>
                        @endif

                        <tr class="grand-total-row">
                            <td>{{ $finalPayable < 0 ? 'Refund To Customer' : 'Net Bill Amount' }}</td>
                            <td class="text-end">{{ number_format(abs($finalPayable), 2) }}</td>
                        </tr>

                        {{-- @if (round(abs($previousBalance), 2) > 0)
                            <tr>
                                <td class="tot-label">Previous Balance</td>
                                <td class="tot-val">
                                    {{ number_format(abs($previousBalance), 2) }}
                                    <small class="text-muted fw-bold">({{ $previousBalance >= 0 ? 'Dr' : 'Cr' }})</small>
                                </td>
                            </tr>
                        @endif --}}

                        {{-- <tr>
                            <td class="tot-label text-success">Paid / Cash Received</td>
                            <td class="tot-val text-success">{{ number_format($paidAmount, 2) }}</td>
                        </tr>

                        @if ($sale->change > 0)
                            <tr>
                                <td class="tot-label text-danger">Change Returned</td>
                                <td class="tot-val text-danger">{{ number_format($sale->change, 2) }}</td>
                            </tr>
                        @endif

                        @if(!empty($sale->customer_id) || round(abs($finalBal), 2) > 0)
                            <tr class="closing-balance-row">
                                <td>Closing Balance</td>
                                <td class="text-end">
                                    {{ number_format(abs($finalBal), 2) }}
                                    <span class="badge-balance">{{ $finalBal >= 0 ? 'Dr' : 'Cr' }}</span>
                                </td>
                            </tr>
                        @endif --}}
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Navigation Script -->
    <script>
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
    </script>
</body>

</html>