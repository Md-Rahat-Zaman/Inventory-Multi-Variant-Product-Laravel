<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleReturnController extends Controller
{
    public function index()
    {
        $saleReturns = SaleReturn::with(['sale', 'customer'])
            ->latest('return_date')
            ->latest('id')
            ->paginate(15);

        return view('sale_returns.index', compact('saleReturns'));
    }

    public function create(Request $request)
    {
        $sales = Sale::with('customer')
            ->where('status', 'completed')
            ->latest('sale_date')
            ->latest('id')
            ->get();

        $selectedSale = null;
        $saleItems = collect();
        $returnableQuantities = [];

        if ($request->filled('sale_id')) {
            $selectedSale = Sale::with('customer')
                ->where('status', 'completed')
                ->findOrFail($request->integer('sale_id'));

            $saleItems = SaleItem::with('product')
                ->where('sale_id', $selectedSale->id)
                ->get();

            foreach ($saleItems as $saleItem) {
                $alreadyReturned = SaleReturnItem::where('sale_item_id', $saleItem->id)
                    ->sum('quantity');

                $returnableQuantities[$saleItem->id] = max(
                    0,
                    (float) $saleItem->quantity - (float) $alreadyReturned
                );
            }
        }
        $isEdit = false;
        return view('sale_returns.create', compact(
            'sales',
            'selectedSale',
            'saleItems',
            'returnableQuantities',
            'isEdit'
        ));
    }

    public function store(Request $request)
    {
        $this->normalizeItems($request);
        $validated = $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer', 'distinct', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $return = DB::transaction(function () use ($validated) {
            $sale = Sale::whereKey($validated['sale_id'])
                ->where('status', 'completed')
                ->lockForUpdate()
                ->firstOrFail();

            $return = SaleReturn::create([
                'return_no' => $this->generateReturnNumber(),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'return_date' => $validated['return_date'],
                'total_amount' => 0,
                'note' => $validated['note'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $saleItem = SaleItem::whereKey($item['sale_item_id'])
                    ->where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = (float) $item['quantity'];
                $alreadyReturned = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)
                    ->lockForUpdate()
                    ->sum('quantity');

                $availableToReturn = (float) $saleItem->quantity - $alreadyReturned;

                if ($quantity > $availableToReturn) {
                    throw ValidationException::withMessages([
                        'items' => "Return quantity for sale item #{$saleItem->id} cannot exceed {$availableToReturn}.",
                    ]);
                }

                $variant = ProductVariant::whereKey($saleItem->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $effectiveUnitPrice = (float) $saleItem->total / max((float) $saleItem->quantity, 0.000001);
                $lineTotal = round($quantity * $effectiveUnitPrice, 2);

                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => round($effectiveUnitPrice, 2),
                    'total' => $lineTotal,
                ]);

                $variant->increment('stock', $quantity);
                $total += $lineTotal;
            }

            $return->update(['total_amount' => round($total, 2)]);
            $this->syncProductStocks($return->items()->pluck('product_id')->unique()->all());

            return $return;
        });

        return redirect()->route('sale-returns.show', $return)
            ->with('success', 'Sale return created successfully.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'customer', 'items.product', 'items.variant', 'items.saleItem']);

        return view('sale_returns.show', compact('saleReturn'));
    }

    public function edit(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'items']);

        $saleItems = SaleItem::with('product')
            ->where('sale_id', $saleReturn->sale_id)
            ->get();

        $returnableQuantities = [];
        $currentQuantities = $saleReturn->items->keyBy('sale_item_id');

        foreach ($saleItems as $saleItem) {
            $returnedByOthers = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)
                ->where('sale_return_id', '!=', $saleReturn->id)
                ->sum('quantity');

            $returnableQuantities[$saleItem->id] = max(
                0,
                (float) $saleItem->quantity - $returnedByOthers
            );
        }
        $isEdit = true;
        return view('sale_returns.edit', compact(
            'saleReturn',
            'saleItems',
            'returnableQuantities',
            'currentQuantities',
            'isEdit'
        ));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        $this->normalizeItems($request);
        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer', 'distinct', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        DB::transaction(function () use ($validated, $saleReturn) {
            $return = SaleReturn::whereKey($saleReturn->id)->lockForUpdate()->firstOrFail();
            $sale = Sale::whereKey($return->sale_id)->lockForUpdate()->firstOrFail();

            $oldItems = $return->items()->get()->keyBy('sale_item_id');
            $newItems = collect($validated['items'])->keyBy(fn ($item) => (int) $item['sale_item_id']);

            $allSaleItemIds = $oldItems->keys()
                ->merge($newItems->keys())
                ->unique()
                ->values();

            $productIds = [];

            foreach ($allSaleItemIds as $saleItemId) {
                $saleItem = SaleItem::whereKey($saleItemId)
                    ->where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldQuantity = (float) ($oldItems->get($saleItemId)->quantity ?? 0);
                $newQuantity = (float) ($newItems->get($saleItemId)['quantity'] ?? 0);
                $otherReturns = (float) SaleReturnItem::where('sale_item_id', $saleItemId)
                    ->where('sale_return_id', '!=', $return->id)
                    ->sum('quantity');

                if ($newQuantity > 0 && $newQuantity > ((float) $saleItem->quantity - $otherReturns)) {
                    throw ValidationException::withMessages([
                        'items' => "Return quantity for sale item #{$saleItemId} exceeds the remaining returnable quantity.",
                    ]);
                }

                $variant = ProductVariant::whereKey($saleItem->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockAdjustment = $newQuantity - $oldQuantity;

                if ($stockAdjustment < 0 && (float) $variant->stock < abs($stockAdjustment)) {
                    throw ValidationException::withMessages([
                        'items' => "Cannot reduce this return because the returned stock has already been used. Variant #{$variant->id} needs at least " . abs($stockAdjustment) . ' stock.',
                    ]);
                }

                if ($stockAdjustment > 0) {
                    $variant->increment('stock', $stockAdjustment);
                } elseif ($stockAdjustment < 0) {
                    $variant->decrement('stock', abs($stockAdjustment));
                }

                if ($newQuantity <= 0) {
                    $oldItems->get($saleItemId)?->delete();
                } else {
                    $effectiveUnitPrice = (float) $saleItem->total / max((float) $saleItem->quantity, 0.000001);
                    $lineTotal = round($newQuantity * $effectiveUnitPrice, 2);

                    $returnItem = $oldItems->get($saleItemId);

                    if ($returnItem) {
                        $returnItem->update([
                            'quantity' => $newQuantity,
                            'unit_price' => round($effectiveUnitPrice, 2),
                            'total' => $lineTotal,
                        ]);
                    } else {
                        SaleReturnItem::create([
                            'sale_return_id' => $return->id,
                            'sale_item_id' => $saleItem->id,
                            'product_id' => $saleItem->product_id,
                            'product_variant_id' => $variant->id,
                            'quantity' => $newQuantity,
                            'unit_price' => round($effectiveUnitPrice, 2),
                            'total' => $lineTotal,
                        ]);
                    }
                }

                $productIds[] = (int) $saleItem->product_id;
            }

            $return->update([
                'return_date' => $validated['return_date'],
                'note' => $validated['note'] ?? null,
                'total_amount' => round((float) $return->items()->sum('total'), 2),
            ]);

            $this->syncProductStocks(array_unique($productIds));
        });

        return redirect()->route('sale-returns.show', $saleReturn)
            ->with('success', 'Sale return updated successfully.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        DB::transaction(function () use ($saleReturn) {
            $return = SaleReturn::whereKey($saleReturn->id)->lockForUpdate()->firstOrFail();
            $items = $return->items()->get();
            $productIds = [];

            foreach ($items as $item) {
                $variant = ProductVariant::whereKey($item->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $variant->stock < (float) $item->quantity) {
                    throw ValidationException::withMessages([
                        'sale_return' => "Cannot delete this return: variant #{$variant->id} does not have enough stock to reverse the return.",
                    ]);
                }

                $variant->decrement('stock', $item->quantity);
                $productIds[] = (int) $item->product_id;
            }

            $this->syncProductStocks(array_unique($productIds));
            $return->delete();
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Sale return deleted successfully.');
    }

    private function normalizeItems(Request $request): void
    {
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => is_array($item) && (float) ($item['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        $request->merge(['items' => $items]);
    }

    private function generateReturnNumber(): string
    {
        do {
            $returnNo = 'SR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (SaleReturn::where('return_no', $returnNo)->exists());

        return $returnNo;
    }

    private function syncProductStocks(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $product = Product::whereKey($productId)->lockForUpdate()->first();

            if ($product) {
                $product->update([
                    'stock' => $product->variants()->sum('stock'),
                ]);
            }
        }
    }
}
