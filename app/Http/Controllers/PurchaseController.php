<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Support\AdvancedListFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $filterFields = [
            'purchase_no' => ['label' => 'Purchase No.', 'column' => 'purchases.purchase_no', 'type' => 'text'],
            'purchase_date' => ['label' => 'Date', 'column' => 'purchases.purchase_date', 'type' => 'date'],
            'supplier' => ['label' => 'Supplier', 'relation' => 'supplier', 'column' => 'name', 'type' => 'text'],
            'grand_total' => ['label' => 'Total', 'column' => 'purchases.grand_total', 'type' => 'number'],
            'paid_amount' => ['label' => 'Paid', 'column' => 'purchases.paid_amount', 'type' => 'number'],
            'due_amount' => ['label' => 'Due', 'column' => 'purchases.due_amount', 'type' => 'number'],
            'payment_status' => [
                'label' => 'Payment Status',
                'column' => 'purchases.payment_status',
                'type' => 'select',
                'options' => ['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'],
            ],
        ];
        $filters = AdvancedListFilter::validatedFilters($request, $filterFields);
        $query = Purchase::with('supplier');

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('purchases.purchase_no', 'like', '%'.$search.'%')
                    ->orWhereHas('supplier', fn ($related) => $related->where('name', 'like', '%'.$search.'%'));
            });
        }

        $purchases = AdvancedListFilter::apply($query, $filters, $filterFields)
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('purchases', 'filterFields', 'filters'));
    }

    public function create()
    {
        $suppliers = \App\Models\Customer::suppliers()
                ->where('status', true)
                ->orderBy('name')
                ->get();

        $variants = ProductVariant::with([
            'product',
            'color',
            'size',
        ])
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('product_id')
            ->get();

        return view('purchases.create', compact(
            'suppliers',
            'variants'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],
            'purchase_date' => [
                'required',
                'date',
            ],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'note' => [
                'nullable',
                'string',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_variant_id' => [
                'required',
                'integer',
                'distinct',
                'exists:product_variants,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'items.*.purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $purchase = DB::transaction(function () use ($validated) {

            $subtotal = 0;
            $itemDiscountTotal = 0;
            $itemsData = [];
            $productIds = [];

            foreach ($validated['items'] as $item) {

                $variant = ProductVariant::with('product')
                    ->lockForUpdate()
                    ->findOrFail($item['product_variant_id']);

                if (!$variant->product) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected variant has no associated product.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                $price = (float) $item['purchase_price'];
                $discount = (float) ($item['discount'] ?? 0);

                $gross = round($quantity * $price, 2);

                if ($discount > $gross) {
                    throw ValidationException::withMessages([
                        'items' => 'An item discount cannot exceed its line amount.',
                    ]);
                }

                $lineTotal = round($gross - $discount, 2);

                $subtotal += $gross;
                $itemDiscountTotal += $discount;
                $productIds[] = $variant->product_id;

                $itemsData[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'purchase_price' => $price,
                    'discount' => $discount,
                    'tax_amount' => 0,
                    'total' => $lineTotal,
                ];
            }

            $invoiceDiscount = (float) ($validated['discount'] ?? 0);
            $grandTotal = round(
                $subtotal - $itemDiscountTotal - $invoiceDiscount,
                2
            );

            if ($invoiceDiscount > $subtotal - $itemDiscountTotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Invoice discount cannot exceed the remaining bill amount.',
                ]);
            }

            $paidAmount = (float) $validated['paid_amount'];

            if ($paidAmount > $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount cannot exceed the purchase total.',
                ]);
            }

            $dueAmount = round($grandTotal - $paidAmount, 2);

            $paymentStatus = $dueAmount <= 0
                ? 'paid'
                : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_no' => $this->generatePurchaseNumber(),
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => round($subtotal, 2),
                'discount' => round(
                    $itemDiscountTotal + $invoiceDiscount,
                    2
                ),
                'tax_amount' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $purchase->items()->create($itemData);

                $variant = ProductVariant::whereKey(
                    $itemData['product_variant_id']
                )->lockForUpdate()->firstOrFail();

                $variant->increment('stock', $itemData['quantity']);
            }

            foreach (array_unique($productIds) as $productId) {
                $product = Product::whereKey($productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product->update([
                    'stock' => $product->variants()->sum('stock'),
                ]);
            }

            return $purchase;
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'items.product',
            'items.variant.color',
            'items.variant.size',
        ]);

        return view('purchases.show', compact('purchase'));
    }

    private function generatePurchaseNumber(): string
    {
        do {
            $number = 'PUR-' . now()->format('YmdHis')
                . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (Purchase::where('purchase_no', $number)->exists());

        return $number;
    }



    public function edit(Purchase $purchase)
{
    $purchase->load('items');

    $suppliers = \App\Models\Customer::suppliers()
        ->where('status', true)
        ->orderBy('name')
        ->get();

    $existingVariantIds = $purchase->items
        ->pluck('product_variant_id')
        ->unique()
        ->values()
        ->all();

    $variants = ProductVariant::with([
        'product',
        'color',
        'size',
    ])
        ->where(function ($query) use ($existingVariantIds) {
            $query->whereHas('product', function ($productQuery) {
                $productQuery->where('status', true);
            });

            if (!empty($existingVariantIds)) {
                $query->orWhereIn('id', $existingVariantIds);
            }
        })
        ->orderBy('product_id')
        ->get();

    $itemDiscountTotal = (float) $purchase->items->sum('discount');

    $invoiceDiscount = max(
        0,
        (float) $purchase->discount - $itemDiscountTotal
    );

    $existingItems = $purchase->items->map(function ($item) {
        return [
            'product_variant_id' => (int) $item->product_variant_id,
            'quantity' => (float) $item->quantity,
            'purchase_price' => (float) $item->purchase_price,
            'discount' => (float) ($item->discount ?? 0),
        ];
    })->values();

    return view('purchases.edit', compact(
        'purchase',
        'suppliers',
        'variants',
        'existingItems',
        'invoiceDiscount'
    ));
}


public function update(Request $request, Purchase $purchase)
{
    $validated = $request->validate([
        'supplier_id' => [
            'required',
            'integer',
            'exists:customers,id',
        ],
        'purchase_date' => [
            'required',
            'date',
        ],
        'discount' => [
            'nullable',
            'numeric',
            'min:0',
        ],
        'paid_amount' => [
            'required',
            'numeric',
            'min:0',
        ],
        'note' => [
            'nullable',
            'string',
        ],
        'items' => [
            'required',
            'array',
            'min:1',
        ],
        'items.*.product_variant_id' => [
            'required',
            'integer',
            'distinct',
            'exists:product_variants,id',
        ],
        'items.*.quantity' => [
            'required',
            'numeric',
            'gt:0',
        ],
        'items.*.purchase_price' => [
            'required',
            'numeric',
            'min:0',
        ],
        'items.*.discount' => [
            'nullable',
            'numeric',
            'min:0',
        ],
    ]);

    $purchase = DB::transaction(function () use ($validated, $purchase) {

        $lockedPurchase = Purchase::whereKey($purchase->id)
            ->lockForUpdate()
            ->firstOrFail();

        $supplierExists = \App\Models\Customer::suppliers()
            ->where('status', true)
            ->whereKey($validated['supplier_id'])
            ->exists();

        if (!$supplierExists) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Please select an active supplier.',
            ]);
        }

        $oldItems = $lockedPurchase->items()
            ->lockForUpdate()
            ->get();

        $oldQuantities = [];
        $newQuantities = [];
        $productIds = [];

        foreach ($oldItems as $oldItem) {
            $variantId = (int) $oldItem->product_variant_id;

            $oldQuantities[$variantId] =
                ($oldQuantities[$variantId] ?? 0) +
                (float) $oldItem->quantity;

            $productIds[] = (int) $oldItem->product_id;
        }

        $subtotal = 0;
        $itemDiscountTotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $variantId = (int) $item['product_variant_id'];
            $quantity = (float) $item['quantity'];
            $price = (float) $item['purchase_price'];
            $discount = (float) ($item['discount'] ?? 0);

            $gross = round($quantity * $price, 2);

            if ($discount > $gross) {
                throw ValidationException::withMessages([
                    'items' => 'An item discount cannot exceed its line amount.',
                ]);
            }

            $lineTotal = round($gross - $discount, 2);

            $newQuantities[$variantId] =
                ($newQuantities[$variantId] ?? 0) + $quantity;

            $subtotal += $gross;
            $itemDiscountTotal += $discount;

            $itemsData[] = [
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'purchase_price' => $price,
                'discount' => $discount,
                'tax_amount' => 0,
                'total' => $lineTotal,
            ];
        }

        $invoiceDiscount = (float) ($validated['discount'] ?? 0);

        $remainingTotal = round(
            $subtotal - $itemDiscountTotal,
            2
        );

        if ($invoiceDiscount > $remainingTotal) {
            throw ValidationException::withMessages([
                'discount' => 'Invoice discount cannot exceed the remaining bill amount.',
            ]);
        }

        $grandTotal = round(
            $remainingTotal - $invoiceDiscount,
            2
        );

        $paidAmount = (float) $validated['paid_amount'];

        if ($paidAmount > $grandTotal) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot exceed the purchase total.',
            ]);
        }

        $dueAmount = round($grandTotal - $paidAmount, 2);

        $paymentStatus = $dueAmount <= 0
            ? 'paid'
            : ($paidAmount > 0 ? 'partial' : 'unpaid');

        $variantIds = array_unique(array_merge(
            array_keys($oldQuantities),
            array_keys($newQuantities)
        ));

        sort($variantIds);

        $variantsById = ProductVariant::whereIn('id', $variantIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($variantsById->count() !== count($variantIds)) {
            throw ValidationException::withMessages([
                'items' => 'One or more selected product variants no longer exist.',
            ]);
        }

        /*
         * Calculate the resulting stock after replacing this purchase.
         * This avoids incorrectly rejecting an unchanged purchase merely
         * because some of its stock has already been sold.
         */
        foreach ($variantIds as $variantId) {
            $variant = $variantsById->get($variantId);

            if (!$variant->product) {
                $variant->load('product');
            }

            if (!$variant->product) {
                throw ValidationException::withMessages([
                    'items' => 'A selected variant has no associated product.',
                ]);
            }

            $currentStock = (float) $variant->stock;
            $oldQuantity = (float) ($oldQuantities[$variantId] ?? 0);
            $newQuantity = (float) ($newQuantities[$variantId] ?? 0);

            $resultingStock = round(
                $currentStock - $oldQuantity + $newQuantity,
                4
            );

            if ($resultingStock < 0) {
                throw ValidationException::withMessages([
                    'items' => 'The purchase cannot be updated because the resulting stock would become negative. Check whether some purchased items have already been sold.',
                ]);
            }

            $productIds[] = (int) $variant->product_id;
        }

        $lockedPurchase->update([
            'supplier_id' => $validated['supplier_id'],
            'purchase_date' => $validated['purchase_date'],
            'subtotal' => round($subtotal, 2),
            'discount' => round(
                $itemDiscountTotal + $invoiceDiscount,
                2
            ),
            'tax_amount' => 0,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'note' => $validated['note'] ?? null,
        ]);

        /*
         * Replace the existing purchase items.
         */
        $lockedPurchase->items()->delete();

        foreach ($itemsData as $itemData) {
            $lockedPurchase->items()->create([
                'product_id' => $variantsById
                    ->get($itemData['product_variant_id'])
                    ->product_id,
                'product_variant_id' => $itemData['product_variant_id'],
                'quantity' => $itemData['quantity'],
                'purchase_price' => $itemData['purchase_price'],
                'discount' => $itemData['discount'],
                'tax_amount' => $itemData['tax_amount'],
                'total' => $itemData['total'],
            ]);
        }

        /*
         * Apply the net stock difference for every affected variant.
         */
        foreach ($variantIds as $variantId) {
            $variant = $variantsById->get($variantId);

            $oldQuantity = (float) ($oldQuantities[$variantId] ?? 0);
            $newQuantity = (float) ($newQuantities[$variantId] ?? 0);

            $variant->stock = round(
                (float) $variant->stock - $oldQuantity + $newQuantity,
                4
            );

            $variant->save();
        }

        /*
         * Recalculate stock totals for all affected products.
         */
        foreach (array_unique($productIds) as $productId) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }

        return $lockedPurchase;
    });

    return redirect()
        ->route('purchases.show', $purchase)
        ->with('success', 'Purchase updated successfully.');
}


