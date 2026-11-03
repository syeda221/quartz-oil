@extends('admin_panel.layout.app')
@section('content')

<style>
    /* ── LAYOUT RESET: make sure page uses full width cleanly ── */
    .erp-page { background: #f8fafc; min-height: calc(100vh - 80px); padding: 20px 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .erp-page .container-fluid { max-width: 100%; box-sizing: border-box; }

    /* ── ERP Product Page – Premium Design System ── */
    :root {
        --erp-primary:    #6366f1;
        --erp-primary-lt: #eef2ff;
        --erp-success:    #10b981;
        --erp-success-lt: #ecfdf5;
        --erp-warning:    #f59e0b;
        --erp-warning-lt: #fffbeb;
        --erp-danger:     #ef4444;
        --erp-danger-lt:  #fef2f2;
        --erp-info:       #3b82f6;
        --erp-info-lt:    #eff6ff;
        --erp-border:     #e2e8f0;
        --erp-bg:         #f8fafc;
        --erp-card-bg:    #ffffff;
        --erp-text:       #0f172a;
        --erp-muted:      #64748b;
        --erp-radius:     14px;
        --erp-shadow:     0 2px 8px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.02);
        --erp-shadow-md:  0 8px 30px rgba(15,23,42,0.08);
    }

    /* ── Stats Cards ── */
    .stat-card {
        background: var(--erp-card-bg);
        border-radius: var(--erp-radius);
        border: 1px solid var(--erp-border);
        box-shadow: var(--erp-shadow);
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--erp-shadow-md); }
    .stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .stat-card .stat-label { font-size: .73rem; font-weight: 700; color: var(--erp-muted); text-transform: uppercase; letter-spacing: .5px; }
    .stat-card .stat-value { font-size: 1.4rem; font-weight: 800; color: var(--erp-text); line-height: 1.2; }
    .stat-card .stat-sub   { font-size: .72rem; color: var(--erp-muted); margin-top: 2px; }

    /* ── Main Card ── */
    .erp-card {
        background: var(--erp-card-bg);
        border-radius: var(--erp-radius);
        border: 1px solid var(--erp-border);
        box-shadow: var(--erp-shadow);
        overflow: hidden;
    }
    .erp-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--erp-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .erp-card-header .page-title { font-size: 1.05rem; font-weight: 800; color: var(--erp-text); margin: 0; display: flex; align-items: center; gap: 8px; }
    .erp-card-header .page-sub   { font-size: .78rem; color: var(--erp-muted); margin: 2px 0 0 0; }
    
    /* ── Header action buttons group ── */
    .erp-hdr-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* ── Filter Panel ── */
    .filter-panel {
        background: #f8fafc;
        border-bottom: 1px solid var(--erp-border);
        padding: 16px 20px;
    }
    .filter-panel .filter-heading {
        font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px;
        color: var(--erp-muted); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
    }
    .filter-panel label.form-label {
        font-size: .72rem; font-weight: 700; color: var(--erp-muted);
        text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px;
    }

    .erp-filter-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        width: 100%;
    }
    .erp-filter-field {
        display: flex;
        flex-direction: column;
        flex: 1 1 150px;
        min-width: 130px;
        max-width: 240px;
        box-sizing: border-box;
    }
    .erp-filter-search { flex: 1 1 220px; max-width: 320px; }
    .erp-filter-btns   { flex: 0 0 auto; max-width: none; min-width: 0; }

    .erp-flabel {
        display: block;
        font-size: .69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--erp-muted);
        margin-bottom: 5px;
        white-space: nowrap;
    }
    .erp-finput {
        display: block;
        width: 100%;
        height: 38px;
        padding: 0 12px;
        border: 1px solid var(--erp-border);
        border-radius: 8px;
        font-size: .83rem;
        font-weight: 500;
        color: var(--erp-text);
        background: #fff;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        box-sizing: border-box;
    }
    .erp-finput:focus {
        border-color: var(--erp-primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .search-wrap { position: relative; width: 100%; }
    .search-wrap .search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        color: var(--erp-muted); font-size: 13px; pointer-events: none;
        z-index: 1;
    }
    .search-wrap .erp-finput { padding-left: 34px; }

    /* Filter action buttons */
    .btn-erp-filter {
        background: var(--erp-primary); color: #fff; border: none;
        border-radius: 8px; height: 38px; padding: 0 16px;
        font-size: .83rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
        transition: background .15s, transform .1s; cursor: pointer;
    }
    .btn-erp-filter:hover { background: #4f46e5; color: #fff; transform: translateY(-1px); }
    .btn-erp-clear {
        background: #fff; color: var(--erp-muted); border: 1px solid var(--erp-border);
        border-radius: 8px; height: 38px; padding: 0 14px;
        font-size: .83rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;
        transition: all .15s; text-decoration: none; cursor: pointer;
    }
    .btn-erp-clear:hover { border-color: var(--erp-danger); color: var(--erp-danger); background: var(--erp-danger-lt); }

    /* Active filter badges */
    .active-filters { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
    .filter-chip {
        background: var(--erp-primary-lt); color: var(--erp-primary);
        border: 1px solid #c7d2fe; border-radius: 20px;
        padding: 3px 10px; font-size: .72rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: 4px;
    }

    /* ── Header action buttons ── */
    .btn-hdr {
        border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px; transition: all .15s;
        border: 1px solid transparent; text-decoration: none; cursor: pointer;
    }
    .btn-hdr-outline { background: #fff; color: var(--erp-muted); border-color: var(--erp-border); }
    .btn-hdr-outline:hover { border-color: #94a3b8; color: var(--erp-text); background: var(--erp-bg); }
    .btn-hdr-success { background: #10b981; color: #fff; border-color: #10b981; }
    .btn-hdr-success:hover { background: #059669; border-color: #059669; color: #fff; transform: translateY(-1px); }
    .btn-hdr-warning { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .btn-hdr-warning:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .btn-hdr-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border-color: #6366f1; box-shadow: 0 2px 6px rgba(99,102,241,0.25); }
    .btn-hdr-primary:hover { background: #4338ca; border-color: #4338ca; color: #fff; transform: translateY(-1px); }

    /* ── Table ── */
    .erp-table-wrap { padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    #productTable {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: .82rem;
    }
    #productTable thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: .67rem;
        letter-spacing: .5px;
        padding: 12px 14px;
        border-bottom: 2px solid var(--erp-border) !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        white-space: nowrap;
        position: sticky; top: 0; z-index: 2;
    }
    #productTable tbody td {
        padding: 10px 14px;
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: var(--erp-text);
        vertical-align: middle;
        white-space: nowrap;
    }
    #productTable tbody td.td-item-details { white-space: normal; min-width: 180px; max-width: 260px; }
    #productTable tbody tr { transition: background .12s ease; }
    #productTable tbody tr:hover { background: #f8fafc !important; }
    #productTable tbody tr.row-inactive { opacity: .6; background: #fafafa; }

    /* Image cell */
    .product-img {
        width: 40px; height: 40px; object-fit: cover;
        border-radius: 8px; border: 1px solid var(--erp-border);
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer; display: block;
    }
    .product-img:hover { transform: scale(1.35); z-index: 10; position: relative; box-shadow: var(--erp-shadow-md); }
    .no-img-badge {
        width: 40px; height: 40px; border-radius: 8px;
        background: #f1f5f9; border: 1px dashed #cbd5e1;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; color: #94a3b8;
    }

    /* Item details cell */
    .item-name { font-weight: 700; color: var(--erp-text); margin-bottom: 3px; font-size: .85rem; line-height: 1.3; }
    .item-meta { font-size: .7rem; color: var(--erp-muted); display: flex; flex-wrap: wrap; gap: 4px; align-items: center; margin-top: 2px; }
    .item-meta .meta-chip {
        background: #f1f5f9; border-radius: 4px; padding: 2px 6px;
        font-size: .67rem; font-weight: 600; color: #475569;
        display: inline-flex; align-items: center; gap: 3px;
    }
    .item-code { font-family: 'Courier New', monospace; background: #f8fafc; border: 1px solid var(--erp-border); border-radius: 4px; padding: 1px 6px; font-size: .7rem; font-weight: 600; color: #334155; }

    /* Stock badge */
    .stock-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--erp-success-lt); color: var(--erp-success);
        border: 1px solid #a7f3d0; border-radius: 6px;
        padding: 3px 8px; font-size: .75rem; font-weight: 700;
        white-space: nowrap;
    }
    .stock-badge.low  { background: var(--erp-danger-lt); color: var(--erp-danger); border-color: #fecaca; }
    .stock-badge.zero { background: #fef3c7; color: #b45309; border-color: #fde68a; }
    .stock-unit { font-weight: 500; font-size: .67rem; opacity: .85; }

    /* Price cells */
    .price-purchase { color: var(--erp-muted); font-weight: 600; font-size: .81rem; white-space: nowrap; }
    .price-sale     { color: var(--erp-success); font-weight: 800; font-size: .84rem; white-space: nowrap; }

    /* Status badge */
    .status-active {
        background: var(--erp-success-lt); color: var(--erp-success);
        border: 1px solid #a7f3d0; border-radius: 20px;
        padding: 3px 10px; font-size: .7rem; font-weight: 700;
        letter-spacing: .2px; white-space: nowrap;
    }
    .status-inactive {
        background: #f1f5f9; color: #64748b;
        border: 1px solid #cbd5e1; border-radius: 20px;
        padding: 3px 10px; font-size: .7rem; font-weight: 700;
        white-space: nowrap;
    }

    /* Action buttons – single row, compact */
    .action-group {
        display: flex; align-items: center; gap: 4px;
        flex-wrap: nowrap;
        justify-content: flex-start;
    }
    .btn-act {
        border-radius: 6px; padding: 4px 9px; font-size: .72rem;
        font-weight: 600; display: inline-flex; align-items: center; gap: 4px;
        border: 1px solid transparent; transition: all .12s; cursor: pointer;
        line-height: 1.5; white-space: nowrap; flex-shrink: 0;
    }
    .btn-act-view    { background: #e0f2fe; color: #0284c7; border-color: #bae6fd; }
    .btn-act-view:hover    { background: #0284c7; color: #fff; }
    .btn-act-edit    { background: var(--erp-primary-lt); color: var(--erp-primary); border-color: #c7d2fe; }
    .btn-act-edit:hover    { background: var(--erp-primary); color: #fff; }
    .btn-act-barcode { background: var(--erp-success-lt); color: var(--erp-success); border-color: #a7f3d0; }
    .btn-act-barcode:hover { background: var(--erp-success); color: #fff; }
    .btn-act-deact   { background: var(--erp-danger-lt); color: var(--erp-danger); border-color: #fecaca; }
    .btn-act-deact:hover   { background: var(--erp-danger); color: #fff; }
    .btn-act-act     { background: var(--erp-success-lt); color: var(--erp-success); border-color: #a7f3d0; }
    .btn-act-act:hover     { background: var(--erp-success); color: #fff; }

    /* ── Pagination ── */
    .erp-pagination { padding: 14px 20px; border-top: 1px solid var(--erp-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .erp-pagination .showing { font-size: .78rem; color: var(--erp-muted); }
    .erp-pagination .page-link { border-radius: 6px !important; border-color: var(--erp-border) !important; color: var(--erp-text) !important; font-size: .8rem; padding: 5px 12px; }
    .erp-pagination .page-item.active .page-link { background: var(--erp-primary) !important; border-color: var(--erp-primary) !important; color: #fff !important; }

    /* ── Actions column – force min-width so buttons never wrap ── */
    #productTable th:last-child,
    #productTable td:last-child { min-width: 190px; }

    /* ── Select checkbox ── */
    input[type="checkbox"].row-check { width: 16px; height: 16px; accent-color: var(--erp-primary); cursor: pointer; }

    /* ── DataTable override ── */
    div.dataTables_wrapper div.dataTables_length select { width: 75px !important; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { display: none !important; }
    .dataTables_wrapper { overflow-x: visible !important; }

    /* ════════════════════════════════════════════════════
       MOBILE RESPONSIVE  ≤ 768px
    ════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .erp-page { padding: 12px 0; }
        .container-fluid { padding-left: 10px !important; padding-right: 10px !important; }

        /* Stats: 2-col grid */
        .stat-grid { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
        .stat-card { padding: 12px; gap: 10px; }
        .stat-icon { width: 36px; height: 36px; font-size: 15px; border-radius: 10px; }
        .stat-card .stat-value { font-size: 1.15rem; }
        .stat-card .stat-label { font-size: .65rem; }
        .stat-card .stat-sub   { display: none; }

        /* Card header: title + buttons */
        .erp-card-header { flex-direction: column; align-items: flex-start; gap: 12px; padding: 14px; }
        .erp-hdr-actions {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
        }
        .btn-hdr {
            width: 100% !important;
            justify-content: center !important;
            text-align: center !important;
            font-size: .78rem !important;
            padding: 9px 10px !important;
            box-sizing: border-box !important;
            border-radius: 10px !important;
            height: 38px !important;
        }

        /* Filter panel: stack fields */
        .filter-panel { padding: 12px 14px; }
        .erp-filter-row { gap: 10px; }
        .erp-filter-field,
        .erp-filter-search { flex: 1 1 100%; max-width: 100%; }
        .erp-filter-btns { flex: 1 1 100%; width: 100%; }
        .erp-filter-btns > div { width: 100%; display: flex; gap: 8px; }
        .btn-erp-filter,
        .btn-erp-clear { flex: 1; text-align: center; justify-content: center; height: 40px; }

        /* Table wrap */
        .erp-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0;
        }
        #productTable {
            min-width: 720px !important;
            font-size: .8rem;
        }
        #productTable thead th { padding: 10px 10px; font-size: .64rem; }
        #productTable tbody td { padding: 8px 10px; }

        /* Pagination */
        .erp-pagination {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
        }
        .erp-pagination nav { width: 100%; }
    }

    /* ── Mobile Product Cards ── */
    .mobile-product-cards { display: none; padding: 14px; }

    @media (max-width: 768px) {
        .erp-table-wrap { display: none !important; }
        .mobile-product-cards { display: flex; flex-direction: column; gap: 12px; }
    }

    .prod-mcard {
        background: #ffffff;
        border: 1px solid var(--erp-border);
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 8px rgba(15,23,42,0.03);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .prod-mcard.row-inactive { opacity: 0.65; background: #f8fafc; }
    .prod-mcard-hd { display: flex; align-items: flex-start; gap: 12px; }
    .prod-mcard-body {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .prod-mcard-price { font-size: 1.05rem; font-weight: 800; color: #10b981; }
    .prod-mcard-actions {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 8px !important;
        margin-top: 12px;
        width: 100%;
    }
    .prod-mcard-actions .btn-act {
        width: 100% !important;
        justify-content: center !important;
        height: 38px !important;
        font-size: .78rem !important;
        border-radius: 8px !important;
    }

    @media (max-width: 480px) {
        .stat-grid { grid-template-columns: 1fr 1fr !important; gap: 8px !important; }
        .stat-card { padding: 10px; }
        .stat-card .stat-value { font-size: 1.05rem; }
        #productTable { min-width: 660px !important; }
        .btn-act { padding: 3px 6px; font-size: .68rem; }
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-3 py-3">

    {{-- ── Stats Row ── --}}
    <div class="stat-grid mb-4" style="display:grid; grid-template-columns: repeat(4,1fr); gap:16px;">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="fas fa-box-open"></i></div>
            <div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value">{{ $products->total() }}</div>
                <div class="stat-sub">in catalog</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5; color:#059669;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-label">Active</div>
                <div class="stat-value">{{ $products->getCollection()->where('is_active',1)->count() }}</div>
                <div class="stat-sub">on this page</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="stat-label">Inactive</div>
                <div class="stat-value">{{ $products->getCollection()->where('is_active',0)->count() }}</div>
                <div class="stat-sub">on this page</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="fas fa-filter"></i></div>
            <div>
                <div class="stat-label">Filtered Results</div>
                <div class="stat-value">{{ $products->total() }}</div>
                <div class="stat-sub">
                    @if(request()->hasAny(['search','category_id','brand_id','status']))
                        <span style="color:#d97706; font-weight:700;">Filters active</span>
                    @else
                        all products
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="erp-card">

        {{-- Card Header --}}
        <div class="erp-card-header">
            <div>
                <p class="page-title"><i class="fas fa-box me-2" style="color:var(--erp-primary);"></i>Product Catalog</p>
                <p class="page-sub">Manage, filter and bulk-edit your entire product inventory</p>
            </div>
            <div class="erp-hdr-actions">
                <a href="{{ route('products.template') }}" class="btn-hdr btn-hdr-outline" title="Download blank CSV template">
                    <i class="fas fa-file-csv"></i> Template
                </a>
                <a href="{{ route('products.export') }}" class="btn-hdr btn-hdr-success" title="Export all products to CSV">
                    <i class="fas fa-file-download"></i> Export CSV
                </a>
                @if (auth()->user()->can('products.create') || auth()->user()->email === 'admin@admin.com')
                    <button type="button" class="btn-hdr btn-hdr-warning" id="openImportModalBtn">
                        <i class="fas fa-file-upload"></i> Import CSV
                    </button>
                    <a href="create_prodcut" class="btn-hdr btn-hdr-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                @endif
            </div>
        </div>


        {{-- ── Filter Panel ── --}}
        <div class="filter-panel">
            <div class="filter-heading"><i class="fas fa-sliders-h"></i> Filters &amp; Search</div>
            <form method="GET" action="{{ route('product') }}" id="filterForm">
                <div class="erp-filter-row">

                    {{-- Search --}}
                    <div class="erp-filter-field erp-filter-search">
                        <label class="erp-flabel">Search</label>
                        <div class="search-wrap">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="erp-finput" placeholder="Item name, code, barcode…">
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="erp-filter-field">
                        <label class="erp-flabel">Category</label>
                        <select name="category_id" class="erp-finput">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand --}}
                    <div class="erp-filter-field">
                        <label class="erp-flabel">Brand</label>
                        <select name="brand_id" class="erp-finput">
                            <option value="">All Brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="erp-filter-field" style="min-width:120px;">
                        <label class="erp-flabel">Status</label>
                        <select name="status" class="erp-finput">
                            <option value="">All Status</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>✅ Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>⛔ Inactive</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="erp-filter-field erp-filter-btns">
                        <label class="erp-flabel">&nbsp;</label>
                        <div style="display:flex; gap:6px;">
                            <button type="submit" class="btn-erp-filter">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('product') }}" class="btn-erp-clear">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Active filter chips --}}
                @if(request()->hasAny(['search','category_id','brand_id','status']))
                <div class="active-filters" style="margin-top:10px;">
                    <span style="font-size:.72rem; font-weight:600; color:var(--erp-muted);">Active:</span>
                    @if(request('search'))
                        <span class="filter-chip"><i class="fas fa-search" style="font-size:.6rem;"></i> "{{ request('search') }}"</span>
                    @endif
                    @if(request('category_id'))
                        <span class="filter-chip"><i class="fas fa-list" style="font-size:.6rem;"></i> {{ $categories->firstWhere('id', request('category_id'))->name ?? 'Category' }}</span>
                    @endif
                    @if(request('brand_id'))
                        <span class="filter-chip"><i class="fas fa-trademark" style="font-size:.6rem;"></i> {{ $brands->firstWhere('id', request('brand_id'))->name ?? 'Brand' }}</span>
                    @endif
                    @if(request('status'))
                        <span class="filter-chip"><i class="fas fa-circle" style="font-size:.6rem;"></i> {{ ucfirst(request('status')) }}</span>
                    @endif
                    <span style="font-size:.72rem; color:var(--erp-muted);">— <strong>{{ $products->total() }}</strong> result(s)</span>
                </div>
                @endif
            </form>
        </div>

        {{-- ── Success Alert ── --}}
        @if (session()->has('success'))
        <div class="mx-4 mt-3 alert d-flex align-items-center gap-2" style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; color:#065f46; font-size:.85rem; padding:10px 14px;">
            <i class="fas fa-check-circle" style="color:#059669; font-size:16px;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size:.6rem;"></button>
        </div>
        @endif


        {{-- ── Table ── --}}
        <div class="erp-table-wrap table-responsive">

                <table id="productTable" class="table table-hover align-middle nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="selectAll" class="row-check"></th>
                            <th style="width:40px;">#</th>
                            <th style="width:52px;">Image</th>
                            <th>Item Details</th>
                            <th>Stock</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th style="width:90px;">Status</th>
                            <th class="text-center" style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            @php
                                $stockPieces = (float) ($product->warehouse_stocks_sum_total_pieces ?? 0);
                                $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                                if (($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') && $ppb > 1) {
                                    $boxes = floor($stockPieces / $ppb);
                                    $loose = $stockPieces % $ppb;
                                    $stockDisplay = $loose > 0 ? "{$boxes}.{$loose}" : "{$boxes}";
                                    $stockUnit    = $loose > 0 ? 'Box.Loose' : 'Boxes';
                                } else {
                                    $stockDisplay = $stockPieces;
                                    $stockUnit    = 'Pcs';
                                }
                                $stockClass = $stockPieces == 0 ? 'zero' : (($product->alert_carton_quantity && $stockPieces <= $product->alert_carton_quantity) ? 'low' : '');

                                if ($product->size_mode === 'by_size') {
                                    $m2 = ($product->height * $product->width) / 10000;
                                    $tradePrice  = $m2 * (float)$product->purchase_price_per_m2;
                                    $retailPrice = $m2 * (float)$product->price_per_m2;
                                } else {
                                    $tradePrice  = (float)$product->purchase_price_per_piece;
                                    $retailPrice = (float)$product->sale_price_per_piece ?: (float)$product->sale_price_per_box;
                                }
                            @endphp
                            <tr id="product-row-{{ $product->id }}" class="{{ $product->is_active ? '' : 'row-inactive' }}">
                                <td><input type="checkbox" class="selectProduct row-check" value="{{ $product->id }}"></td>
                                <td style="color:var(--erp-muted); font-size:.72rem;">{{ $products->firstItem() + $key }}</td>
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('uploads/products/' . $product->image) }}"
                                            alt="{{ $product->item_name }}" class="product-img">
                                    @else
                                        <div class="no-img-badge"><i class="fas fa-image"></i></div>
                                    @endif
                                </td>
                                <td class="td-item-details">
                                    <div class="item-name">{{ $product->item_name }}</div>
                                    <div class="item-meta">
                                        <span class="item-code">{{ $product->item_code }}</span>
                                        @if($product->category_relation)
                                            <span class="meta-chip"><i class="fas fa-list" style="font-size:.6rem;"></i> {{ $product->category_relation->name }}</span>
                                        @endif
                                        @if($product->brand)
                                            <span class="meta-chip"><i class="fas fa-trademark" style="font-size:.6rem;"></i> {{ $product->brand->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="stock-badge {{ $stockClass }}">
                                        <i class="fas fa-cubes" style="font-size:.65rem;"></i>
                                        {{ $stockDisplay }}
                                        <span class="stock-unit">{{ $stockUnit }}</span>
                                    </span>
                                </td>
                                <td class="price-purchase">Rs. {{ number_format($tradePrice, 2) }}</td>
                                <td class="price-sale">Rs. {{ number_format($retailPrice, 2) }}</td>
                                <td>
                                    @if($product->is_active)
                                        <span class="status-active" id="status-badge-{{ $product->id }}">Active</span>
                                    @else
                                        <span class="status-inactive" id="status-badge-{{ $product->id }}">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button type="button" class="btn-act btn-act-view viewProductBtn"
                                            data-id="{{ $product->id }}" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                            <a href="{{ route('products.edit', $product->id) }}"
                                                class="btn-act btn-act-edit" title="Edit Product">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                        @endif
                                        <a href="{{ route('generate-barcode-image', $product->id) }}"
                                            class="btn-act btn-act-barcode" title="Generate Barcode">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                        @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                            <button type="button"
                                                class="btn-act {{ $product->is_active ? 'btn-act-deact' : 'btn-act-act' }} toggle-active-btn"
                                                data-id="{{ $product->id }}"
                                                data-active="{{ $product->is_active ? '1' : '0' }}"
                                                data-name="{{ $product->item_name }}"
                                                title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>{{-- /erp-table-wrap --}}

        {{-- ── Mobile Product Cards (Shown only on mobile < 768px for 100% user-friendly view) ── --}}
        <div class="mobile-product-cards">
            @foreach ($products as $product)
                @php
                    $stockPieces = (float) ($product->warehouse_stocks_sum_total_pieces ?? 0);
                    $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                    if (($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') && $ppb > 1) {
                        $boxes = floor($stockPieces / $ppb);
                        $loose = $stockPieces % $ppb;
                        $stockDisplay = $loose > 0 ? "{$boxes}.{$loose}" : "{$boxes}";
                        $stockUnit    = $loose > 0 ? 'Box.Loose' : 'Boxes';
                    } else {
                        $stockDisplay = $stockPieces;
                        $stockUnit    = 'Pcs';
                    }
                    $stockClass = $stockPieces == 0 ? 'zero' : (($product->alert_carton_quantity && $stockPieces <= $product->alert_carton_quantity) ? 'low' : '');

                    if ($product->size_mode === 'by_size') {
                        $m2 = ($product->height * $product->width) / 10000;
                        $tradePrice  = $m2 * (float)$product->purchase_price_per_m2;
                        $retailPrice = $m2 * (float)$product->price_per_m2;
                    } else {
                        $tradePrice  = (float)$product->purchase_price_per_piece;
                        $retailPrice = (float)$product->sale_price_per_piece ?: (float)$product->sale_price_per_box;
                    }
                @endphp
                <div class="prod-mcard {{ $product->is_active ? '' : 'row-inactive' }}" id="pmcard-{{ $product->id }}">
                    <div class="prod-mcard-hd">
                        <input type="checkbox" class="selectProduct row-check mt-1" value="{{ $product->id }}">
                        @if ($product->image)
                            <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->item_name }}" class="product-img">
                        @else
                            <div class="no-img-badge"><i class="fas fa-image"></i></div>
                        @endif
                        <div style="flex:1; min-width:0;">
                            <div class="d-flex align-items-center justify-content-between gap-1">
                                <div class="item-name mb-0 text-truncate">{{ $product->item_name }}</div>
                                @if($product->is_active)
                                    <span class="status-active" id="mstatus-badge-{{ $product->id }}">Active</span>
                                @else
                                    <span class="status-inactive" id="mstatus-badge-{{ $product->id }}">Inactive</span>
                                @endif
                            </div>
                            <div class="item-meta mt-1">
                                <span class="item-code">{{ $product->item_code }}</span>
                                @if($product->category_relation)
                                    <span class="meta-chip"><i class="fas fa-list"></i> {{ $product->category_relation->name }}</span>
                                @endif
                                @if($product->brand)
                                    <span class="meta-chip"><i class="fas fa-trademark"></i> {{ $product->brand->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="prod-mcard-body">
                        <div>
                            <div style="font-size:.68rem; font-weight:700; color:var(--erp-muted); text-transform:uppercase;">Sale Price</div>
                            <div class="prod-mcard-price">Rs. {{ number_format($retailPrice, 2) }}</div>
                            <div style="font-size:.7rem; color:var(--erp-muted);">Cost: Rs. {{ number_format($tradePrice, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:.68rem; font-weight:700; color:var(--erp-muted); text-transform:uppercase; margin-bottom:2px;">Stock</div>
                            <span class="stock-badge {{ $stockClass }}">
                                <i class="fas fa-cubes"></i> {{ $stockDisplay }} <span class="stock-unit">{{ $stockUnit }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="prod-mcard-actions">
                        <button type="button" class="btn-act btn-act-view viewProductBtn" data-id="{{ $product->id }}">
                            <i class="fas fa-eye"></i> View
                        </button>
                        @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                            <a href="{{ route('products.edit', $product->id) }}" class="btn-act btn-act-edit">
                                <i class="fas fa-pencil-alt"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('generate-barcode-image', $product->id) }}" class="btn-act btn-act-barcode">
                            <i class="fas fa-barcode"></i> Barcode
                        </a>
                        @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                            <button type="button"
                                class="btn-act {{ $product->is_active ? 'btn-act-deact' : 'btn-act-act' }} toggle-active-btn"
                                data-id="{{ $product->id }}"
                                data-active="{{ $product->is_active ? '1' : '0' }}"
                                data-name="{{ $product->item_name }}">
                                <i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>{{-- /mobile-product-cards --}}


        {{-- ── Pagination ── --}}
        <div class="erp-pagination">
            <span class="showing">
                Showing <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                of <strong>{{ $products->total() }}</strong> products
            </span>
            {{ $products->appends(request()->query())->links() }}
        </div>

    </div>{{-- /erp-card --}}
        </div>{{-- /container-fluid --}}
    </div>{{-- /main-content-inner --}}
</div>{{-- /main-content --}}


{{-- ══════════════════════════════════════════════════════════════
     IMPORT MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border-bottom: none;">
                <div>
                    <h5 class="modal-title fw-bold" id="importModalLabel">
                        <i class="fas fa-file-upload me-2"></i>Import Products from CSV
                    </h5>
                    <small style="color: rgba(255,255,255,0.85);">New products will be created. Existing ones (matched by Barcode or Item Code) will be updated.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:.8; text-shadow:none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background:#f8fafc;">
                <form action="{{ route('products.import.validate') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(session('error'))
                        <div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
                    @endif
                    <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:.85rem;">
                        <i class="fas fa-info-circle fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Zaroori Hidayat (Import Guidelines):</strong><br>
                            1. Pehle <a href="{{ route('products.template') }}" class="alert-link fw-bold text-decoration-underline">CSV Template Download</a> karein.<br>
                            2. <strong>Compulsory Fields:</strong> <code>Product Name</code>, <code>Variant Name</code>, <code>Variant Sale Price</code>, <code>Variant Purch Price</code> zaroori hain.<br>
                            3. <strong>Naye Product ke liye:</strong> <code>Item Code</code> khali (blank) chhod dein — system khud naya code assign karega. Ek product ke multiple variants ke liye Product Name same rakhein.<br>
                            4. <strong>Carton &amp; Pieces:</strong> Carton items ke liye <code>Size Mode = by_cartons</code> aur <code>Pieces Per Box</code> dein. Aap <code>Carton Stock</code> aur <code>Loose Pieces Stock</code> dono enter kar sakte hain.<br>
                            5. File save karte waqt <strong>CSV (Comma delimited)</strong> format select karein.
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Import Mode</label>
                        <select name="import_mode" class="form-select form-control" required>
                            <option value="create">Create (Add new products &amp; variants)</option>
                            <option value="update_only">Update Only (Update existing variants only)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoCreate" name="auto_create" value="1" checked>
                            <label class="form-check-label fw-bold ms-2" for="autoCreate">Auto-create missing Category &amp; Brand</label>
                        </div>
                        <small class="text-muted ms-4 d-block">If disabled, missing master data will throw validation errors.</small>
                    </div>
                    <div class="form-group mb-4">
                        <label class="fw-bold">Upload CSV File</label>
                        <input type="file" name="csv_file" class="form-control p-1" accept=".csv,.txt" required>
                        <small class="text-muted">Max 5 MB</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-arrow-right me-1"></i> Next: Validate &amp; Preview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PRODUCT VIEW MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="productViewModal" tabindex="-1" aria-labelledby="productViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-bottom bg-white px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="productViewModalLabel">
                        <span id="view_item_name">Product</span>
                    </h5>
                    <small class="text-muted" id="view_item_subtext">CODE</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="modalLoadingSpinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted small mt-2">Fetching product details…</p>
                </div>
                <div id="modalContentRow" class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size:.88rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th class="text-start ps-4" style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Variant Name</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Size</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Color</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Stock</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Sale Price</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Purch Price</th>
                                <th style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Alert</th>
                                <th class="text-end pe-4" style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#475569;">Barcode</th>
                            </tr>
                        </thead>
                        <tbody id="variantTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



@endsection

@section('js')
<script>
$(document).ready(function () {

    // ── Open Import Modal ──
    $('#openImportModalBtn').on('click', function () {
        $('#importModal').modal('show');
    });

    // ── Select All ──
    $('#selectAll').click(function() {
        $('.selectProduct').prop('checked', this.checked);
    });

    // ── DataTable init ── (responsive:false – we use CSS horizontal scroll instead)
    let table = $('#productTable').DataTable({
        responsive: false,
        paging:     false,
        ordering:   true,
        info:       false,
        order:      [[3, 'asc']],
        dom:        'rt',
        scrollX:    false,
        columnDefs: [{ targets: [0, 8], orderable: false, searchable: false }]
    });   // DataTable closes here

    // ── Select All ──
    $('#selectAll').click(function() {
        $('.selectProduct').prop('checked', this.checked);
    });

    // ── View Product Modal ──
    $(document).on('click', '.viewProductBtn', function() {
        let productId = $(this).data('id');
        $('#modalContentRow').addClass('d-none');
        $('#modalLoadingSpinner').removeClass('d-none');
        $('#productViewModal').modal('show');

        $.ajax({
            url:  "/productview/" + productId,
            type: "GET",
            success: function(product) {
                $('#modalLoadingSpinner').addClass('d-none');
                $('#modalContentRow').removeClass('d-none');

                $('#view_item_name').text(product.item_name ?? 'Unknown');
                $('#view_item_subtext').text(
                    (product.item_code ?? '') + ' | ' +
                    (product.category_relation?.name ?? '') + ' | ' +
                    (product.brand?.name ?? '')
                );

                let tbody = $('#variantTableBody');
                tbody.empty();

                let colorList = ['-'];
                let variants  = [];
                if (product.color) {
                    let parsed = product.color;
                    if (typeof parsed === 'string') {
                        try { parsed = JSON.parse(parsed); } catch (e) {}
                    }
                    if (typeof parsed === 'string') {
                        try { parsed = JSON.parse(parsed); } catch (e) {}
                    }
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        if (typeof parsed[0] === 'object' && parsed[0] !== null) {
                            variants = parsed;
                        } else {
                            colorList = parsed;
                        }
                    } else if (typeof parsed === 'object' && parsed !== null) {
                        variants = [parsed];
                    } else if (typeof parsed === 'string') {
                        colorList = [parsed];
                    }
                }

                let sizeStr = '-';
                if (product.size_mode === 'by_size')
                    sizeStr = (product.height || 0) + ' x ' + (product.width || 0) + ' cm';

                let stock     = product.calculated_total_stock_qty ?? 0;
                let alertDef  = product.alert_carton_quantity != null ? product.alert_carton_quantity + '' : '-';
                let salePrice = product.size_mode === 'by_size' ? product.price_per_m2 : (product.sale_price_per_piece || product.sale_price_per_box || 0);
                let purchPrice= product.size_mode === 'by_size' ? product.purchase_price_per_m2 : (product.purchase_price_per_piece || 0);
                let priceLabel= product.size_mode === 'by_size' ? '/m²' : '/pc';

                function stockBadgeHtml(qty, alert) {
                    let isLow = qty > 0 && alert != null && qty <= alert;
                    let cls   = qty == 0 ? 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;' : (isLow ? 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' : 'background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;');
                    return `<span style="${cls} border-radius:6px; padding:3px 8px; font-size:.78rem; font-weight:600;">${qty}</span>`;
                }

                if (variants.length > 0) {
                    variants.forEach(v => {
                        let vName     = v.name || v.variant_name || product.item_name;
                        let vSize     = v.size || v.variant_size || '-';
                        let vColorVal = v.color || v.variant_color || '-';
                        let vStock    = (v.stock !== undefined && v.stock !== null && v.stock !== '') ? v.stock : (v.variant_stock ?? 0);
                        let vSale     = (v.sale_price !== undefined && v.sale_price !== null && v.sale_price !== '') ? v.sale_price : (v.variant_sale_price ?? 0);
                        let vPurch    = (v.purch_price !== undefined && v.purch_price !== null && v.purch_price !== '') ? v.purch_price : (v.purchase_price ?? v.variant_purchase_price ?? 0);
                        let vAlert    = (v.alert !== undefined && v.alert !== null && v.alert !== '') ? v.alert : (v.variant_alert_qty ?? 0);
                        let vBarcode  = v.barcode || v.variant_barcode || (product.barcode_path ?? product.item_code);
                        let vUnit     = v.unit || v.variant_unit || (product.unit ? product.unit.name : 'Pcs');

                        let colorBadge = (vColorVal && vColorVal !== '-') ? `<span style="background:#e2e8f0;border-radius:4px;padding:2px 6px;font-size:.72rem;">${vColorVal}</span>` : '<span style="color:#94a3b8;">—</span>';
                        let alertQty  = (vAlert != null && vAlert != 0) ? vAlert : '-';
                        
                        if (product.size_mode === 'by_kg' && v.conv_factor != 1 && !v.unit) vUnit = 'Pcs';
                        let vPriceLabel = product.size_mode === 'by_size' ? '/m²' : '/' + vUnit;

                        tbody.append(`<tr>
                            <td class="text-start ps-4 fw-semibold">${vName}</td>
                            <td>${vSize}</td>
                            <td>${colorBadge}</td>
                            <td>${stockBadgeHtml(vStock, vAlert)}</td>
                            <td class="fw-bold" style="color:#059669;">Rs. ${parseFloat(vSale||0).toFixed(2)} <small class="fw-normal text-muted">${vPriceLabel}</small></td>
                            <td class="text-muted">Rs. ${parseFloat(vPurch||0).toFixed(2)} <small>${vPriceLabel}</small></td>
                            <td><span style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:4px;padding:2px 6px;font-size:.72rem;">${alertQty}</span></td>
                            <td class="text-end pe-4"><code style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:2px 6px;font-size:.75rem;">${vBarcode}</code></td>
                        </tr>`);
                    });
                } else {
                    colorList.forEach((color, index) => {
                        let barcode   = (product.barcode_path ?? product.item_code ?? '') + (index > 0 ? '-' + String(index+1).padStart(2,'0') : '');
                        let colorBadge = (color && color !== '-') ? `<span style="background:#e2e8f0;border-radius:4px;padding:2px 6px;font-size:.72rem;">${color}</span>` : '<span style="color:#94a3b8;">—</span>';
                        tbody.append(`<tr>
                            <td class="text-start ps-4 fw-semibold">${product.item_name}</td>
                            <td>${sizeStr}</td>
                            <td>${colorBadge}</td>
                            <td>${stockBadgeHtml(stock, product.alert_carton_quantity)}</td>
                            <td class="fw-bold" style="color:#059669;">Rs. ${parseFloat(salePrice||0).toFixed(2)} <small class="fw-normal text-muted">${priceLabel}</small></td>
                            <td class="text-muted">Rs. ${parseFloat(purchPrice||0).toFixed(2)} <small>${priceLabel}</small></td>
                            <td><span style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:4px;padding:2px 6px;font-size:.72rem;">${alertDef}</span></td>
                            <td class="text-end pe-4"><code style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:2px 6px;font-size:.75rem;">${barcode}</code></td>
                        </tr>`);
                    });
                }
            },
            error: function() {
                $('#modalLoadingSpinner').addClass('d-none');
                Swal.fire('Error', 'Could not fetch product details.', 'error');
            }
        });
    });

    // ── Toggle Active ──
    $(document).on('click', '.toggle-active-btn', function () {
        const btn        = $(this);
        const productId  = btn.data('id');
        const isActive   = btn.data('active') == '1';
        const name       = btn.data('name');
        const actionText = isActive ? 'Deactivate' : 'Activate';

        Swal.fire({
            title: actionText + ' Product?',
            html:  `<b>${name}</b><br><small class="text-muted">${isActive ? 'Product will be hidden from Sale/Purchase forms.' : 'Product will be visible in Sale/Purchase forms.'}</small>`,
            icon:  isActive ? 'warning' : 'success',
            showCancelButton:    true,
            confirmButtonText:   'Yes, ' + actionText,
            confirmButtonColor:  isActive ? '#dc2626' : '#059669',
            cancelButtonText:    'Cancel',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url:  `/product/${productId}/toggle-active`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (!res.success) return;
                    const row    = $(`#product-row-${productId}`);
                    const card   = $(`#pmcard-${productId}`);
                    const badge  = $(`#status-badge-${productId}`);
                    const mbadge = $(`#mstatus-badge-${productId}`);
                    if (res.is_active) {
                        row.removeClass('row-inactive');
                        card.removeClass('row-inactive');
                        badge.attr('class', 'status-active').text('Active');
                        mbadge.attr('class', 'status-active').text('Active');
                        btn.removeClass('btn-act-act').addClass('btn-act-deact')
                           .attr('title','Deactivate').html('<i class="fas fa-ban"></i>')
                           .data('active','1');
                    } else {
                        row.addClass('row-inactive');
                        card.addClass('row-inactive');
                        badge.attr('class', 'status-inactive').text('Inactive');
                        mbadge.attr('class', 'status-inactive').text('Inactive');
                        btn.removeClass('btn-act-deact').addClass('btn-act-act')
                           .attr('title','Activate').html('<i class="fas fa-check"></i>')
                           .data('active','0');
                    }
                    Swal.fire({ toast:true, position:'top-end', icon:'success', title:res.message, showConfirmButton:false, timer:2500, timerProgressBar:true });
                },
                error: () => Swal.fire('Error', 'Could not update product status.', 'error')
            });
        });
    });

    // ── Subcategory fetch helpers ──
    $('#categorySelect').change(function() {
        var id = $(this).val();
        $('#subCategorySelect').html('<option value="">Loading...</option>');
        if (id) {
            $.get("/get-subcategories/" + id, { category_id: id }, function(data) {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
                $.each(data, function(k, sub) {
                    $('#subCategorySelect').append('<option value="' + sub.id + '">' + sub.name + '</option>');
                });
            }).fail(() => alert('Error fetching subcategories.'));
        } else {
            $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
        }
    });

});  // ── end $(document).ready ──
</script>
@endsection
