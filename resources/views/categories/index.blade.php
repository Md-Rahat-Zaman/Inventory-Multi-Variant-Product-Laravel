@extends('layouts.app_bootstrap')

@section('title', 'Categories')

@section('content')

<div class="container-fluid module-page">


{{-- Page Header --}}
<div class="module-header">
    <div class="module-header-row">

        <div>
            <h3 class="module-title">Categories</h3>
            <p class="module-subtitle">
                Manage your product categories
            </p>
        </div>

        @if (auth()->user()->hasPermission('categories', 'create'))
        <div class="module-header-actions">
            <button type="button"
                    class="btn btn-primary module-btn quick-create-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#quickCreateModal"
                    data-type="category"
                    data-mode="create">

                <i class="bi bi-plus-lg"></i>
                Add Category

            </button>
        </div>
        @endif

    </div>
</div>


{{-- Category Card --}}
<div class="module-card">

    <div class="module-card-header">

        <div>
            <h5 class="module-card-title">
                Category List
            </h5>

            <span class="module-card-count">
                {{ $categories->total() }} Categories
            </span>
        </div>

    </div>


    {{-- Toolbar --}}
    <div class="module-toolbar">

        <div class="module-search">
            <i class="bi bi-search module-search-icon"></i>

            <input type="text"
                   class="module-search-input"
                   placeholder="Search categories...">
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

                @forelse($categories as $category)

                    <tr>

                        <td class="module-serial">
                            {{ $categories->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <div class="module-name">
                                {{ $category->name }}
                            </div>
                        </td>

                        <td>
                            <span class="module-secondary">
                                {{ $category->slug }}
                            </span>
                        </td>

                        <td>
                            @if($category->status)
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
                                {{ $category->created_at->format('d M Y') }}
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

                                    @if (auth()->user()->hasPermission('categories', 'edit'))
                                    <li>
                                        <button type="button"
                                                class="dropdown-item quick-create-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quickCreateModal"
                                                data-type="category"
                                                data-mode="edit"
                                                data-category='@json($category)'>

                                            <i class="bi bi-pencil me-2"></i>
                                            Edit

                                        </button>
                                    </li>
                                    @endif

                                    @if (auth()->user()->hasPermission('categories', 'delete'))
                                    <li>
                                        <form action="{{ route('categories.destroy', $category) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this category?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="dropdown-item delete-item">
                                                <i class="bi bi-trash me-2"></i>
                                                Delete
                                            </button>

                                        </form>
                                    </li>
                                    @endif

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
                                    No Categories Found
                                </h5>

                                <p class="module-empty-text">
                                    Start by creating your first category.
                                </p>

                                @if (auth()->user()->hasPermission('categories', 'create'))
                                <button type="button"
                                        class="btn btn-primary module-btn quick-create-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#quickCreateModal"
                                        data-type="category"
                                        data-mode="create">

                                    <i class="bi bi-plus-lg"></i>
                                    Add Category

                                </button>
                                @endif

                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- Pagination --}}
    @if($categories->hasPages())

        <div class="module-pagination">

            <div class="module-pagination-info">
                Showing
                {{ $categories->firstItem() }}
                to
                {{ $categories->lastItem() }}
                of
                {{ $categories->total() }}
            </div>

            <div>
                {{ $categories->links() }}
            </div>

        </div>

    @endif

</div>

</div>

@include('components.quick-create-modal')
@endsection
