<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('password');
            $table->json('permissions')->nullable()->after('role');
        });

        $permissions = [];

        foreach ([
            'dashboard' => ['view'],
            'products' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'units' => ['view', 'create', 'edit', 'delete'],
            'brands' => ['view', 'create', 'edit', 'delete'],
            'purchases' => ['view', 'create', 'edit', 'delete'],
            'purchase_returns' => ['view', 'create', 'edit', 'delete'],
            'sales' => ['view', 'create', 'edit', 'delete'],
            'sale_returns' => ['view', 'create', 'edit', 'delete'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'posts' => ['view', 'create', 'edit', 'delete'],
        ] as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[$module][$action] = true;
            }
        }

        DB::table('users')->update([
            'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'permissions']);
        });
    }
};
