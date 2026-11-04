@extends('layouts.app_bootstrap')

@section('title', 'Customers & Suppliers')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Customers & Suppliers</h4>
            <div class="text-muted small">
                Manage your customers, suppliers and business parties.
            </div>
        </div>

        @if (auth()->user()->hasPermission('customers', 'create'))
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Party
        </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-5">
                        <label for="search" class="form-label">Search</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Name, phone, email or company"
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="type" class="form-label">Party Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="customer" @selected(request('type') === 'customer')>
                                Customer
                            </option>
                            <option value="supplier" @selected(request('type') === 'supplier')>
                                Supplier
                            </option>
                            <option value="both" @selected(request('type') === 'both')>
                                Both
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('status') === '1')>
                                Active
                            </option>
                            <option value="0" @selected(request('status') === '0')>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-1"></i> Search
                        </button>

                        <a href="{{ route('customers.index') }}" class="btn btn-light border">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Party List</h6>
            <span class="badge text-bg-light border">
                {{ $customers->total() }} records
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>
                                    {{ $customers->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $customer->name }}</div>
                                    @if ($customer->address)
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($customer->address, 45) }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $customer->company_name ?: '—' }}</td>
                                <td>{{ $customer->phone ?: '—' }}</td>
                                <td>{{ $customer->email ?: '—' }}</td>

                                <td>
                                    @php
                                        $type = strtolower($customer->type ?? '');
                                        $typeClass = match ($type) {
                                            'customer' => 'text-bg-primary',
                                            'supplier' => 'text-bg-info',
                                            'both' => 'text-bg-success',
                                            default => 'text-bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge {{ $typeClass }}">
                                        {{ ucfirst($type ?: 'N/A') }}
                                    </span>
                                </td>

                                <td>
                                    @if ($customer->status)
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a
                                            href="{{ route('customers.show', $customer->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if (auth()->user()->hasPermission('customers', 'edit'))
                                        <a
                                            href="{{ route('customers.edit', $customer->id) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif

                                        @if (auth()->user()->hasPermission('customers', 'delete'))
                                        <form
                                            action="{{ route('customers.destroy', $customer->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this party?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-people fs-2 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">No parties found</div>
                                    <div class="text-muted small">
                                        Try changing your search or add a new party.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($customers->hasPages())
            <div class="card-footer bg-white">
                {{ $customers->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
