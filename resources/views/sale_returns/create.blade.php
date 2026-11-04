@extends('layouts.app_bootstrap')

@section('title', 'New Sale Return')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">New Sale Return</h4>
            <p class="text-muted mb-0">Select the returned items and quantities.</p>
        </div>
        <a href="{{ route('sale-returns.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (!$isEdit)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <label for="saleSelector" class="form-label fw-semibold">Select Sale Invoice</label>
                <select id="saleSelector" class="form-select" onchange="if(this.value){window.location.href='{{ route('sale-returns.create') }}?sale_id='+this.value}else{window.location.href='{{ route('sale-returns.create') }}'}">
                    <option value="">Choose a completed sale...</option>
                    @foreach ($sales as $sale)
                        <option value="{{ $sale->id }}" @selected($selectedSale && $selectedSale->id === $sale->id)>
                            {{ $sale->sale_no }} — {{ $sale->customer_name ?? $sale->customer->name ?? 'Walk-in Customer' }} — {{ number_format((float) $sale->grand_total, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">Return No.</div><strong>{{ $saleReturn->return_no }}</strong></div>
                    <div class="col-md-4"><div class="text-muted small">Sale Invoice</div><strong>{{ $saleReturn->sale->sale_no ?? ('Sale #' . $saleReturn->sale_id) }}</strong></div>
                    <div class="col-md-4"><div class="text-muted small">Customer</div><strong>{{ $saleReturn->sale->customer_name ?? $saleReturn->sale->customer->name ?? 'Walk-in Customer' }}</strong></div>
                </div>
            </div>
        </div>
    @endif

    @if ($isEdit || $selectedSale)
        <form action="{{ $isEdit ? route('sale-returns.update', $saleReturn) : route('sale-returns.store') }}" method="POST">
            @csrf
            @if ($isEdit) @method('PUT') @else
                <input type="hidden" name="sale_id" value="{{ $selectedSale->id }}">
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="return_date" class="form-label fw-semibold">Return Date</label>
                            <input type="date" name="return_date" id="return_date" class="form-control"
                                   value="{{ old('return_date', $isEdit ? $saleReturn->return_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="note" class="form-label fw-semibold">Note</label>
                            <input type="text" name="note" id="note" class="form-control"
                                   value="{{ old('note', $isEdit ? $saleReturn->note : '') }}" placeholder="Optional reason or note">
                        </div>
                    </div>

                    <h5 class="fw-semibold mb-3">Sale Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Variant ID</th>
                                    <th>Sold Qty</th>
                                    <th>Max Return Qty</th>
                                    <th style="min-width:150px">Return Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($saleItems as $saleItem)
                                    @php
                                        $maxQty = (float) ($returnableQuantities[$saleItem->id] ?? 0);
                                        $currentQty = $isEdit ? (float) ($currentQuantities->get($saleItem->id)->quantity ?? 0) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $saleItem->product->name ?? 'Product #' . $saleItem->product_id }}</div>
                                            <div class="small text-muted">Sale item #{{ $saleItem->id }}</div>
                                        </td>
                                        <td>{{ $saleItem->product_variant_id }}</td>
                                        <td>{{ number_format((float) $saleItem->quantity, 3) }}</td>
                                        <td>{{ number_format($maxQty, 3) }}</td>
                                        <td>
                                            <input type="hidden" name="items[{{ $saleItem->id }}][sale_item_id]" value="{{ $saleItem->id }}">
                                            <input type="number"
                                                   name="items[{{ $saleItem->id }}][quantity]"
                                                   class="form-control return-quantity"
                                                   min="0" max="{{ $maxQty }}" step="0.001"
                                                   value="{{ old('items.' . $saleItem->id . '.quantity', $currentQty) }}"
                                                   {{ $maxQty <= 0 && $currentQty <= 0 ? 'disabled' : '' }}>
                                            <div class="form-text">0 = do not return</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No items found for this sale.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info small mb-0">
                        Return amount is calculated from the original sale item's net unit price. Returned quantities are added back to stock.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('sale-returns.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>{{ $isEdit ? 'Update Return' : 'Save Return' }}
                </button>
            </div>
        </form>
    @elseif (!$isEdit)
        <div class="alert alert-info">Select a sale invoice above to see its items and create a return.</div>
    @endif
</div>
@endsection
