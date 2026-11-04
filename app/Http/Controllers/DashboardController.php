<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $today = now()->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $weekStart = $today->copy()->subDays(6);

        $canViewSales = $user->hasPermission('sales', 'view');
        $canViewPurchases = $user->hasPermission('purchases', 'view');
        $canViewProducts = $user->hasPermission('products', 'view');
        $canViewCustomers = $user->hasPermission('customers', 'view');

        $salesToday = 0;
        $salesThisMonth = 0;
        $receivables = 0;
        $recentSales = collect();
        $salesChart = collect();

        if ($canViewSales) {
            $completedSales = Sale::query()->where('status', 'completed');

            $salesToday = (clone $completedSales)
                ->whereDate('sale_date', $today)
                ->sum('grand_total');
            $salesThisMonth = (clone $completedSales)
                ->whereBetween('sale_date', [$monthStart, $today])
                ->sum('grand_total');
            $receivables = (clone $completedSales)
                ->where('due_amount', '>', 0)
                ->sum('due_amount');

            $dailySales = (clone $completedSales)
                ->selectRaw('sale_date, SUM(grand_total) as total')
                ->whereBetween('sale_date', [$weekStart, $today])
                ->groupBy('sale_date')
                ->pluck('total', 'sale_date');

            $salesChart = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $dailySales) {
                $date = $weekStart->copy()->addDays($offset);

                return [
                    'label' => $date->format('D'),
                    'date' => $date->format('d M'),
                    'total' => (float) ($dailySales[$date->toDateString()] ?? 0),
                ];
            });

            $recentSales = (clone $completedSales)
                ->latest('sale_date')
                ->latest('id')
                ->limit(6)
                ->get();
        }

        $purchasesToday = 0;
        $purchasesThisMonth = 0;
        $payables = 0;

        if ($canViewPurchases) {
            $purchasesToday = Purchase::query()
                ->whereDate('purchase_date', $today)
                ->sum('grand_total');
            $purchasesThisMonth = Purchase::query()
                ->whereBetween('purchase_date', [$monthStart, $today])
                ->sum('grand_total');
            $payables = Purchase::query()
                ->where('due_amount', '>', 0)
                ->sum('due_amount');
        }

        $totalProducts = 0;
        $totalStock = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $lowStockProducts = collect();

        if ($canViewProducts) {
            $simpleProducts = Product::query()->whereDoesntHave('variants');
            $simpleVariants = ProductVariant::query();

            $totalProducts = Product::query()->count();
            $totalStock = (float) (clone $simpleProducts)->sum('stock')
                + (float) $simpleVariants->sum('stock');
            $lowStockCount = (clone $simpleProducts)
                ->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->count()
                + (clone $simpleVariants)
                    ->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'minimum_stock')
                    ->count();
            $outOfStockCount = (clone $simpleProducts)
                ->where('stock', '<=', 0)
                ->count()
                + (clone $simpleVariants)
                    ->where('stock', '<=', 0)
                    ->count();

            $lowSimpleProducts = (clone $simpleProducts)
                ->with('category')
                ->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')
                ->limit(5)
                ->get()
                ->map(fn (Product $product) => [
                    'name' => $product->name,
                    'variant' => null,
                    'category' => $product->category?->name,
                    'stock' => (float) $product->stock,
                    'minimum_stock' => (float) $product->minimum_stock,
                ]);

            $lowVariants = (clone $simpleVariants)
                ->with(['product.category', 'color', 'size'])
                ->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')
                ->limit(5)
                ->get()
                ->map(fn (ProductVariant $variant) => [
                    'name' => $variant->product->name,
                    'variant' => collect([$variant->color?->name, $variant->size?->name])
                        ->filter()
                        ->implode(' / ') ?: null,
                    'category' => $variant->product->category?->name,
                    'stock' => (float) $variant->stock,
                    'minimum_stock' => (float) $variant->minimum_stock,
                ]);

            $lowStockProducts = $lowSimpleProducts
                ->merge($lowVariants)
                ->sortBy('stock')
                ->take(5)
                ->values();
        }

        $customerCount = $canViewCustomers
            ? Customer::query()->whereIn('type', ['customer', 'both'])->count()
            : null;

        $maxDailySales = (float) $salesChart->max('total');
        $salesChart = $salesChart->map(function (array $day) use ($maxDailySales) {
            $day['bar_height'] = $maxDailySales > 0
                ? max(4, ($day['total'] / $maxDailySales) * 100)
                : 4;

            return $day;
        });

        return view('dashboard.index', compact(
            'canViewSales',
            'canViewPurchases',
            'canViewProducts',
            'canViewCustomers',
            'salesToday',
            'salesThisMonth',
            'receivables',
            'recentSales',
            'salesChart',
            'purchasesToday',
            'purchasesThisMonth',
            'payables',
            'totalProducts',
            'totalStock',
            'lowStockCount',
            'outOfStockCount',
            'lowStockProducts',
            'customerCount'
        ));
    }
}
