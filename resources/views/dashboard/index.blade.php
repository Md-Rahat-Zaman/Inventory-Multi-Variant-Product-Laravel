@extends('layouts.app_bootstrap')

@section('title', 'Dashboard')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Inventory Management</div>
            <h2 class="page-title">Dashboard</h2>
            <div class="text-secondary mt-1">
                Live business overview for {{ now()->format('l, d F Y') }}
            </div>
        </div>
        <div class="col-auto ms-auto">
            @if(auth()->user()->hasPermission('products', 'create'))
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Product
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row row-cards mb-4">
    @if($canViewSales)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="avatar bg-green-lt me-3"><i class="bi bi-cart-check fs-3"></i></span>
                        <div class="flex-fill">
                            <div class="fw-semibold fs-3">Sales</div>
                            <div class="text-secondary small">Completed sales</div>
                        </div>
                        <a href="{{ route('sales.index') }}" class="text-success text-decoration-none small fw-medium">View all</a>
                    </div>
                    <div class="row">
                        <div class="col-6 border-end">
                            <div class="text-secondary small mb-1">Today</div>
                            <div class="h2 text-success mb-0">৳ {{ number_format((float) $salesToday, 2) }}</div>
                        </div>
                        <div class="col-6 ps-4">
                            <div class="text-secondary small mb-1">This month</div>
                            <div class="h2 mb-0">৳ {{ number_format((float) $salesThisMonth, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($canViewPurchases)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="avatar bg-blue-lt me-3"><i class="bi bi-cart-plus fs-3"></i></span>
                        <div class="flex-fill">
                            <div class="fw-semibold fs-3">Purchases</div>
                            <div class="text-secondary small">Supplier purchases</div>
                        </div>
                        <a href="{{ route('purchases.index') }}" class="text-primary text-decoration-none small fw-medium">View all</a>
                    </div>
                    <div class="row">
                        <div class="col-6 border-end">
                            <div class="text-secondary small mb-1">Today</div>
                            <div class="h2 text-primary mb-0">৳ {{ number_format((float) $purchasesToday, 2) }}</div>
                        </div>
                        <div class="col-6 ps-4">
                            <div class="text-secondary small mb-1">This month</div>
                            <div class="h2 mb-0">৳ {{ number_format((float) $purchasesThisMonth, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($canViewProducts)
        <div class="col-md-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="avatar bg-warning-lt me-3"><i class="bi bi-boxes fs-3"></i></span>
                        <div class="flex-fill">
                            <div class="fw-semibold fs-3">Inventory</div>
                            <div class="text-secondary small">{{ number_format($totalProducts) }} products · {{ number_format($totalStock, 2) }} units</div>
                        </div>
                        <a href="{{ route('products.index') }}" class="text-warning text-decoration-none small fw-medium">View all</a>
                    </div>
                    <div class="row">
                        <div class="col-6 border-end text-center">
                            <div class="text-secondary small mb-1">Low stock</div>
                            <div class="h2 text-warning mb-0">{{ number_format($lowStockCount) }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="text-secondary small mb-1">Out of stock</div>
                            <div class="h2 text-danger mb-0">{{ number_format($outOfStockCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if($canViewSales || $canViewPurchases || $canViewCustomers)
    <div class="row row-cards mb-4">
        @if($canViewSales)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="avatar bg-green-lt me-3"><i class="bi bi-cash-stack fs-3"></i></span>
                        <div>
                            <div class="text-secondary">Accounts receivable</div>
                            <div class="h2 mb-0">৳ {{ number_format((float) $receivables, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($canViewPurchases)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="avatar bg-orange-lt me-3"><i class="bi bi-credit-card fs-3"></i></span>
                        <div>
                            <div class="text-secondary">Accounts payable</div>
                            <div class="h2 mb-0">৳ {{ number_format((float) $payables, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($canViewCustomers)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="avatar bg-purple-lt me-3"><i class="bi bi-people fs-3"></i></span>
                        <div>
                            <div class="text-secondary">Customers</div>
                            <div class="h2 mb-0">{{ number_format($customerCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

<div class="row row-cards mb-4">
    @if($canViewSales)
        <div class="{{ $canViewProducts ? 'col-lg-8' : 'col-12' }}">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Sales · last 7 days</h3>
                    <a href="{{ route('sales.index') }}" class="card-actions text-decoration-none">View sales</a>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between gap-2" style="height: 190px">
                        @foreach($salesChart as $day)
                            <div class="d-flex flex-column align-items-center justify-content-end h-100 flex-fill">
                                <div class="text-secondary small mb-2 text-nowrap">৳{{ number_format($day['total'], 0) }}</div>
                                <div class="bg-primary rounded-top w-100" style="height: {{ $day['bar_height'] }}%; min-height: 4px" title="{{ $day['date'] }}: ৳{{ number_format($day['total'], 2) }}"></div>
                                <div class="text-secondary small mt-2">{{ $day['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($canViewProducts)
        <div class="{{ $canViewSales ? 'col-lg-4' : 'col-12' }}">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low stock</h3>
                    <a href="{{ route('products.index') }}" class="card-actions text-decoration-none">View products</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar bg-warning-lt me-3">{{ mb_substr($product['name'], 0, 1) }}</span>
                                <div class="flex-fill min-w-0">
                                    <div class="fw-medium text-truncate">{{ $product['name'] }}</div>
                                    <div class="text-secondary small text-truncate">
                                        {{ $product['variant'] ?? $product['category'] ?? 'Product' }}
                                    </div>
                                </div>
                                <span class="badge bg-warning-lt">{{ number_format($product['stock'], 2) }} left</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary py-4 text-center">
                            No low-stock products right now.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

@if($canViewSales)
    <div class="row row-cards mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent sales</h3>
                    <a href="{{ route('sales.index') }}" class="card-actions text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                @php
                                    $paymentBadge = match ($sale->payment_status) {
                                        'paid' => 'bg-success-lt',
                                        'partial' => 'bg-warning-lt',
                                        default => 'bg-danger-lt',
                                    };
                                @endphp
                                <tr>
                                    <td><a href="{{ route('sales.show', $sale) }}" class="fw-semibold">{{ $sale->sale_no }}</a></td>
                                    <td>{{ $sale->customer_name ?: 'Walk-in customer' }}</td>
                                    <td>{{ $sale->sale_date->format('d M Y') }}</td>
                                    <td><span class="badge {{ $paymentBadge }}">{{ ucfirst($sale->payment_status) }}</span></td>
                                    <td class="text-end">৳ {{ number_format((float) $sale->grand_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No sales recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

@php
    $quickActions = [
        ['permission' => ['products', 'create'], 'route' => 'products.create', 'label' => 'Add Product', 'icon' => 'bi-box-seam', 'style' => 'primary'],
        ['permission' => ['sales', 'create'], 'route' => 'sales.pos', 'label' => 'New Sale', 'icon' => 'bi-cart-check', 'style' => 'success'],
        ['permission' => ['purchases', 'create'], 'route' => 'purchases.create', 'label' => 'New Purchase', 'icon' => 'bi-cart-plus', 'style' => 'info'],
        ['permission' => ['customers', 'create'], 'route' => 'customers.create', 'label' => 'Add Customer', 'icon' => 'bi-person-plus', 'style' => 'warning'],
    ];
@endphp
@if(collect($quickActions)->contains(fn ($action) => auth()->user()->hasPermission(...$action['permission'])))
    <div class="row row-cards mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Quick actions</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($quickActions as $action)
                            @if(auth()->user()->hasPermission(...$action['permission']))
                                <div class="col-sm-6 col-lg-3">
                                    <a href="{{ route($action['route']) }}" class="btn btn-outline-{{ $action['style'] }} w-100 py-3">
                                        <i class="bi {{ $action['icon'] }} fs-2 d-block mb-1"></i>
                                        {{ $action['label'] }}
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
