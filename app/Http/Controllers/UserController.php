<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => null,
            'modules' => User::PERMISSION_MODULES,
            'actions' => User::PERMISSION_ACTION_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedUser($request);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'role' => $validated['role'],
            'permissions' => $this->permissionsFor($validated['role'], $validated['permissions'] ?? []),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'modules' => User::PERMISSION_MODULES,
            'actions' => User::PERMISSION_ACTION_LABELS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validatedUser($request, $user);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withInput()->withErrors([
                'role' => 'You cannot remove your own administrator access.',
            ]);
        }

        if ($user->isAdministrator() && $validated['role'] !== 'admin'
            && User::query()->where('role', 'admin')->count() <= 1) {
            return back()->withInput()->withErrors([
                'role' => 'The last administrator cannot be changed to staff.',
            ]);
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'permissions' => $this->permissionsFor($validated['role'], $validated['permissions'] ?? []),
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account from user management.',
            ]);
        }

        if ($user->isAdministrator()
            && User::query()->where('role', 'admin')->count() <= 1) {
            return back()->withErrors([
                'user' => 'The last administrator cannot be deleted.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    private function validatedUser(Request $request, ?User $user = null): array
    {
        $moduleKeys = implode(',', array_keys(User::PERMISSION_MODULES));
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => $user
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
            'permissions' => ['nullable', "array:{$moduleKeys}"],
            'permissions.*' => ['array:view,create,edit,delete'],
            'permissions.*.*' => ['boolean'],
        ]);

        if ($validated['role'] === 'admin' && ! $request->user()->isAdministrator()) {
            abort(403);
        }

        return $validated;
    }

    private function permissionsFor(string $role, array $submitted): array
    {
        if ($role === 'admin') {
            return User::defaultPermissions();
        }

        $permissions = [];

        foreach (User::PERMISSION_MODULES as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $permissions[$module][$action] = (bool) ($submitted[$module][$action] ?? false);
            }

            if (isset($permissions[$module]['view'])) {
                $permissions[$module]['view'] = $permissions[$module]['view']
                    || ($permissions[$module]['create'] ?? false)
                    || ($permissions[$module]['edit'] ?? false)
                    || ($permissions[$module]['delete'] ?? false);
            }
        }

        return $permissions;
    }
}
