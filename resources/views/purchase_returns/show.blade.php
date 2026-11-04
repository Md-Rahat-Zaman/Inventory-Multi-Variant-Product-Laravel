@extends('layouts.app_bootstrap')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Purchase Return Details</h4>

        <div class="d-flex gap-2">
            @if (auth()->user()->hasPermission('purchase_returns', 'edit'))
            <a href="{{ route('purchase-returns.edit', $purchaseReturn) }}"
               class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endif

            <a href="{{ route('purchase-returns.index') }}"
               class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Return No</div>
                    <strong>{{ $purchaseReturn->return_no }}</strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Purchase No</div>
                    <strong>
                        {{ $purchaseReturn->purchase->purchase_no ?? $purchaseReturn->purchase_id }}
                    </strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Supplier</div>
                    <strong>{{ $purchaseReturn->supplier->name ?? 'N/A' }}</strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Return Date</div>
                    <strong>{{ $purchaseReturn->return_date->format('d M Y') }}</strong>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Note</div>
                    {{ $purchaseReturn->note ?: 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Purchase Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchaseReturn->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $item->product->name ?? 'Product #' . $item->product_id }}
                            </td>

                            <td>#{{ $item->product_variant_id }}</td>

                            <td class="text-end">
                                {{ number_format((float) $item->quantity, 3) }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $item->unit_price, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $item->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Grand Total</th>
                        <th class="text-end">
                            {{ number_format((float) $purchaseReturn->total_amount, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection