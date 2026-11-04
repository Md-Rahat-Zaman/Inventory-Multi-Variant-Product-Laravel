@extends('layouts.app_bootstrap')

@section('title', $user ? 'Edit User Access' : 'Add User')

@section('content')
    @php
        $editing = $user !== null;
        $selectedRole = old('role', $user?->role ?? 'staff');
    @endphp

    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Back to users
            </a>
            <h1 class="h3 mt-2 mb-1">{{ $editing ? 'Edit user access' : 'Add user' }}</h1>
            <p class="text-muted mb-0">Choose a role and set access for each module.</p>
        </div>

        <form action="{{ $editing ? route('users.update', $user) : route('users.store') }}" method="POST">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 mb-0">Account details</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required maxlength="255">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="staff" @selected($selectedRole === 'staff')>Staff — selected permissions</option>
                                <option value="admin" @selected($selectedRole === 'admin')>Admin — all access and user management</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Password{{ $editing ? ' (leave blank to keep current)' : '' }}
                            </label>
                            <input id="password" name="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autocomplete="new-password" {{ $editing ? '' : 'required' }}>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   class="form-control" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 mb-1">Module permissions</h2>
                    <p class="small text-muted mb-0">Admin accounts always have full access. Staff with any write permission also get View access so they can use that module.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Module</th>
                                @foreach ($actions as $action => $label)
                                    <th scope="col" class="text-center">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $module => $definition)
                                <tr>
                                    <th scope="row" class="fw-medium">{{ $definition['label'] }}</th>
                                    @foreach ($actions as $action => $label)
                                        <td class="text-center">
                                            @if (in_array($action, $definition['actions'], true))
                                                @php
                                                    $defaultChecked = $user?->hasPermission($module, $action)
                                                        ?? ($module === 'dashboard' && $action === 'view');
                                                    $checked = old("permissions.{$module}.{$action}", $defaultChecked);
                                                @endphp
                                                        <input type="hidden"
                                                               name="permissions[{{ $module }}][{{ $action }}]" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                       name="permissions[{{ $module }}][{{ $action }}]" value="1"
                                                       id="permission-{{ $module }}-{{ $action }}"
                                                       data-module="{{ $module }}"
                                                       data-action="{{ $action }}"
                                                       @checked((bool) $checked)
                                                       aria-label="{{ $definition['label'] }}: {{ $label }}">
                                            @else
                                                <span class="text-muted" aria-hidden="true">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('permissions')<div class="card-footer text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    {{ $editing ? 'Save access' : 'Create user' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.form-check-input[data-action]:not([data-action="view"])').forEach((input) => {
            input.addEventListener('change', () => {
                if (!input.checked) {
                    return;
                }

                const viewPermission = document.querySelector(
                    `.form-check-input[data-module="${input.dataset.module}"][data-action="view"]`
                );

                if (viewPermission) {
                    viewPermission.checked = true;
                }
            });
        });
    </script>
@endsection
