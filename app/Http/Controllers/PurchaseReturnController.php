<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with([
            'purchase',
            'supplier',
        ])->latest()->paginate(15);

        return view('purchase_returns.index', compact('purchaseReturns'));
    }

    public function create(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->latest()
            ->get();

        $selectedPurchaseId = $request->query('purchase_id');
        $purchase = null;
        $purchaseItems = collect();

        if ($selectedPurchaseId) {
            $purchase = Purchase::with('supplier')
                ->findOrFail($selectedPurchaseId);

            $purchaseItems = $this->getPurchaseItems($purchase->id);
        }

        return view('purchase_returns.create', compact(
            'purchases',
            'selectedPurchaseId',
            'purchase',
            'purchaseItems'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => ['required', 'exists:purchases,id'],
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array'],
            'items.*.purchase_item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:purchase_items,id',
            ],
            'items.*.quantity' => ['nullable', 'numeric', 'gte:0'],
        ]);

        DB::transaction(function () use ($data) {
            $purchase = Purchase::whereKey($data['purchase_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $supplier = Customer::whereKey($purchase->supplier_id)
                ->first();

            if (
                !$supplier ||
                !in_array($supplier->type, ['supplier', 'both'], true)
            ) {
                throw ValidationException::withMessages([
                    'purchase_id' => 'The selected purchase must have a valid supplier.',
                ]);
            }

            $return = PurchaseReturn::create([
                'return_no' => $this->generateReturnNo(),
                'purchase_id' => $purchase->id,
                'supplier_id' => $supplier->id,
                'return_date' => $data['return_date'],
                'total_amount' => 0,
                'note' => $data['note'] ?? null,
            ]);

            $totalAmount = 0;
            $hasItems = false;

            foreach ($data['items'] as $row) {
                $quantity = (float) ($row['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $purchaseItem = PurchaseItem::whereKey(
                    $row['purchase_item_id']
                )->lockForUpdate()->firstOrFail();

                if ((int) $purchaseItem->purchase_id !== (int) $purchase->id) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product does not belong to this purchase.',
                    ]);
                }

                $alreadyReturned = $this->returnedQuantity(
                    $purchaseItem->id
                );

                $remainingQuantity = (float) $purchaseItem->quantity
                    - $alreadyReturned;

                if ($quantity > $remainingQuantity + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "Return quantity exceeds the remaining quantity for purchase item #{$purchaseItem->id}.",
                    ]);
                }

                $variant = ProductVariant::whereKey(
                    $purchaseItem->product_variant_id
                )->lockForUpdate()->firstOrFail();

                if ((float) $variant->stock + 0.0001 < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient current stock for purchase item #{$purchaseItem->id}.",
                    ]);
                }

                $unitPrice = (float) $purchaseItem->purchase_price;
                $lineTotal = round($quantity * $unitPrice, 2);

                $return->items()->create([
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'product_variant_id' => $purchaseItem->product_variant_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ]);

                $variant->decrement('stock', $quantity);

                $this->syncProductStock($purchaseItem->product_id);

                $totalAmount += $lineTotal;
                $hasItems = true;
            }

            if (!$hasItems) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a return quantity for at least one product.',
                ]);
            }

            $return->update([
                'total_amount' => $totalAmount,
            ]);
        });

        return redirect()
            ->route('purchase-returns.index')
            ->with('success', 'Purchase return created successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchase',
            'supplier',
            'items.product',
            'items.variant',
            'items.purchaseItem',
        ]);

        return view('purchase_returns.show', compact('purchaseReturn'));
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('items');

        $purchases = Purchase::with('supplier')->latest()->get();

        $purchase = Purchase::with('supplier')
            ->findOrFail($purchaseReturn->purchase_id);

        $purchaseItems = $this->getPurchaseItems(
            $purchase->id,
            $purchaseReturn->id
        );

        $currentQuantities = $purchaseReturn->items
            ->keyBy('purchase_item_id')
            ->map(fn ($item) => (float) $item->quantity);

        return view('purchase_returns.edit', compact(
            'purchases',
            'purchase',
            'purchaseReturn',
            'purchaseItems',
            'currentQuantities'
        ));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $data = $request->validate([
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array'],
            'items.*.purchase_item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:purchase_items,id',
            ],
            'items.*.quantity' => ['nullable', 'numeric', 'gte:0'],
        ]);

        DB::transaction(function () use ($data, $purchaseReturn) {
            $return = PurchaseReturn::whereKey($purchaseReturn->id)
                ->lockForUpdate()
                ->firstOrFail();

            $purchase = Purchase::whereKey($return->purchase_id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldItems = $return->items()
                ->get()
                ->keyBy('purchase_item_id');

            $newQuantities = [];

            foreach ($data['items'] as $row) {
                $purchaseItemId = (int) $row['purchase_item_id'];
                $quantity = (float) ($row['quantity'] ?? 0);

                $purchaseItem = PurchaseItem::whereKey($purchaseItemId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $purchaseItem->purchase_id !== (int) $purchase->id) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product does not belong to this purchase.',
                    ]);
                }

                $newQuantities[$purchaseItemId] = $quantity;
            }

            $allIds = array_unique(array_merge(
                $oldItems->keys()->all(),
                array_keys($newQuantities)
            ));

            $totalAmount = 0;
            $hasItems = false;

            foreach ($allIds as $purchaseItemId) {
                $purchaseItem = PurchaseItem::whereKey($purchaseItemId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldItem = $oldItems->get($purchaseItemId);

                $oldQuantity = $oldItem
                    ? (float) $oldItem->quantity
                    : 0;

                $newQuantity = (float) (
                    $newQuantities[$purchaseItemId] ?? 0
                );

                if ($newQuantity < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Return quantity cannot be negative.',
                    ]);
                }

                $alreadyReturnedElsewhere = $this->returnedQuantity(
                    $purchaseItemId,
                    $return->id
                );

                $maxQuantity = (float) $purchaseItem->quantity
                    - $alreadyReturnedElsewhere;

                if ($newQuantity > $maxQuantity + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => "Return quantity exceeds the remaining quantity for purchase item #{$purchaseItemId}.",
                    ]);
                }

                $difference = $newQuantity - $oldQuantity;

                $variant = ProductVariant::whereKey(
                    $purchaseItem->product_variant_id
                )->lockForUpdate()->firstOrFail();

                if ($difference > 0) {
                    if ((float) $variant->stock + 0.0001 < $difference) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient current stock for purchase item #{$purchaseItemId}.",
                        ]);
                    }

                    $variant->decrement('stock', $difference);
                } elseif ($difference < 0) {
                    $variant->increment('stock', abs($difference));
                }

                if ($newQuantity <= 0) {
                    if ($oldItem) {
                        $oldItem->delete();
                    }
                } else {
                    $unitPrice = (float) $purchaseItem->purchase_price;
                    $lineTotal = round($newQuantity * $unitPrice, 2);

                    if ($oldItem) {
                        $oldItem->update([
                            'quantity' => $newQuantity,
                            'unit_price' => $unitPrice,
                            'total' => $lineTotal,
                        ]);
                    } else {
                        $return->items()->create([
                            'purchase_item_id' => $purchaseItem->id,
                            'product_id' => $purchaseItem->product_id,
                            'product_variant_id' => $purchaseItem->product_variant_id,
                            'quantity' => $newQuantity,
                            'unit_price' => $unitPrice,
                            'total' => $lineTotal,
                        ]);
                    }

                    $totalAmount += $lineTotal;
                    $hasItems = true;
                }

                $this->syncProductStock($purchaseItem->product_id);
            }

            if (!$hasItems) {
                throw ValidationException::withMessages([
                    'items' => 'At least one product must have a return quantity.',
                ]);
            }

            $return->update([
                'return_date' => $data['return_date'],
                'note' => $data['note'] ?? null,
                'total_amount' => $totalAmount,
            ]);
        });

        return redirect()
            ->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return updated successfully.');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {
            $return = PurchaseReturn::whereKey($purchaseReturn->id)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($return->items as $item) {
                $variant = ProductVariant::whereKey(
                    $item->product_variant_id
                )->lockForUpdate()->firstOrFail();

                $variant->increment('stock', $item->quantity);

                $this->syncProductStock($item->product_id);
            }

            $return->delete();
        });

        return redirect()
            ->route('purchase-returns.index')
            ->with('success', 'Purchase return deleted and stock restored.');
    }

    private function getPurchaseItems(
        int $purchaseId,
        ?int $excludeReturnId = null
    ) {
        return PurchaseItem::with(['product', 'variant'])
            ->where('purchase_id', $purchaseId)
            ->get()
            ->map(function ($item) use ($excludeReturnId) {
                $item->already_returned_quantity = $this->returnedQuantity(
                    $item->id,
                    $excludeReturnId
                );

                $item->returnable_quantity = max(
                    0,
                    (float) $item->quantity
                        - $item->already_returned_quantity
                );

                return $item;
            });
    }

    private function returnedQuantity(
        int $purchaseItemId,
        ?int $excludeReturnId = null
    ): float {
        $query = PurchaseReturnItem::where(
            'purchase_item_id',
            $purchaseItemId
        );

        if ($excludeReturnId !== null) {
            $query->where('purchase_return_id', '!=', $excludeReturnId);
        }

        return (float) $query->sum('quantity');
    }

    private function syncProductStock(int $productId): void
    {
        $stock = ProductVariant::where('product_id', $productId)
            ->sum('stock');

        Product::whereKey($productId)->update([
            'stock' => $stock,
        ]);
    }

    private function generateReturnNo(): string
    {
        do {
            $returnNo = 'PR-' . now()->format('YmdHis')
                . '-' . random_int(1000, 9999);
        } while (
            PurchaseReturn::where('return_no', $returnNo)->exists()
        );

        return $returnNo;
    }
}