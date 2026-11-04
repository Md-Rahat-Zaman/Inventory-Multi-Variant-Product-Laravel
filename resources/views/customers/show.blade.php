@extends('layouts.app_bootstrap')

@section('title', 'Party Details')

@section('content')
<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Party Details</h4>
            <p class="text-muted mb-0">View party information and sales history.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            @if (auth()->user()->hasPermission('customers', 'edit'))
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit Party
            </a>
            @endif
        </div>
    </div>

    {{-- Party Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-person-vcard me-2"></i>Party Information
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-4">

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Party Name</label>
                    <div class="fw-semibold">{{ $customer->name }}</div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Company Name</label>
                    <div>{{ $customer->company_name ?: '—' }}</div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Phone</label>
                    <div>{{ $customer->phone ?: '—' }}</div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Email</label>
                    <div>{{ $customer->email ?: '—' }}</div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Party Type</label>
                    <div>
                        @if ($customer->type === 'customer')
                            <span class="badge bg-primary-subtle text-primary">Customer</span>
                        @elseif ($customer->type === 'supplier')
                            <span class="badge bg-warning-subtle text-warning-emphasis">Supplier</span>
                        @elseif ($customer->type === 'both')
                            <span class="badge bg-info-subtle text-info-emphasis">Both</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ ucfirst($customer->type ?? 'Unknown') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <label class="text-muted small d-block mb-1">Status</label>
                    <div>
                        @if ($customer->status)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <label class="text-muted small d-block mb-1">Address</label>
                    <div>{{ $customer->address ?: '—' }}</div>
                </div>

                @if (!empty($customer->note))
                    <div class="col-12">
                        <label class="text-muted small d-block mb-1">Note</label>
                        <div>{{ $customer->note }}</div>
                    </div>
                @endif

                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Created At</label>
                    <div>{{ $customer->created_at?->format('d M Y, h:i A') ?? '—' }}</div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Last Updated</label>
                    <div>{{ $customer->updated_at?->format('d M Y, h:i A') ?? '—' }}</div>
                </div>

            </div>
        </div>
    </div>

    {{-- Sales History --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-receipt me-2"></i>Sales History
            </h5>

            <span class="badge bg-primary-subtle text-primary">
                {{ $customer->sales->count() }} Sales
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Sale No.</th>
                            <th>Date</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customer->sales->sortByDesc('id') as $sale)
                            <tr>
                                <td class="ps-3">{{ $loop->iteration }}</td>

                                <td class="fw-semibold">
                                    {{ $sale->sale_no ?? ('SALE-' . $sale->id) }}
                                </td>

                                <td>
                                    {{ $sale->sale_date
                                        ? \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y')
                                        : '—' }}
                                </td>

                                <td class="text-end">
                                    {{ number_format((float) ($sale->grand_total ?? 0), 2) }}
                                </td>

                                <td class="text-end text-success">
                                    {{ number_format((float) ($sale->paid_amount ?? 0), 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    {{ number_format((float) ($sale->due_amount ?? 0), 2) }}
                                </td>

                                <td>
                                    @php
                                        $paymentStatus = strtolower($sale->payment_status ?? '');
                                    @endphp

                                    @if ($paymentStatus === 'paid')
                                        <span class="badge bg-success-subtle text-success">Paid</span>
                                    @elseif ($paymentStatus === 'partial')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Partial</span>
                                    @elseif ($paymentStatus === 'unpaid')
                                        <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ $sale->payment_status ? ucfirst($sale->payment_status) : '—' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('sales.show', $sale->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Sale">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">No sales found</div>
                                    <div class="text-muted small">
                                        No sales have been recorded for this party yet.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($customer->sales->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end">
                                    {{ number_format((float) $customer->sales->sum('grand_total'), 2) }}
                                </td>
                                <td class="text-end text-success">
                                    {{ number_format((float) $customer->sales->sum('paid_amount'), 2) }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format((float) $customer->sales->sum('due_amount'), 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
