@extends('layouts.app_bootstrap')

@section('title', 'User Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Users &amp; access</h1>
                <p class="text-muted mb-0">Manage accounts, roles, and module permissions.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                Add user
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->has('user'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('user') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Role</th>
                            <th scope="col">Module access</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $accessibleModules = collect($user->permissions ?? [])
                                    ->filter(fn ($permissions) => collect($permissions)->contains(true))
                                    ->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $user->isAdministrator() ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                        {{ $user->isAdministrator() ? 'Admin' : 'Staff' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->isAdministrator())
                                        <span class="text-muted">All modules</span>
                                    @else
                                        {{ $accessibleModules }} module{{ $accessibleModules === 1 ? '' : 's' }}
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                        Edit access
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this user account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5 text-center text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="card-footer bg-white">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection
