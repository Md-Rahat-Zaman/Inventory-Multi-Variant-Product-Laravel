@extends('layouts.app_bootstrap')

@section('title', 'Purchases')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Purchases</h4>
            <p class="text-muted mb-0">Manage supplier purchases and payments.</p>
        </div>

        @if (auth()->user()->hasPermission('purchases', 'create'))
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> New Purchase
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
        'searchPlaceholder' => 'Purchase number or supplier name...',
    ])

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SL</th>
                        <th>Purchase No.</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Due</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchases->firstItem() + $loop->index }}</td>

                            <td class="fw-semibold">
                                {{ $purchase->purchase_no }}
                            </td>

                            <td>
                                {{ $purchase->purchase_date->format('d M Y') }}
                            </td>

                            <td>
                                {{ $purchase->supplier->name ?? 'N/A' }}
                            </td>

                            <td class="text-end">
                                {{ number_format((float) $purchase->grand_total, 2) }}
                            </td>

                            <td class="text-end text-success">
                                {{ number_format((float) $purchase->paid_amount, 2) }}
                            </td>

                            <td class="text-end {{ (float) $purchase->due_amount > 0 ? 'text-danger' : '' }}">
                                {{ number_format((float) $purchase->due_amount, 2) }}
                            </td>

                            <td>
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge text-bg-success">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge text-bg-warning">Partial</span>
                                @else
                                    <span class="badge text-bg-danger">Unpaid</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Purchase">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (auth()->user()->hasPermission('purchases', 'edit'))
                                <a href="{{ route('purchases.edit', $purchase->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit Purchase">
                                    <i class="bi bi-pencil-square"></i>
                                    Edit
                                </a>
                                @endif

                                @if (auth()->user()->hasPermission('purchases', 'delete'))
                                <form
                                    action="{{ route('purchases.destroy', $purchase->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this purchase?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-receipt fs-2 text-muted d-block mb-2"></i>
                                <div class="fw-semibold">No purchases found</div>
                                <div class="text-muted small mb-3">
                                    Create your first purchase to get started.
                                </div>
                                @if (auth()->user()->hasPermission('purchases', 'create'))
                                <a href="{{ route('purchases.create') }}"
                                   class="btn btn-sm btn-success">
                                    Create Purchase
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="card-footer bg-white">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection