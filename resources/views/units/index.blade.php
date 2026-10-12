@extends('layouts.app_bootstrap')

@section('title', 'Units')

@section('content')

<div class="container-fluid module-page">


{{-- Page Header --}}
<div class="module-header">
    <div class="module-header-row">

        <div>
            <h3 class="module-title">Units</h3>
            <p class="module-subtitle">
                Manage your product units
            </p>
        </div>

        <div class="module-header-actions">
            <button type="button"
                    class="btn btn-primary module-btn quick-create-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#quickCreateModal"
                    data-type="unit"
                    data-mode="create">

                <i class="bi bi-plus-lg"></i>
                Add Unit

            </button>
        </div>

    </div>
</div>


{{-- Unit Card --}}
<div class="module-card">

    <div class="module-card-header">

        <div>
            <h5 class="module-card-title">
                Units List
            </h5>

            <span class="module-card-count">
                {{ $units->total() }} Units
            </span>
        </div>

    </div>


    {{-- Toolbar --}}
    <div class="module-toolbar">

        <div class="module-search">
            <i class="bi bi-search module-search-icon"></i>

            <input type="text"
                   class="module-search-input"
                   placeholder="Search units...">
        </div>

    </div>


    {{-- Table --}}
    <div class="module-table-wrapper">

        <table class="table module-table">

            <thead>
                <tr>
                    <th width="70">#</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="100">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($units as $unit)

                    <tr>

                        <td class="module-serial">
                            {{ $units->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <div class="module-name">
                                {{ $unit->name }}
                            </div>
                        </td>

                        <td>
                            <span class="module-secondary">
                                {{ $unit->slug }}
                            </span>
                        </td>

                        <td>
                            @if($unit->status)
                                <span class="module-badge module-badge-success">
                                    Active
                                </span>
                            @else
                                <span class="module-badge module-badge-danger">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="module-secondary">
                                {{ $unit->created_at->format('d M Y') }}
                            </span>
                        </td>

                        <td>

                            <div class="dropdown module-action">

                                <button class="btn module-action-btn"
                                        type="button"
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>

                                <ul class="dropdown-menu module-action-menu">

                                    <li>
                                        <button type="button"
                                                class="dropdown-item quick-create-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quickCreateModal"
                                                data-type="unit"
                                                data-mode="edit"
                                                data-unit='@json($unit)'>

                                            <i class="bi bi-pencil me-2"></i>
                                            Edit

                                        </button>
                                    </li>

                                    <li>
                                        <form action="{{ route('units.destroy', $unit) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this unit?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="dropdown-item delete-item">
                                                <i class="bi bi-trash me-2"></i>
                                                Delete
                                            </button>

                                        </form>
                                    </li>

                                </ul>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">

                            <div class="module-empty">

                                <div class="module-empty-icon">
                                    <i class="bi bi-folder"></i>
                                </div>

                                <h5 class="module-empty-title">
                                    No Units Found
                                </h5>

                                <p class="module-empty-text">
                                    Start by creating your first unit.
                                </p>

                                <button type="button"
                                        class="btn btn-primary module-btn quick-create-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#quickCreateModal"
                                        data-type="unit"
                                        data-mode="create">

                                    <i class="bi bi-plus-lg"></i>
                                    Add Unit

                                </button>

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- Pagination --}}
    @if($units->hasPages())

        <div class="module-pagination">

            <div class="module-pagination-info">
                Showing
                {{ $units->firstItem() }}
                to
                {{ $units->lastItem() }}
                of
                {{ $units->total() }}
            </div>

            <div>
                {{ $units->links() }}
            </div>

        </div>

    @endif

</div>

</div>

@include('components.quick-create-modal')
@endsection
