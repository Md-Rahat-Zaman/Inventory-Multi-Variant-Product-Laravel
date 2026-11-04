<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $items = $this->inventoryItems();
        $lowStockCount = $items->filter(fn (array $item) => $item['stock'] <= $item['minimum_stock'])->count();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:in_stock,low_stock,out_of_stock'],
        ]);
        $search = mb_strtolower($filters['search'] ?? '');

        $filteredItems = $items
            ->filter(function (array $item) use ($filters, $search) {
                if (! empty($filters['category']) && $item['category'] !== $filters['category']) {
                    return false;
                }

                if ($search !== '' && ! str_contains(mb_strtolower(implode(' ', array_filter([
                    $item['name'],
                    $item['variant'],
                    $item['sku'],
                    $item['category'],
                    $item['unit'],
                ]))), $search)) {
                    return false;
                }

                return match ($filters['status'] ?? '') {
                    'in_stock' => $item['stock'] > $item['minimum_stock'],
                    'low_stock' => $item['stock'] > 0 && $item['stock'] <= $item['minimum_stock'],
                    'out_of_stock' => $item['stock'] <= 0,
                    default => true,
                };
            })
            ->values();

        return view('inventory.index', [
            'items' => $this->paginateItems($filteredItems)->withQueryString(),
            'itemCount' => $items->count(),
            'totalStock' => $items->sum('stock'),
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $items->where('stock', '<=', 0)->count(),
            'categories' => $items->pluck('category')->filter()->unique()->sort()->values(),
            'filters' => $filters,
        ]);
    }

    public function createAdjustment(): View
    {
        return view('inventory.adjustment', [
            'items' => $this->inventoryItems(),
        ]);
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item' => ['required', 'regex:/^(product|variant):\d+$/'],
            'adjustment_type' => ['required', 'in:increase,decrease,set'],
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($validated['adjustment_type'] !== 'set' && (float) $validated['quantity'] <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Enter a quantity greater than zero for an increase or decrease.',
            ]);
        }

        [$itemType, $itemId] = explode(':', $validated['item'], 2);

        DB::transaction(function () use ($request, $validated, $itemType, $itemId) {
            if ($itemType === 'product') {
                $item = Product::query()
                    ->whereDoesntHave('variants')
                    ->whereKey($itemId)
                    ->lockForUpdate()
                    ->firstOrFail();
                $variantName = null;
                $productId = $item->id;
                $variantId = null;
            } else {
                $item = ProductVariant::query()
                    ->with(['product', 'color', 'size'])
                    ->whereKey($itemId)
                    ->lockForUpdate()
                    ->firstOrFail();
                $variantName = collect([$item->color?->name, $item->size?->name])
                    ->filter()
                    ->implode(' / ') ?: 'Variant #'.$item->id;
                $productId = $item->product_id;
                $variantId = $item->id;
            }

            $stockBefore = (float) $item->stock;
            $quantity = (float) $validated['quantity'];
            $stockAfter = round(match ($validated['adjustment_type']) {
                'increase' => $stockBefore + $quantity,
                'decrease' => $stockBefore - $quantity,
                default => $quantity,
            }, 2);

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Stock cannot be reduced below zero. Available stock: {$stockBefore}.",
                ]);
            }

            $item->stock = round($stockAfter, 2);

            if (array_key_exists('minimum_stock', $validated) && $validated['minimum_stock'] !== null) {
                $item->minimum_stock = $validated['minimum_stock'];
            }

            $item->save();

            if ($item instanceof ProductVariant) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $product->update([
                    'stock' => $item->product->variants()->sum('stock'),
                ]);
            }

            StockAdjustment::create([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'user_id' => $request->user()->id,
                'item_name' => $item instanceof ProductVariant ? $item->product->name : $item->name,
                'variant_name' => $variantName,
                'adjustment_type' => $validated['adjustment_type'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'quantity_change' => $stockAfter - $stockBefore,
                'reason' => $validated['reason'],
            ]);
        });

        return redirect()->route('inventory.history')->with('success', 'Stock adjusted successfully.');
    }

    public function history(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:Sale,Purchase,Sale return,Purchase return,Adjustment'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        if (! empty($filters['from']) && ! empty($filters['to']) && $filters['to'] < $filters['from']) {
            throw ValidationException::withMessages([
                'to' => 'The to date must be on or after the from date.',
            ]);
        }

        $saleItems = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_items.product_variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_variants.color_id')
            ->leftJoin('sizes', 'sizes.id', '=', 'product_variants.size_id')
            ->where('sales.status', 'completed')
            ->select([
                'sale_items.id as entry_id',
                'sale_items.created_at',
                'products.name as item_name',
                'colors.name as color_name',
                'sizes.name as size_name',
                DB::raw('NULL as variant_name'),
                'sales.sale_no as reference',
                DB::raw("'Sale' as movement_type"),
                DB::raw('-sale_items.quantity as quantity_change'),
                DB::raw('NULL as stock_before'),
                DB::raw('NULL as stock_after'),
                DB::raw("'Stock sold' as reason"),
                DB::raw('NULL as user_name'),
            ]);

        $purchaseItems = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'purchase_items.product_variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_variants.color_id')
            ->leftJoin('sizes', 'sizes.id', '=', 'product_variants.size_id')
            ->select([
                'purchase_items.id as entry_id',
                'purchase_items.created_at',
                'products.name as item_name',
                'colors.name as color_name',
                'sizes.name as size_name',
                DB::raw('NULL as variant_name'),
                'purchases.purchase_no as reference',
                DB::raw("'Purchase' as movement_type"),
                'purchase_items.quantity as quantity_change',
                DB::raw('NULL as stock_before'),
                DB::raw('NULL as stock_after'),
                DB::raw("'Stock received' as reason"),
                DB::raw('NULL as user_name'),
            ]);

        $saleReturns = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_return_items.product_variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_variants.color_id')
            ->leftJoin('sizes', 'sizes.id', '=', 'product_variants.size_id')
            ->select([
                'sale_return_items.id as entry_id',
                'sale_return_items.created_at',
                'products.name as item_name',
                'colors.name as color_name',
                'sizes.name as size_name',
                DB::raw('NULL as variant_name'),
                'sale_returns.return_no as reference',
                DB::raw("'Sale return' as movement_type"),
                'sale_return_items.quantity as quantity_change',
                DB::raw('NULL as stock_before'),
                DB::raw('NULL as stock_after'),
                DB::raw("'Returned stock' as reason"),
                DB::raw('NULL as user_name'),
            ]);

        $purchaseReturns = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->join('products', 'products.id', '=', 'purchase_return_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'purchase_return_items.product_variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_variants.color_id')
            ->leftJoin('sizes', 'sizes.id', '=', 'product_variants.size_id')
            ->select([
                'purchase_return_items.id as entry_id',
                'purchase_return_items.created_at',
                'products.name as item_name',
                'colors.name as color_name',
                'sizes.name as size_name',
                DB::raw('NULL as variant_name'),
                'purchase_returns.return_no as reference',
                DB::raw("'Purchase return' as movement_type"),
                DB::raw('-purchase_return_items.quantity as quantity_change'),
                DB::raw('NULL as stock_before'),
                DB::raw('NULL as stock_after'),
                DB::raw("'Stock returned to supplier' as reason"),
                DB::raw('NULL as user_name'),
            ]);

        $adjustments = DB::table('stock_adjustments')
            ->leftJoin('users', 'users.id', '=', 'stock_adjustments.user_id')
            ->select([
                'stock_adjustments.id as entry_id',
                'stock_adjustments.created_at',
                'stock_adjustments.item_name',
                DB::raw('NULL as color_name'),
                DB::raw('NULL as size_name'),
                'stock_adjustments.variant_name',
                DB::raw('NULL as reference'),
                DB::raw("'Adjustment' as movement_type"),
                'stock_adjustments.quantity_change',
                'stock_adjustments.stock_before',
                'stock_adjustments.stock_after',
                'stock_adjustments.reason',
                'users.name as user_name',
            ]);

        $historyQuery = $saleItems
            ->unionAll($purchaseItems)
            ->unionAll($saleReturns)
            ->unionAll($purchaseReturns)
            ->unionAll($adjustments);

        $history = DB::query()
            ->fromSub($historyQuery, 'stock_history')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('item_name', 'like', '%'.$search.'%')
                        ->orWhere('variant_name', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%')
                        ->orWhere('reason', 'like', '%'.$search.'%')
                        ->orWhere('user_name', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('movement_type', $type))
            ->when($filters['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->orderByDesc('entry_id');
        $adjustments = $history->paginate(20)->withQueryString();

        return view('inventory.history', compact('adjustments', 'filters'));
    }

    public function lowStock(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:low_stock,out_of_stock'],
        ]);
        $search = mb_strtolower($filters['search'] ?? '');
        $items = $this->inventoryItems()
            ->filter(fn (array $item) => $item['stock'] <= $item['minimum_stock']);
        $categories = $items->pluck('category')->filter()->unique()->sort()->values();
        $items = $items
            ->filter(function (array $item) use ($filters, $search) {
                if (! empty($filters['category']) && $item['category'] !== $filters['category']) {
                    return false;
                }

                if ($search !== '' && ! str_contains(mb_strtolower(implode(' ', array_filter([
                    $item['name'],
                    $item['variant'],
                    $item['sku'],
                    $item['category'],
                    $item['unit'],
                ]))), $search)) {
                    return false;
                }

                return match ($filters['status'] ?? '') {
                    'low_stock' => $item['stock'] > 0,
                    'out_of_stock' => $item['stock'] <= 0,
                    default => true,
                };
            })
            ->sortBy('stock')
            ->values();

        return view('inventory.low-stock', [
            'items' => $this->paginateItems($items)->withQueryString(),
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    private function inventoryItems(): Collection
    {
        $products = Product::query()
            ->with(['category', 'unit'])
            ->whereDoesntHave('variants')
            ->get()
            ->map(fn (Product $product) => [
                'key' => 'product:'.$product->id,
                'type' => 'Product',
                'name' => $product->name,
                'variant' => null,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'unit' => $product->unit?->name,
                'stock' => (float) $product->stock,
                'minimum_stock' => (float) $product->minimum_stock,
            ]);

        $variants = ProductVariant::query()
            ->with(['product.category', 'product.unit', 'color', 'size'])
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'key' => 'variant:'.$variant->id,
                'type' => 'Variant',
                'name' => $variant->product->name,
                'variant' => collect([$variant->color?->name, $variant->size?->name])
                    ->filter()
                    ->implode(' / ') ?: 'Variant #'.$variant->id,
                'sku' => $variant->sku,
                'category' => $variant->product->category?->name,
                'unit' => $variant->product->unit?->name,
                'stock' => (float) $variant->stock,
                'minimum_stock' => (float) $variant->minimum_stock,
            ]);

        return $products->concat($variants)->sortBy('name')->values();
    }

    private function paginateItems(Collection $items): LengthAwarePaginator
    {
        $perPage = 20;
        $currentPage = Paginator::resolveCurrentPage();

        return new Paginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

}
