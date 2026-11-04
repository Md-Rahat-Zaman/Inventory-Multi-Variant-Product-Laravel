@extends('layouts.app_bootstrap')

@section('title', 'Sale Returns')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-arrow-return-left me-2"></i>Sale Returns</h4>
            <p class="text-muted mb-0">Manage returned products.</p>
        </div>
        @if (auth()->user()->hasPermission('sale_returns', 'create'))
        <a href="{{ route('sale-returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Sale Return
        </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Return No.</th>
                        <th>Sale No.</th>
                        <th>Customer</th>
                        <th>Return Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saleReturns as $saleReturn)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $saleReturn->return_no }}</td>
                            <td>{{ $saleReturn->sale->sale_no ?? ('Sale #' . $saleReturn->sale_id) }}</td>
                            <td>{{ $saleReturn->customer->name ?? $saleReturn->sale->customer_name ?? 'Walk-in Customer' }}</td>
                            <td>{{ $saleReturn->return_date?->format('d M Y') }}</td>
                            <td class="text-end">{{ number_format((float) $saleReturn->total_amount, 2) }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('sale-returns.show', $saleReturn) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (auth()->user()->hasPermission('sale_returns', 'edit'))
                                <a href="{{ route('sale-returns.edit', $saleReturn) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                @if (auth()->user()->hasPermission('sale_returns', 'delete'))
                                <form action="{{ route('sale-returns.destroy', $saleReturn) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this sale return? Stock will be adjusted back.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No sale returns found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $saleReturns->links() }}</div>
    </div>
</div>
@endsection
