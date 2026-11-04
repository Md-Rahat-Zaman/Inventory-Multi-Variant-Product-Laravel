
@extends('layouts.app_bootstrap')

@section('title', 'Sale Invoice')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1">Sale Invoice</h4>
            <div class="text-muted small">{{ $sale->sale_no }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            <button type="button" class="btn btn-outline-secondary"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success no-print">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm" id="saleInvoice">
        <div class="card-body p-4 p-md-5">

            <div class="row g-4 pb-4 border-bottom">
                <div class="col-12 col-md-6">
                    <h3 class="fw-bold mb-1">SALES INVOICE</h3>
                    <div class="text-muted">Invoice: {{ $sale->sale_no }}</div>
                </div>

                <div class="col-12 col-md-6 text-md-end">
                    <div class="text-muted small">Sale Date</div>
                    <div class="fw-semibold">{{ $sale->sale_date->format('d M Y') }}</div>

                    <div class="mt-2">
                        @if($sale->payment_status === 'paid')
                            <span class="badge text-bg-success">Paid</span>
                        @elseif($sale->payment_status === 'partial')
                            <span class="badge text-bg-warning">Partially Paid</span>
                        @else
                            <span class="badge text-bg-danger">Unpaid</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-4 py-4">
                <div class="col-12 col-md-6">
                    <div class="text-muted small mb-1">Customer</div>

                    <div class="fw-bold">
                        {{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in Customer') }}
                    </div>

                    @if($sale->customer_phone)
                        <div class="text-muted small mt-1">{{ $sale->customer_phone }}</div>
                    @endif
                </div>

                <div class="col-12 col-md-6 text-md-end">
                    <div class="text-muted small mb-1">Invoice Number</div>
                    <div class="fw-semibold">{{ $sale->sale_no }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->variant->color->name ?? 'N/A' }}</td>
                                <td>{{ $item->variant->size->name ?? 'N/A' }}</td>

                                <td class="text-end">
                                    {{ number_format((float) $item->quantity, 2) }}
                                </td>

                                <td class="text-end">
                                    {{ number_format((float) $item->unit_price, 2) }}
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

            <div class="row justify-content-end mt-4">
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Subtotal</span>
                        <span>{{ number_format((float) $sale->subtotal, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Discount</span>
                        <span>{{ number_format((float) $sale->discount, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between py-3 border-top">
                        <strong>Grand Total</strong>
                        <strong>{{ number_format((float) $sale->grand_total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Paid Amount</span>
                        <span class="text-success">
                            {{ number_format((float) $sale->paid_amount, 2) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-top">
                        <strong>Due Amount</strong>
                        <strong class="{{ (float) $sale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format((float) $sale->due_amount, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            @if($sale->note)
                <div class="border-top mt-4 pt-3">
                    <div class="text-muted small mb-1">Note</div>
                    <div>{{ $sale->note }}</div>
                </div>
            @endif

            <div class="text-center text-muted small mt-5">
                Thank you for your purchase.
            </div>

        </div>
    </div>
</div>

<style>
    @media print {
        .no-print,
        nav,
        .sidebar {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        #saleInvoice {
            border: none !important;
            box-shadow: none !important;
        }

        .card-body {
            padding: 0 !important;
        }
    }
</style>
@endsection
