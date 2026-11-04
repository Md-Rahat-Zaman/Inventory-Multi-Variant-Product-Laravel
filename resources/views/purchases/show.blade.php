@extends('layouts.app_bootstrap')

@section('title', 'Purchase Details')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Purchase Details</h4>
            <div class="text-muted small">
                Invoice: {{ $purchase->purchase_no }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            <button type="button" class="btn btn-outline-secondary"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="text-muted small mb-1">Purchase Number</div>
                    <div class="fw-bold">{{ $purchase->purchase_no }}</div>

                    <div class="text-muted small mt-3 mb-1">Purchase Date</div>
                    <div>{{ $purchase->purchase_date->format('d M Y') }}</div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="text-muted small mb-1">Supplier</div>
                    <div class="fw-bold">{{ $purchase->supplier->name ?? 'N/A' }}</div>

                    @if($purchase->supplier && $purchase->supplier->phone)
                        <div class="text-muted small mt-1">
                            {{ $purchase->supplier->phone }}
                        </div>
                    @endif

                    <div class="mt-3">
                        @if($purchase->payment_status === 'paid')
                            <span class="badge text-bg-success">Paid</span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge text-bg-warning">Partially Paid</span>
                        @else
                            <span class="badge text-bg-danger">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SL</th>
                        <th>Product</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                            <td>{{ $item->variant->color->name ?? 'N/A' }}</td>
                            <td>{{ $item->variant->size->name ?? 'N/A' }}</td>

                            <td class="text-end">
                                {{ number_format((float) $item->quantity, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $item->purchase_price, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $item->discount, 2) }}
                            </td>

                            <td class="text-end fw-semibold">
                                {{ number_format((float) $item->total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Subtotal</span>
                        <span>{{ number_format((float) $purchase->subtotal, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Discount</span>
                        <span>{{ number_format((float) $purchase->discount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-top">
                        <strong>Grand Total</strong>
                        <strong>{{ number_format((float) $purchase->grand_total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Paid Amount</span>
                        <span class="text-success">
                            {{ number_format((float) $purchase->paid_amount, 2) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-top">
                        <strong>Due Amount</strong>
                        <strong class="{{ (float) $purchase->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format((float) $purchase->due_amount, 2) }}
                        </strong>
                    </div>

                </div>
            </div>

            @if($purchase->note)
                <div class="border-top mt-3 pt-3">
                    <div class="text-muted small mb-1">Note</div>
                    <div>{{ $purchase->note }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        nav, .sidebar, .btn, .alert {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection