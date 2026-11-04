@extends('layouts.app_bootstrap')

@section('title', 'Stock Overview')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Stock Overview</h1>
                <p class="text-muted mb-0">Current stock across products and variants.</p>
            </div>
            @if (auth()->user()->hasPermission('inventory', 'create'))
                <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary">
                    <i class="bi bi-sliders me-1"></i>Adjust stock
                </a>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="text-muted small">Inventory items</div>
                    <div class="fs-3 fw-semibold">{{ number_format($itemCount) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="text-muted small">Total units in stock</div>
                    <div class="fs-3 fw-semibold">{{ number_format($totalStock, 2) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('inventory.low-stock') }}" class="card border-0 shadow-sm h-100 text-decoration-none text-reset"><div class="card-body">
                    <div class="text-muted small">At or below minimum</div>
                    <div class="fs-3 fw-semibold text-warning">{{ number_format($lowStockCount) }}</div>
                </div></a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('inventory.low-stock') }}" class="card border-0 shadow-sm h-100 text-decoration-none text-reset"><div class="card-body">
                    <div class="text-muted small">Out of stock</div>
                    <div class="fs-3 fw-semibold text-danger">{{ number_format($outOfStockCount) }}</div>
                </div></a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('inventory.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-5 col-lg-4">
                        <label for="search" class="form-label">Search inventory</label>
                        <input id="search" name="search" type="search" class="form-control"
                               value="{{ $filters['search'] ?? '' }}" placeholder="Product, SKU, category...">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Stock status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="in_stock" @selected(($filters['status'] ?? '') === 'in_stock')>In stock</option>
                            <option value="low_stock" @selected(($filters['status'] ?? '') === 'low_stock')>Low stock</option>
                            <option value="out_of_stock" @selected(($filters['status'] ?? '') === 'out_of_stock')>Out of stock</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h2 class="h6 mb-0">Current stock</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th>Product</th><th>SKU</th><th>Category</th><th>Unit</th>
                        <th class="text-end">Stock</th><th class="text-end">Minimum</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $item['name'] }}</div>
                                    @if ($item['variant'])<div class="small text-muted">{{ $item['variant'] }}</div>@endif
                                </td>
                                <td>{{ $item['sku'] ?: '—' }}</td>
                                <td>{{ $item['category'] ?: '—' }}</td>
                                <td>{{ $item['unit'] ?: '—' }}</td>
                                <td class="text-end">{{ number_format($item['stock'], 2) }}</td>
                                <td class="text-end">{{ number_format($item['minimum_stock'], 2) }}</td>
                                <td>
                                    @if ($item['stock'] <= 0)
                                        <span class="badge text-bg-danger">Out of stock</span>
                                    @elseif ($item['stock'] <= $item['minimum_stock'])
                                        <span class="badge text-bg-warning">Low stock</span>
                                    @else
                                        <span class="badge text-bg-success">In stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No inventory items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())<div class="card-footer bg-white">{{ $items->links() }}</div>@endif
        </div>
    </div>
@endsection
