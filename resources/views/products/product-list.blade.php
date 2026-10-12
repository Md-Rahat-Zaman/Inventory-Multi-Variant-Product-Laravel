@extends('layouts.app_bootstrap')

@section('title', 'Products')

@section('content')
<style>
/* Product information */
.product-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 190px;
}

.product-info-icon {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #edf5f1;
    color: #006044;
    font-size: 20px;
}

.product-category {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
}

.variant-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 7px;
    background: #edf5f1;
    color: #006044;
    font-size: 13px;
    font-weight: 600;
}

.product-price {
    font-weight: 600;
    white-space: nowrap;
    color: #1e293b;
}

.product-table > tbody > tr > td {
    vertical-align: middle;
}

.variant-details-box {
    margin: 0;
    padding: 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}

.variant-details-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.variant-details-heading h6 {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.variant-inner-table {
    min-width: 720px;
    background: #fff;
    border: 1px solid #e2e8f0;
}

.variant-inner-table thead th {
    padding: 12px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    border-bottom: 1px solid #e2e8f0;
}

.variant-inner-table tbody td {
    padding: 12px;
    color: #334155;
    font-size: 13px;
    vertical-align: middle;
}

.variant-color-name {
    font-weight: 600;
}

.variant-size {
    display: inline-block;
    padding: 3px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    background: #fff;
    font-size: 12px;
    font-weight: 600;
}

.variant-inner-table tbody tr:last-child td {
    border-bottom: 0;
}

@media (max-width: 768px) {
    .variant-details-box {
        padding: 12px;
    }

    .product-info {
        min-width: 170px;
    }
}
</style>
<div class="module-page">

    <div class="container">

        {{-- =====================================================
             Page Header
        ====================================================== --}}
        <div class="module-header">

            <div class="module-header-row">

                <div>

                    <h3 class="module-title">
                        Products
                    </h3>

                    <p class="module-subtitle">
                        Manage your products
                    </p>

                </div>


                <div class="module-header-actions">

                    <a href="{{ route('products.create') }}"
                       class="btn btn-primary module-btn">

                        <i class="bi bi-plus-lg"></i>

                        Add Product

                    </a>

                </div>

            </div>

        </div>


        {{-- =====================================================
             Summary
        ====================================================== --}}
        <div class="module-summary">

            <div class="module-summary-box">

                <div class="module-summary-icon">

                    <i class="bi bi-box-seam"></i>

                </div>


                <div>

                    <span class="module-summary-label">
                        Total Products
                    </span>

                    <span class="module-summary-value">
                        {{ $products->total() }}
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
             Main Card
        ====================================================== --}}
        <div class="module-card">


            {{-- =================================================
                 Card Header
            ================================================== --}}
            <div class="module-card-header">

                <h5 class="module-card-title">
                    Product List
                </h5>

                <span class="module-card-count">
                    {{ $products->total() }} Products
                </span>

            </div>


            {{-- =================================================
                 Basic Toolbar
            ================================================== --}}
            <div class="module-toolbar">


                {{-- Search --}}
                <div class="module-search">

                    <i class="bi bi-search module-search-icon"></i>

                    <input
                        type="text"
                        class="module-search-input"
                        placeholder="Search products..."
                    >

                </div>


                {{-- Basic Filters --}}
                <div class="module-filters">


                    {{-- Status --}}
                    <select class="module-filter">

                        <option value="">
                            All Status
                        </option>

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>

                    </select>


                    {{-- Sort --}}
                    <select class="module-filter">

                        <option value="">
                            Sort By
                        </option>

                        <option value="latest">
                            Latest
                        </option>

                        <option value="oldest">
                            Oldest
                        </option>

                        <option value="name">
                            Name
                        </option>

                    </select>


                    {{-- Reset --}}
                    <button
                        type="button"
                        class="module-reset-btn"
                    >

                        <i class="bi bi-arrow-clockwise"></i>

                        Reset

                    </button>

                </div>

            </div>


            {{-- =================================================
                 Advanced Filter
            ================================================== --}}
            <div class="advanced-filter">


                {{-- =================================================
                     Advanced Filter Header
                ================================================== --}}
                <div class="advanced-filter-header">


                    <div class="advanced-filter-heading">

                        <div class="advanced-filter-icon">

                            <i class="bi bi-funnel"></i>

                        </div>


                        <div>

                            <h6 class="advanced-filter-title">
                                Advanced Filter
                            </h6>

                            <p class="advanced-filter-subtitle">
                                Filter products using multiple conditions
                            </p>

                        </div>

                    </div>


                    {{-- Toggle --}}
                    <button
                        type="button"
                        class="advanced-filter-toggle"
                    >

                        <i class="bi bi-sliders"></i>

                        <span>
                            Advanced Filter
                        </span>

                        <i class="bi bi-chevron-down advanced-filter-chevron"></i>

                    </button>

                </div>


                {{-- =================================================
                     Advanced Filter Body
                ================================================== --}}
                <div class="advanced-filter-body">


                    {{-- =================================================
                         Filter Rows Container
                    ================================================== --}}
                    <div class="advanced-filter-rows">


                        {{-- =================================================
                             Filter Row
                        ================================================== --}}
                        <div class="advanced-filter-row">


                            {{-- Column --}}
                            <div class="filter-field">

                                <label>
                                    Column
                                </label>

                                <select
                                    class="advanced-filter-control filter-column "
                                >

                                    <option value="">
                                        Select Column
                                    </option>

                                    <option value="name">
                                        Product Name
                                    </option>

                                    <option value="sku">
                                        SKU
                                    </option>

                                    <option value="sale_price">
                                        Price
                                    </option>

                                    <option value="stock">
                                        Stock
                                    </option>

                                    <option value="status">
                                        Status
                                    </option>

                                </select>

                            </div>


                            {{-- Condition --}}
                            <div class="filter-field">

                                <label>
                                    Condition
                                </label>

                                <select
                                    class="advanced-filter-control filter-condition"
                                >

                                    <option value="">
                                        Select Condition
                                    </option>

                                    <option value="=">
                                        Equals
                                    </option>

                                    <option value="!=">
                                        Not Equals
                                    </option>

                                    <option value="contains">
                                        Contains
                                    </option>

                                    <option value="starts_with">
                                        Starts With
                                    </option>

                                    <option value="ends_with">
                                        Ends With
                                    </option>

                                    <option value=">">
                                        Greater Than
                                    </option>

                                    <option value=">=">
                                        Greater Than or Equal
                                    </option>

                                    <option value="<">
                                        Less Than
                                    </option>

                                    <option value="<=">
                                        Less Than or Equal
                                    </option>

                                </select>

                            </div>


                            {{-- Value --}}
                            <div class="filter-field filter-value-field">

                                <label>
                                    Value
                                </label>

                                <input
                                    type="text"
                                    class="advanced-filter-control filter-value"
                                    placeholder="Enter value..."
                                >

                            </div>


                            {{-- Logic --}}
                            <div class="filter-field filter-logic-field">

                                <label>
                                    Logic
                                </label>

                                <select
                                    class="advanced-filter-control filter-logic"
                                >

                                    <option value="and">
                                        AND
                                    </option>

                                    <option value="or">
                                        OR
                                    </option>

                                </select>

                            </div>


                            {{-- Remove --}}
                            <div class="filter-remove">

                                <button
                                    type="button"
                                    class="filter-remove-btn"
                                    title="Remove filter"
                                >

                                    <i class="bi bi-trash"></i>

                                </button>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         Advanced Filter Footer
                    ================================================== --}}
                    <div class="advanced-filter-footer">


                        {{-- Add Filter --}}
                        <button
                            type="button"
                            class="add-filter-btn"
                        >

                            <i class="bi bi-plus-lg"></i>

                            <span>
                                Add Filter
                            </span>

                        </button>


                        {{-- Actions --}}
                        <div class="advanced-filter-actions">


                            {{-- Clear --}}
                            <button
                                type="button"
                                class="filter-clear-btn"
                            >

                                <i class="bi bi-arrow-counterclockwise"></i>

                                <span>
                                    Clear
                                </span>

                            </button>


                            {{-- Apply --}}
                            <button
                                type="button"
                                class="filter-apply-btn"
                            >

                                <i class="bi bi-funnel-fill"></i>

                                <span>
                                    Apply Filters
                                </span>

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 Product Table
            ====================================================== --}}
            
            {{-- Product Table --}}
            <div class="module-table-wrapper">
                <table class="module-table product-table">

                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Product Information</th>
                            <th>Category</th>
                            <th>Variants</th>
                            <th>Price Range</th>
                            <th>Total Stock</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $key => $product)

                            <tr>
                                <td class="module-serial">
                                    {{ $products->firstItem() + $key }}
                                </td>

                                <td>
                                    <div class="product-info">
                                        <div class="product-info-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>

                                        <div>
                                            <div class="module-name">
                                                {{ $product->name }}
                                            </div>

                                            <div class="module-secondary small">
                                                SKU: {{ $product->sku ?? '-' }}
                                            </div>

                                            <div class="module-secondary small">
                                                Unit: {{ $product->unit->name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="product-category">
                                        {{ $product->category->name ?? '-' }}
                                    </span>

                                    @if($product->brand)
                                        <div class="module-secondary small mt-1">
                                            {{ $product->brand->name }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="variant-count">
                                        <i class="bi bi-layers"></i>
                                        {{ $product->variants->count() }}
                                    </span>

                                    @if($product->variants->count())
                                        <div class="module-secondary small mt-1">
                                            Color / Size combinations
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if($product->variants->isNotEmpty())
                                        <div class="product-price">
                                            {{ number_format($product->variants->min('sale_price'), 2) }}
                                            @if($product->variants->min('sale_price') != $product->variants->max('sale_price'))
                                                <span class="module-secondary">–</span>
                                                {{ number_format($product->variants->max('sale_price'), 2) }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="product-price">
                                            {{ number_format($product->sale_price, 2) }}
                                        </div>
                                    @endif

                                    <div class="module-secondary small">Sale price</div>
                                </td>

                                <td>
                                    @php
                                        $totalStock = $product->variants->isNotEmpty()
                                            ? $product->variants->sum('stock')
                                            : $product->stock;
                                    @endphp

                                    @if($totalStock <= 0)
                                        <span class="module-stock out">
                                            <i class="bi bi-exclamation-circle"></i>
                                            Out of stock
                                        </span>
                                    @elseif($totalStock <= ($product->minimum_stock ?? 0))
                                        <span class="module-stock low">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                    @else
                                        <span class="module-stock">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($product->status)
                                        <span class="module-badge module-badge-success">
                                            <i class="bi bi-check-circle"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="module-badge module-badge-danger">
                                            <i class="bi bi-x-circle"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-light module-action-btn dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                        >
                                            <i class="bi bi-three-dots"></i>
                                            Action
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end module-action-menu">
                                           
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#variantsModal{{ $product->id }}"
                                                >
                                                    <i class="bi bi-layers"></i>
                                                    View Variants
                                                </button>
                                            </li>

                                            <li>
                                                <a
                                                    href="{{ route('products.show', $product->id) }}"
                                                    class="dropdown-item"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                    View Product
                                                </a>
                                            </li>

                                            <li>
                                                <a
                                                    href="{{ route('products.edit', $product->id) }}"
                                                    class="dropdown-item"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                    Edit
                                                </a>
                                            </li>

                                            <li><hr class="dropdown-divider"></li>

                                            <li>
                                                <form
                                                    action="{{ route('products.destroy', $product->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this product?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="dropdown-item delete-item">
                                                        <i class="bi bi-trash"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="module-empty">
                                        <div class="module-empty-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>

                                        <div class="module-empty-title">
                                            No Products Found
                                        </div>

                                        <p class="module-empty-text">
                                            Start by adding your first product.
                                        </p>

                                        <a href="{{ route('products.create') }}"
                                        class="btn btn-primary module-btn">
                                            <i class="bi bi-plus-lg"></i>
                                            Add Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
{{-- Variant Modals --}}
                            @foreach($products as $product)
                                <div
                                    class="modal fade"
                                    id="variantsModal{{ $product->id }}"
                                    tabindex="-1"
                                    aria-labelledby="variantsModalLabel{{ $product->id }}"
                                    aria-hidden="true"
                                >
                                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow">

                                            <div class="modal-header text-white" style="background: #006044;">
                                                <div>
                                                    <h5
                                                        class="modal-title fw-bold"
                                                        id="variantsModalLabel{{ $product->id }}"
                                                    >
                                                        <i class="bi bi-layers me-2"></i>
                                                        {{ $product->name }} — Variants
                                                    </h5>

                                                    <small>
                                                        {{ $product->variants->count() }} variants available
                                                    </small>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close"
                                                ></button>
                                            </div>

                                            <div class="modal-body p-3 p-md-4">

                                                <div class="row g-3 mb-4">

                                                    <div class="col-md-4">
                                                        <div class="border rounded-3 p-3 h-100">
                                                            <div class="text-muted small mb-1">Product</div>
                                                            <div class="fw-semibold">
                                                                {{ $product->name }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="border rounded-3 p-3 h-100">
                                                            <div class="text-muted small mb-1">Total Variants</div>
                                                            <div class="fw-bold fs-5" style="color: #006044;">
                                                                {{ $product->variants->count() }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        @php
                                                            $variantStock = $product->variants->sum('stock');
                                                        @endphp

                                                        <div class="border rounded-3 p-3 h-100">
                                                            <div class="text-muted small mb-1">Total Variant Stock</div>
                                                            <div class="fw-bold fs-5">
                                                                {{ number_format($variantStock, 2) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                @if($product->variants->isNotEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>#</th>
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
                                                                @foreach($product->variants as $variantKey => $variant)
                                                                    <tr>
                                                                        <td>{{ $variantKey + 1 }}</td>

                                                                        <td>
                                                                            <span class="fw-semibold">
                                                                                {{ $variant->color->name ?? '-' }}
                                                                            </span>
                                                                        </td>

                                                                        <td>
                                                                            <span class="badge bg-light text-dark border">
                                                                                {{ $variant->size->name ?? '-' }}
                                                                            </span>
                                                                        </td>

                                                                        <td>
                                                                            <span class="text-muted">
                                                                                {{ $variant->sku ?? '-' }}
                                                                            </span>
                                                                        </td>

                                                                        <td>
                                                                            {{ $variant->barcode ?? '-' }}
                                                                        </td>

                                                                        <td class="text-end">
                                                                            {{ number_format($variant->purchase_price, 2) }}
                                                                        </td>

                                                                        <td class="text-end fw-semibold">
                                                                            {{ number_format($variant->sale_price, 2) }}
                                                                        </td>

                                                                        <td class="text-end">
                                                                            @if($variant->stock <= 0)
                                                                                <span class="badge bg-danger">
                                                                                    Out of stock
                                                                                </span>
                                                                            @else
                                                                                <span class="badge bg-success">
                                                                                    {{ number_format($variant->stock, 2) }}
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="text-center py-5">
                                                        <i class="bi bi-layers fs-1 text-muted"></i>
                                                        <h6 class="mt-3">No Variants Found</h6>
                                                        <p class="text-muted mb-0">
                                                            This product has no variants yet.
                                                        </p>
                                                    </div>
                                                @endif

                                            </div>

                                            <div class="modal-footer bg-light">
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal"
                                                >
                                                    <i class="bi bi-x-lg me-1"></i>
                                                    Close
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach



            {{-- =====================================================
                 Pagination
            ====================================================== --}}
            @if($products->hasPages())

                <div class="module-pagination">


                    {{-- Pagination Info --}}
                    <div class="module-pagination-info">

                        Showing

                        {{ $products->firstItem() ?? 0 }}

                        to

                        {{ $products->lastItem() ?? 0 }}

                        of

                        {{ $products->total() }}

                    </div>


                    {{-- Pagination --}}
                    <div>

                        {{ $products->links() }}

                    </div>

                </div>

            @endif


        </div>

    </div>

</div>

@endsection