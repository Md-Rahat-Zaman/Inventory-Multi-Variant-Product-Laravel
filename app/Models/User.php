<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERMISSION_MODULES = [
        'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
        'inventory' => ['label' => 'Inventory', 'actions' => ['view', 'create']],
        'products' => ['label' => 'Products', 'actions' => ['view', 'create', 'edit', 'delete']],
        'categories' => ['label' => 'Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
        'units' => ['label' => 'Units', 'actions' => ['view', 'create', 'edit', 'delete']],
        'brands' => ['label' => 'Brands', 'actions' => ['view', 'create', 'edit', 'delete']],
        'purchases' => ['label' => 'Purchases', 'actions' => ['view', 'create', 'edit', 'delete']],
        'purchase_returns' => ['label' => 'Purchase returns', 'actions' => ['view', 'create', 'edit', 'delete']],
        'sales' => ['label' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete']],
        'sale_returns' => ['label' => 'Sales returns', 'actions' => ['view', 'create', 'edit', 'delete']],
        'customers' => ['label' => 'Customers', 'actions' => ['view', 'create', 'edit', 'delete']],
        'reports' => ['label' => 'Reports', 'actions' => ['view']],
        'posts' => ['label' => 'Posts', 'actions' => ['view', 'create', 'edit', 'delete']],
    ];

    public const PERMISSION_ACTION_LABELS = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ];

    public static function defaultPermissions(): array
    {
        $permissions = [];

        foreach (self::PERMISSION_MODULES as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $permissions[$module][$action] = true;
            }
        }

        return $permissions;
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if (! in_array($action, self::PERMISSION_MODULES[$module]['actions'] ?? [], true)) {
            return false;
        }

        return (bool) data_get($this->permissions ?? [], "{$module}.{$action}", false);
    }

    public function hasAnyPermission(string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if ($this->hasPermission($module, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
