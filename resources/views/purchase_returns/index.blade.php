@extends('layouts.app_bootstrap')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Purchase Returns</h4>

        @if (auth()->user()->hasPermission('purchase_returns', 'create'))
        <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Purchase Return
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Return No</th>
                        <th>Purchase No</th>
                        <th>Supplier</th>
                        <th>Return Date</th>
                        <th class="text-end">Total</th>
                        <th width="210">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchaseReturns as $return)
                        <tr>
                            <td>{{ $purchaseReturns->firstItem() + $loop->index }}</td>
                            <td>{{ $return->return_no }}</td>
                            <td>{{ $return->purchase->purchase_no ?? $return->purchase_id }}</td>
                            <td>{{ $return->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $return->return_date->format('d M Y') }}</td>
                            <td class="text-end">
                                {{ number_format((float) $return->total_amount, 2) }}
                            </td>
                            <td>
                                <a href="{{ route('purchase-returns.show', $return) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if (auth()->user()->hasPermission('purchase_returns', 'edit'))
                                <a href="{{ route('purchase-returns.edit', $return) }}"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif

                                @if (auth()->user()->hasPermission('purchase_returns', 'delete'))
                                <form action="{{ route('purchase-returns.destroy', $return) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this return and restore stock?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No purchase returns found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $purchaseReturns->links() }}
        </div>
    </div>

</div>
@endsection