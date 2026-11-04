
@extends('layouts.app_bootstrap')

@section('title', 'Product Details')

@section('content')

<style>
    .product-show-page {
        color: #253047;
    }

    .product-show-page .page-heading {
        font-weight: 700;
        letter-spacing: -0.4px;
    }

    .product-show-page .info-card {
        border: 1px solid #e9edf3;
        border-radius: 14px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
        height: 100%;
    }

    .product-show-page .card-heading {
        font-size: 15px;
        font-weight: 700;
        padding-bottom: 14px;
        margin-bottom: 16px;
        border-bottom: 1px solid #edf0f5;
    }

    .product-show-page .info-label {
        color: #778196;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .product-show-page .info-value {
        color: #253047;
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .product-show-page .summary-card {
        border: 1px solid #e9edf3;
        border-radius: 12px;
        background: #fff;
        padding: 18px;
        height: 100%;
    }

    .product-show-page .summary-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eaf7f0;
        color: #16804a;
        font-size: 19px;
    }

    .product-show-page .summary-label {
        color: #778196;
        font-size: 12px;
        margin-top: 13px;
        margin-bottom: 4px;
    }

    .product-show-page .summary-value {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .product-show-page .table thead th {
        background: #f6f8fb;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        padding: 13px 14px;
        border-bottom: 1px solid #e9edf3;
    }

    .product-show-page .table tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        font-size: 13px;
    }

    .product-show-page .variant-badge {
        display: inline-block;
        background: #f0f3f8;
        color: #344054;
        border-radius: 6px;
        padding: 5px 9px;
        font-size: 12px;
        font-weight: 600;
    }

    .product-show-page .section-card {
        border: 1px solid #e9edf3;
        border-radius: 14px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .product-show-page .section-card-header {
        background: #fff;
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f5;
    }

    .product-show-page .section-card-body {
        padding: 20px;
    }

    .product-show-page .description-box {
        background: #f8fafc;
        border: 1px solid #edf0f5;
        border-radius: 10px;
        padding: 14px;
        white-space: pre-line;
        overflow-wrap: anywhere;
        font-size: 14px;
    }
</style>

<div class="container-fluid product-show-page py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="text-muted small mb-1">
                Inventory / Products / Details
            </div>

            <h4 class="page-heading mb-1">
                <i class="bi bi-box-seam me-2 text-success"></i>
                Product Details
            </h4>

            <div class="text-muted small">
                View product information, pricing and variants.
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}"
               class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Products
            </a>

            @if (auth()->user()->hasPermission('products', 'edit'))
            <a href="{{ route('products.edit', $product->id) }}"
               class="btn btn-success">
                <i class="bi bi-pencil-square me-1"></i>
                Edit Product
            </a>
            @endif
        </div>

    </div>

    {{-- Product Name and Status --}}
    <div class="section-card mb-4">
        <div class="section-card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

                <div>
                    <h5 class="fw-bold mb-2">
                        {{ $product->name }}
                    </h5>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted small">
                            SKU:
                            <strong class="text-dark">
                                {{ $product->sku ?: 'N/A' }}
                            </strong>
                        </span>

                        <span class="text-muted">|</span>

                        <span class="text-muted small">
                            Barcode:
                            <strong class="text-dark">
                                {{ $product->barcode ?: 'N/A' }}
                            </strong>
                        </span>
                    </div>
                </div>

                @if($product->status)
                    <span class="badge rounded-pill text-bg-success px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Active
                    </span>
                @else
                    <span class="badge rounded-pill text-bg-secondary px-3 py-2">
                        <i class="bi bi-pause-circle me-1"></i>
                        Inactive
                    </span>
                @endif

            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>

                <div class="summary-label">Purchase Price</div>

                <div class="summary-value">
                    {{ number_format((float) $product->purchase_price, 2) }}
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-tag"></i>
                </div>

                <div class="summary-label">Sale Price</div>

                <div class="summary-value">
                    {{ number_format((float) $product->sale_price, 2) }}
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-boxes"></i>
                </div>

                <div class="summary-label">Current Total Stock</div>

                <div class="summary-value">
                    {{ number_format((float) $product->stock, 2) }}
                </div>

                <div class="text-muted small mt-1">
                    {{ $product->unit->name ?? 'Unit not set' }}
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="bi bi-grid"></i>
                </div>

                <div class="summary-label">Total Variants</div>

                <div class="summary-value">
                    {{ $product->variants->count() }}
                </div>

                <div class="text-muted small mt-1">
                    Color and size combinations
                </div>
            </div>
        </div>

    </div>

    {{-- Basic Information and Pricing --}}
    <div class="row g-4 mb-4">

        {{-- Basic Information --}}
        <div class="col-12 col-xl-6">
            <div class="card info-card">
                <div class="card-body p-4">

                    <h6 class="card-heading">
                        <i class="bi bi-info-circle me-2 text-success"></i>
                        Basic Information
                    </h6>

                    <div class="row g-4">

                        <div class="col-6">
                            <div class="info-label">Product Name</div>
                            <div class="info-value">
                                {{ $product->name }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Category</div>
                            <div class="info-value">
                                {{ $product->category->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Brand</div>
                            <div class="info-value">
                                {{ $product->brand->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Unit</div>
                            <div class="info-value">
                                {{ $product->unit->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Product SKU</div>
                            <div class="info-value">
                                {{ $product->sku ?: 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Product Barcode</div>
                            <div class="info-value">
                                {{ $product->barcode ?: 'N/A' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        {{-- Pricing and Inventory --}}
        <div class="col-12 col-xl-6">
            <div class="card info-card">
                <div class="card-body p-4">

                    <h6 class="card-heading">
                        <i class="bi bi-cash-stack me-2 text-success"></i>
                        Pricing & Inventory
                    </h6>

                    <div class="row g-4">

                        <div class="col-6">
                            <div class="info-label">Purchase Price</div>
                            <div class="info-value">
                                {{ number_format((float) $product->purchase_price, 2) }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Sale Price</div>
                            <div class="info-value">
                                {{ number_format((float) $product->sale_price, 2) }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Tax Rate</div>
                            <div class="info-value">
                                {{ number_format((float) ($product->tax_rate ?? 0), 2) }}%
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Minimum Stock</div>
                            <div class="info-value">
                                {{ number_format((float) ($product->minimum_stock ?? 0), 2) }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Opening Stock</div>
                            <div class="info-value">
                                {{ number_format((float) ($product->opening_stock ?? 0), 2) }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="info-label">Current Stock</div>
                            <div class="info-value">
                                {{ number_format((float) $product->stock, 2) }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- Product Variants --}}
    <div class="section-card mb-4">

        <div class="section-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div>
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-grid-3x3-gap me-2 text-success"></i>
                    Product Variants
                </h6>

                <div class="text-muted small">
                    Stock and pricing for each color and size combination.
                </div>
            </div>

            <span class="badge text-bg-light border px-3 py-2">
                {{ $product->variants->count() }} Variants
            </span>

        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th style="width: 60px;">SL</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>SKU</th>
                        <th>Barcode</th>
                        <th class="text-end">Purchase Price</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-end">Stock</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($product->variants as $variant)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="variant-badge">
                                    {{ $variant->color->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span class="variant-badge">
                                    {{ $variant->size->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                {{ $variant->sku ?: 'N/A' }}
                            </td>

                            <td>
                                {{ $variant->barcode ?: 'N/A' }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $variant->purchase_price, 2) }}
                            </td>

                            <td class="text-end fw-semibold">
                                {{ number_format((float) $variant->sale_price, 2) }}
                            </td>

                            <td class="text-end">
                                @if((float) $variant->stock <= 0)
                                    <span class="badge text-bg-danger">
                                        {{ number_format((float) $variant->stock, 2) }}
                                    </span>
                                @else
                                    <span class="badge text-bg-success">
                                        {{ number_format((float) $variant->stock, 2) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 d-block text-muted mb-2"></i>
                                <div class="fw-semibold">No variants found</div>
                                <div class="text-muted small">
                                    This product does not have any variants yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($product->variants->isNotEmpty())
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="7" class="text-end">
                                Total Variant Stock
                            </th>

                            <th class="text-end">
                                {{ number_format((float) $product->variants->sum('stock'), 2) }}
                            </th>
                        </tr>
                    </tfoot>
                @endif

            </table>
        </div>
    </div>

    {{-- Notes and Description --}}
    @if($product->note || $product->description)
        <div class="row g-4 mb-4">

            @if($product->note)
                <div class="col-12 col-lg-6">
                    <div class="card info-card">
                        <div class="card-body p-4">

                            <h6 class="card-heading">
                                <i class="bi bi-sticky me-2 text-success"></i>
                                Note
                            </h6>

                            <div class="description-box">{{ $product->note }}</div>

                        </div>
                    </div>
                </div>
            @endif

            @if($product->description)
                <div class="col-12 col-lg-6">
                    <div class="card info-card">
                        <div class="card-body p-4">

                            <h6 class="card-heading">
                                <i class="bi bi-card-text me-2 text-success"></i>
                                Description
                            </h6>

                            <div class="description-box">{{ $product->description }}</div>

                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif

    {{-- Bottom Actions --}}
    <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">

        <a href="{{ route('products.index') }}"
           class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Products
        </a>

        @if (auth()->user()->hasPermission('products', 'edit'))
        <a href="{{ route('products.edit', $product->id) }}"
           class="btn btn-success">
            <i class="bi bi-pencil-square me-1"></i>
            Edit Product
        </a>
        @endif

    </div>

</div>

@endsection
