@extends('layouts.app_bootstrap')

@section('title', 'Stock History')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Stock History</h1>
                <p class="text-muted mb-0">Purchases, sales, returns, and manual stock adjustments.</p>
            </div>
            @if (auth()->user()->hasPermission('inventory', 'create'))
                <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary">
                    <i class="bi bi-sliders me-1"></i>Adjust stock
                </a>
            @endif
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('inventory.history') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search history</label>
                        <input id="search" name="search" type="search" class="form-control"
                               value="{{ $filters['search'] ?? '' }}" placeholder="Product, reason, reference...">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Movement type</label>
                        <select id="type" name="type" class="form-select">
                            <option value="">All types</option>
                            @foreach (['Sale', 'Purchase', 'Sale return', 'Purchase return', 'Adjustment'] as $type)
                                <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="from" class="form-label">From date</label>
                        <input id="from" name="from" type="date" class="form-control" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="to" class="form-label">To date</label>
                        <input id="to" name="to" type="date" class="form-control" value="{{ $filters['to'] ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="{{ route('inventory.history') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th>Date</th><th>Product</th><th>Type</th><th>Reason</th>
                        <th class="text-end">Before</th><th class="text-end">Change</th>
                        <th class="text-end">After</th><th>Adjusted by</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($adjustments as $movement)
                            <tr>
                                <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($movement->created_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="fw-medium">{{ $movement->item_name }}</div>
                                    @php
                                        $variantName = $movement->variant_name
                                            ?: collect([$movement->color_name, $movement->size_name])->filter()->implode(' / ');
                                    @endphp
                                    @if ($variantName)<div class="small text-muted">{{ $variantName }}</div>@endif
                                </td>
                                <td>
                                    {{ $movement->movement_type }}
                                    @if ($movement->reference)<div class="small text-muted">{{ $movement->reference }}</div>@endif
                                </td>
                                <td>{{ $movement->reason }}</td>
                                <td class="text-end">{{ $movement->stock_before !== null ? number_format((float) $movement->stock_before, 2) : '—' }}</td>
                                <td class="text-end {{ $movement->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format((float) $movement->quantity_change, 2) }}
                                </td>
                                <td class="text-end">{{ $movement->stock_after !== null ? number_format((float) $movement->stock_after, 2) : '—' }}</td>
                                <td>{{ $movement->user_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No stock movements found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($adjustments->hasPages())<div class="card-footer bg-white">{{ $adjustments->links() }}</div>@endif
        </div>
    </div>
@endsection