public function destroy(Purchase $purchase)
{
    DB::transaction(function () use ($purchase) {

        $lockedPurchase = Purchase::whereKey($purchase->id)
            ->lockForUpdate()
            ->firstOrFail();

        $items = $lockedPurchase->items()
            ->lockForUpdate()
            ->get();

        $quantities = [];
        $productIds = [];

        foreach ($items as $item) {
            $variantId = (int) $item->product_variant_id;

            $quantities[$variantId] =
                ($quantities[$variantId] ?? 0) +
                (float) $item->quantity;

            $productIds[] = (int) $item->product_id;
        }

        $variantIds = array_keys($quantities);
        sort($variantIds);

        $variantsById = ProductVariant::whereIn('id', $variantIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($variantsById->count() !== count($variantIds)) {
            throw ValidationException::withMessages([
                'purchase' => 'This purchase cannot be deleted because one or more associated product variants no longer exist.',
            ]);
        }

        foreach ($variantIds as $variantId) {
            $variant = $variantsById->get($variantId);

            $resultingStock = round(
                (float) $variant->stock -
                (float) $quantities[$variantId],
                4
            );

            if ($resultingStock < 0) {
                throw ValidationException::withMessages([
                    'purchase' => 'This purchase cannot be deleted because some of its stock has already been sold or used.',
                ]);
            }

            $productIds[] = (int) $variant->product_id;
        }

        /*
         * Remove the purchase items first, then the purchase record.
         */
        $lockedPurchase->items()->delete();
        $lockedPurchase->delete();

        foreach ($variantIds as $variantId) {
            $variant = $variantsById->get($variantId);

            $variant->stock = round(
                (float) $variant->stock -
                (float) $quantities[$variantId],
                4
            );

            $variant->save();
        }

        foreach (array_unique($productIds) as $productId) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }
    });

    return redirect()
        ->route('purchases.index')
        ->with('success', 'Purchase deleted successfully.');
}
}
