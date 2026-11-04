
@extends('layouts.app_bootstrap')

@section('title', 'Sales')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Sales</h4>
            <p class="text-muted mb-0">Manage sales invoices and stock transactions.</p>
        </div>

        @if (auth()->user()->hasPermission('sales', 'create'))
        <a href="{{ route('sales.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> New Sale
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('partials.advanced_list_filter', [
        'filterFields' => $filterFields,
        'filters' => $filters,
        'searchPlaceholder' => 'Invoice, customer name or phone...',
    ])

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SL</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Due</th>
                        <th>Payment Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sales->firstItem() + $loop->index }}</td>

                            <td class="fw-semibold">{{ $sale->sale_no }}</td>

                            <td>{{ $sale->sale_date->format('d M Y') }}</td>

                            <td>
                                {{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in Customer') }}

                                @if($sale->customer_phone)
                                    <div class="text-muted small">{{ $sale->customer_phone }}</div>
                                @endif
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $sale->grand_total, 2) }}
                            </td>

                            <td class="text-end text-success">
                                {{ number_format((float) $sale->paid_amount, 2) }}
                            </td>

                            <td class="text-end {{ (float) $sale->due_amount > 0 ? 'text-danger' : '' }}">
                                {{ number_format((float) $sale->due_amount, 2) }}
                            </td>

                            <td>
                                @if($sale->payment_status === 'paid')
                                    <span class="badge text-bg-success">Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="badge text-bg-warning">Partial</span>
                                @else
                                    <span class="badge text-bg-danger">Unpaid</span>
                                @endif
                            </td>

                            <td class="text-center">
                                
                                 <div class="d-inline-flex gap-1">
                                    <a href="{{ route('sales.show', $sale) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Invoice">
                                    <i class="bi bi-eye"></i>
                                </a>
                                    @if (auth()->user()->hasPermission('sales', 'edit'))
                                    <a href="{{ route('sales.edit', $sale->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit Sale">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>

                                    @endif
                                    @if (auth()->user()->hasPermission('sales', 'delete'))
                                    <form action="{{ route('sales.destroy', $sale->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this sale?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Sale">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                    @endif

                                 </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-receipt fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold">No sales found</div>
                                <div class="text-muted small mb-3">
                                    Create your first sales invoice.
                                </div>
                                @if (auth()->user()->hasPermission('sales', 'create'))
                                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-success">
                                    Create Sale
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="card-footer bg-white">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
