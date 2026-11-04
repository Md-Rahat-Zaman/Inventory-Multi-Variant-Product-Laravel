<?php

use App\Models\User;

test('administrators have every module permission', function () {
    $user = new User;
    $user->forceFill(['role' => 'admin', 'permissions' => []]);

    expect($user->hasPermission('products', 'delete'))->toBeTrue()
        ->and($user->hasPermission('reports', 'view'))->toBeTrue()
        ->and($user->hasPermission('users', 'view'))->toBeTrue();
});

test('staff can only use explicitly granted permissions', function () {
    $user = new User;
    $user->forceFill([
        'role' => 'staff',
        'permissions' => [
            'dashboard' => ['view' => true],
            'products' => ['view' => true, 'create' => false],
        ],
    ]);

    expect($user->hasPermission('dashboard', 'view'))->toBeTrue()
        ->and($user->hasPermission('products', 'view'))->toBeTrue()
        ->and($user->hasPermission('products', 'create'))->toBeFalse()
        ->and($user->hasPermission('products', 'delete'))->toBeFalse()
        ->and($user->hasPermission('unknown', 'view'))->toBeFalse()
        ->and($user->hasAnyPermission('products', ['create', 'view']))->toBeTrue();
});
