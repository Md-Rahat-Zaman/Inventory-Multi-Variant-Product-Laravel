@extends('layouts.app_bootstrap')

@section('title', 'Sale Return Details')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Sale Return Details</h4>
            <p class="text-muted mb-0">{{ $saleReturn->return_no }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sale-returns.index') }}" class="btn btn-outline-secondary">Back</a>
            @if (auth()->user()->hasPermission('sale_returns', 'edit'))
            <a href="{{ route('sale-returns.edit', $saleReturn) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><div class="text-muted small">Return No.</div><strong>{{ $saleReturn->return_no }}</strong></div>
                <div class="col-md-3"><div class="text-muted small">Sale Invoice</div><strong>{{ $saleReturn->sale->sale_no ?? ('Sale #' . $saleReturn->sale_id) }}</strong></div>
                <div class="col-md-3"><div class="text-muted small">Customer</div><strong>{{ $saleReturn->customer->name ?? $saleReturn->sale->customer_name ?? 'Walk-in Customer' }}</strong></div>
                <div class="col-md-3"><div class="text-muted small">Return Date</div><strong>{{ $saleReturn->return_date?->format('d M Y') }}</strong></div>
            </div>
            @if ($saleReturn->note)
                <div class="mt-3"><div class="text-muted small">Note</div>{{ $saleReturn->note }}</div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product</th>
                        <th>Variant ID</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end pe-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($saleReturn->items as $item)
                        <tr>
                            <td class="ps-3">{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                            <td>{{ $item->product_variant_id }}</td>
                            <td class="text-end">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-end pe-3">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">Return Total</th>
                        <th class="text-end pe-3">{{ number_format((float) $saleReturn->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
