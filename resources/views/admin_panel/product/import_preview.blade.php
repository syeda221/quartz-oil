@extends('admin_panel.layout.app')
@section('title', 'Import Preview')

@section('content')
<div class="content-wrapper text-sm">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="row align-items-center mb-1">
                <div class="col-sm-6">
                    <h5 class="m-0 text-dark fw-bold"><i class="fas fa-file-import me-2 text-primary"></i>Import Products (Preview)</h5>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('product') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
                    @if(count($payload['products']) > 0)
                    <form action="{{ route('products.import.confirm') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> Confirm &amp; Import</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon bg-success"><i class="fas fa-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Products to Create</span>
                            <span class="info-box-number fw-bold">{{ $payload['preview_stats']['products_create'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon bg-info"><i class="fas fa-edit"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Products to Update</span>
                            <span class="info-box-number fw-bold">{{ $payload['preview_stats']['products_update'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon bg-warning text-white"><i class="fas fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variants to Process</span>
                            <span class="info-box-number fw-bold">{{ $payload['preview_stats']['variants_create'] + $payload['preview_stats']['variants_update'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-database"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Master Data (Auto-create)</span>
                            <span class="info-box-number fw-bold">{{ $payload['preview_stats']['master_create'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($payload['errors']) && count($payload['errors']) > 0)
            <div class="alert alert-danger shadow-sm">
                <strong><i class="fas fa-times-circle me-1"></i> Validation Errors ({{ count($payload['errors']) }}):</strong>
                <ul class="mb-0 mt-2" style="max-height: 150px; overflow-y: auto;">
                    @foreach($payload['errors'] as $error)
                        <li>Row {{ $error['row'] }}: {{ $error['msg'] }}</li>
                    @endforeach
                </ul>
                <div class="mt-2 text-sm fw-bold">Rows with errors will be skipped if you proceed. You can fix them in your CSV and re-upload.</div>
            </div>
            @endif

            @if(count($payload['master_data']['categories']) > 0 || count($payload['master_data']['brands']) > 0)
            <div class="alert alert-warning shadow-sm">
                <strong><i class="fas fa-exclamation-triangle me-1"></i> Notice:</strong> The following master data will be auto-created:<br>
                @if(count($payload['master_data']['categories']) > 0)
                    <strong>Categories:</strong> {{ implode(', ', $payload['master_data']['categories']) }}<br>
                @endif
                @if(count($payload['master_data']['brands']) > 0)
                    <strong>Brands:</strong> {{ implode(', ', $payload['master_data']['brands']) }}<br>
                @endif
            </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold m-0"><i class="fas fa-list me-2 text-muted"></i>Import Data Preview</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-bordered mb-0" style="font-size:0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:160px;">Code / Status</th>
                                <th style="width:280px;">Product &amp; Details</th>
                                <th>Variants &amp; Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payload['products'] as $groupKey => $pData)
                                @php
                                    $isUpdate = !empty($pData['is_update']);
                                    $displayRef = $isUpdate ? ($pData['matched_code'] ?? $pData['raw_ref']) : 'Auto ITEM-XXXX';
                                    $mode = $pData['size_mode'] ?? 'by_pieces';
                                    $ppb = $pData['pcs_per_carton'] ?? 1;
                                @endphp
                                <tr>
                                    <td class="align-middle">
                                        <strong>{{ $displayRef }}</strong><br>
                                        @if($isUpdate)
                                            <span class="badge bg-info"><i class="fas fa-sync-alt me-1"></i> Update</span>
                                        @else
                                            <span class="badge bg-success"><i class="fas fa-plus me-1"></i> New Product</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <strong class="text-dark">{{ $pData['name'] }}</strong><br>
                                        <small class="text-muted">
                                            <strong>Mode:</strong> {{ $mode }} | 
                                            @if($mode === 'by_cartons')
                                                <strong>Pcs/Box:</strong> {{ $ppb }} |
                                            @endif
                                            <strong>Cat:</strong> {{ $pData['category'] ?: '-' }} | 
                                            <strong>Brand:</strong> {{ $pData['brand'] ?: '-' }}
                                        </small>
                                    </td>
                                    <td class="p-0 align-middle">
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead class="bg-light text-muted" style="font-size:0.75rem;">
                                                <tr>
                                                    <th>Variant Name</th>
                                                    <th>Size / Color</th>
                                                    <th>Unit</th>
                                                    <th>Stock</th>
                                                    <th>Sale Price</th>
                                                    <th>Purch Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pData['variants'] as $v)
                                                <tr>
                                                    <td class="fw-semibold">{{ $v['name'] }}</td>
                                                    <td>{{ $v['size'] }} / {{ $v['color'] }}</td>
                                                    <td><span class="badge bg-secondary">{{ $v['unit'] }}</span></td>
                                                    <td>
                                                        @if($mode === 'by_cartons')
                                                            <span class="badge bg-light text-dark border">
                                                                📦 {{ $v['carton_stock'] ?? floor($v['stock'] / max(1, $ppb)) }} Ctn 
                                                                + {{ $v['loose_stock'] ?? fmod($v['stock'], max(1, $ppb)) }} Pcs
                                                                ({{ $v['stock'] }} Total Pcs)
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-dark border">{{ $v['stock'] }} {{ $v['unit'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-success fw-bold">Rs. {{ number_format($v['sale_price'], 2) }}</td>
                                                    <td class="text-muted">Rs. {{ number_format($v['purch_price'], 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No valid data found to import.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
