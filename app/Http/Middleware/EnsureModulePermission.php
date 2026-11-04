<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    private const ROUTE_MODULES = [
        'dashboard' => 'dashboard',
        'inventory' => 'inventory',
        'products' => 'products',
        'categories' => 'categories',
        'units' => 'units',
        'brands' => 'brands',
        'purchases' => 'purchases',
        'purchase-returns' => 'purchase_returns',
        'sales' => 'sales',
        'sale-returns' => 'sale_returns',
        'customers' => 'customers',
        'reports' => 'reports',
        'posts' => 'posts',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName) {
            return $next($request);
        }

        if ($routeName === 'quick-create.store') {
            $module = match ($request->input('type')) {
                'category' => 'categories',
                'brand' => 'brands',
                'unit' => 'units',
                default => null,
            };

            abort_unless($module, 403);

            return $this->authorize($request, $next, $user, $module, 'create');
        }

        $routePrefix = explode('.', $routeName, 2)[0];
        $module = self::ROUTE_MODULES[$routePrefix] ?? null;

        if (! $module) {
            return $next($request);
        }

        $action = $this->resolveAction($request, $routeName, $module);

        return $this->authorize($request, $next, $user, $module, $action);
    }

    private function resolveAction(Request $request, string $routeName, string $module): string
    {
        if ($module === 'reports') {
            return 'view';
        }

        if ($routeName === 'sales.pos') {
            return 'create';
        }

        if ($routeName === 'customers.ajax-store') {
            return 'create';
        }

        if (in_array($routeName, ['inventory.adjustments.create', 'inventory.adjustments.store'], true)) {
            return 'create';
        }

        $routeAction = $request->route()?->getActionMethod();

        return match ($routeAction) {
            'create', 'store' => 'create',
            'edit', 'update' => 'edit',
            'destroy' => 'delete',
            default => 'view',
        };
    }

    private function authorize(
        Request $request,
        Closure $next,
        User $user,
        string $module,
        string $action
    ): Response {
        abort_unless($user->hasPermission($module, $action), 403);

        return $next($request);
    }
}
