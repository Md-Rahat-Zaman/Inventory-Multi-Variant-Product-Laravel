<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Support\AdvancedListFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class SaleController extends Controller
{
    public function index(Request $request)
    {
        $filterFields = [
            'sale_no' => ['label' => 'Invoice', 'column' => 'sales.sale_no', 'type' => 'text'],
            'sale_date' => ['label' => 'Date', 'column' => 'sales.sale_date', 'type' => 'date'],
            'customer_name' => ['label' => 'Customer', 'column' => 'sales.customer_name', 'type' => 'text'],
            'customer_phone' => ['label' => 'Phone', 'column' => 'sales.customer_phone', 'type' => 'text'],
            'grand_total' => ['label' => 'Total', 'column' => 'sales.grand_total', 'type' => 'number'],
            'paid_amount' => ['label' => 'Paid', 'column' => 'sales.paid_amount', 'type' => 'number'],
            'due_amount' => ['label' => 'Due', 'column' => 'sales.due_amount', 'type' => 'number'],
            'payment_status' => [
                'label' => 'Payment Status',
                'column' => 'sales.payment_status',
                'type' => 'select',
                'options' => ['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'],
            ],
        ];
        $filters = AdvancedListFilter::validatedFilters($request, $filterFields);
        $query = Sale::with('customer');

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('sales.sale_no', 'like', '%'.$search.'%')
                    ->orWhere('sales.customer_name', 'like', '%'.$search.'%')
                    ->orWhere('sales.customer_phone', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', fn ($related) => $related->where('name', 'like', '%'.$search.'%'));
            });
        }

        $sales = AdvancedListFilter::apply($query, $filters, $filterFields)
            ->latest('sale_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales', 'filterFields', 'filters'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)
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
            ->where('stock', '>', 0)
            ->orderBy('product_id')
            ->get();

        return view('sales.create', compact(
            'customers',
            'variants'
        ));
    }
    public function pos()
    {
        // dd('bismiAllah');
        $customers = Customer::customers()
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
            ->where('stock', '>', 0)
            ->orderBy('product_id')
            ->get();

        return view('sales.pos', compact(
            'customers',
            'variants'
        ));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],
            'customer_name' => [
                'nullable',
                'string',
                'max:150',
            ],
            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'sale_date' => [
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
            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $sale = DB::transaction(function () use ($validated) {

            $customer = null;

            if (!empty($validated['customer_id'])) {
                $customer = Customer::where('status', true)
                    ->find($validated['customer_id']);

                if (!$customer) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'The selected customer is inactive or unavailable.',
                    ]);
                }
            }

            $items = $validated['items'];

            // Lock variants in a consistent order to reduce deadlock risk.
            $variantIds = collect($items)
                ->pluck('product_variant_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $lockedVariants = [];

            foreach ($variantIds as $variantId) {
                $variant = ProductVariant::whereKey($variantId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $variant->load('product');

                if (!$variant->product || !$variant->product->status) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected products is inactive or unavailable.',
                    ]);
                }

                $lockedVariants[$variantId] = $variant;
            }

            $subtotal = 0;
            $itemDiscountTotal = 0;
            $saleItems = [];
            $productIds = [];

            foreach ($items as $item) {
                $variantId = (int) $item['product_variant_id'];
                $variant = $lockedVariants[$variantId];

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $variant->sale_price;
                $itemDiscount = (float) ($item['discount'] ?? 0);

                $availableStock = (float) $variant->stock;

                if ($quantity > $availableStock) {
                    $productName = $variant->product->name;

                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$productName}. Available stock: {$availableStock}.",
                    ]);
                }

                $lineGross = round($quantity * $unitPrice, 2);

                if ($itemDiscount > $lineGross) {
                    throw ValidationException::withMessages([
                        'items' => 'An item discount cannot exceed its line amount.',
                    ]);
                }

                $lineTotal = round($lineGross - $itemDiscount, 2);

                $subtotal += $lineGross;
                $itemDiscountTotal += $itemDiscount;
                $productIds[] = (int) $variant->product_id;

                $saleItems[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $itemDiscount,
                    'total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $itemDiscountTotal = round($itemDiscountTotal, 2);
            $invoiceDiscount = (float) ($validated['discount'] ?? 0);

            if ($invoiceDiscount > ($subtotal - $itemDiscountTotal)) {
                throw ValidationException::withMessages([
                    'discount' => 'Invoice discount cannot exceed the remaining sale amount.',
                ]);
            }

            $totalDiscount = round(
                $itemDiscountTotal + $invoiceDiscount,
                2
            );

            $grandTotal = round($subtotal - $totalDiscount, 2);
            $paidAmount = round((float) $validated['paid_amount'], 2);

            if ($paidAmount > $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount cannot exceed the sale total.',
                ]);
            }

            $dueAmount = round($grandTotal - $paidAmount, 2);

            $paymentStatus = $dueAmount <= 0
                ? 'paid'
                : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name
                    ?? ($validated['customer_name'] ?? null),
                'customer_phone' => $customer?->phone
                    ?? ($validated['customer_phone'] ?? null),
                'sale_no' => $this->generateSaleNumber(),
                'sale_date' => $validated['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($saleItems as $saleItem) {
                $sale->items()->create($saleItem);

                $variant = $lockedVariants[
                    (int) $saleItem['product_variant_id']
                ];

                $variant->decrement('stock', $saleItem['quantity']);
            }

            // Synchronize product stock with the sum of its variant stocks.
            $productIds = array_unique($productIds);
            sort($productIds);

            foreach ($productIds as $productId) {
                $product = Product::whereKey($productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product->update([
                    'stock' => $product->variants()->sum('stock'),
                ]);
            }

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale completed successfully.');
    }





    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
            'items.variant.color',
            'items.variant.size',
        ]);

        return view('sales.show', compact('sale'));
    }

    private function generateSaleNumber(): string
    {
        do {
            $saleNo = 'SAL-' . now()->format('YmdHis')
                . '-' . strtoupper(Str::random(6));
        } while (Sale::where('sale_no', $saleNo)->exists());

        return $saleNo;
    }

    public function edit(Sale $sale)
{
    $sale->load('items');

    $customers = Customer::customers()
        ->where('status', true)
        ->orderBy('name')
        ->get();

    $variants = ProductVariant::with([
        'product',
        'color',
        'size',
    ])
        ->orderBy('id')
        ->get();

    $itemDiscountTotal = (float) $sale->items->sum('discount');

    // sales.discount stores item discounts + invoice discount.
    $invoiceDiscount = max(
        0,
        (float) $sale->discount - $itemDiscountTotal
    );

    $existingItems = $sale->items->map(function ($item) {
        return [
            'product_variant_id' => (int) $item->product_variant_id,
            'quantity' => (float) $item->quantity,
            'discount' => (float) ($item->discount ?? 0),
        ];
    })->values();

    return view('sales.edit', compact(
        'sale',
        'customers',
        'variants',
        'existingItems',
        'invoiceDiscount'
    ));
}


public function update(Request $request, Sale $sale)
{
    $validated = $request->validate([
        'customer_id' => [
            'nullable',
            'integer',
            'exists:customers,id',
        ],
        'customer_name' => [
            'nullable',
            'string',
            'max:150',
        ],
        'customer_phone' => [
            'nullable',
            'string',
            'max:30',
        ],
        'sale_date' => [
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
        'items.*.discount' => [
            'nullable',
            'numeric',
            'min:0',
        ],
    ]);

    DB::transaction(function () use ($validated, $sale) {

        // Lock the sale to avoid concurrent modifications.
        $sale = Sale::whereKey($sale->id)
            ->lockForUpdate()
            ->firstOrFail();

        $customer = null;

        if (!empty($validated['customer_id'])) {
            $customer = Customer::where('status', true)
                ->find($validated['customer_id']);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer_id' => 'The selected customer is inactive or unavailable.',
                ]);
            }
        }

        // Existing sale items before replacement.
        $oldItems = $sale->items()->get();

        $oldQuantities = $oldItems
            ->groupBy('product_variant_id')
            ->map(function ($items) {
                return (float) $items->sum('quantity');
            });

        $oldProductIds = $oldItems
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Lock all old and newly selected variants in a consistent order.
        $variantIds = collect($validated['items'])
            ->pluck('product_variant_id')
            ->merge($oldItems->pluck('product_variant_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        $lockedVariants = [];

        foreach ($variantIds as $variantId) {
            $variant = ProductVariant::whereKey($variantId)
                ->lockForUpdate()
                ->firstOrFail();

            $variant->load('product');

            $lockedVariants[$variantId] = $variant;
        }

        $subtotal = 0;
        $itemDiscountTotal = 0;
        $saleItems = [];
        $newProductIds = [];

        foreach ($validated['items'] as $item) {
            $variantId = (int) $item['product_variant_id'];
            $variant = $lockedVariants[$variantId];

            if (!$variant->product || !$variant->product->status) {
                throw ValidationException::withMessages([
                    'items' => 'One of the selected products is inactive or unavailable.',
                ]);
            }

            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $variant->sale_price;
            $itemDiscount = (float) ($item['discount'] ?? 0);

            /*
             * Current stock + this sale's previous quantity.
             * The previous quantity can be sold again during this edit.
             */
            $availableStock = (float) $variant->stock
                + (float) ($oldQuantities[$variantId] ?? 0);

            if ($quantity > $availableStock) {
                $productName = $variant->product->name;

                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$productName}. Available stock: {$availableStock}.",
                ]);
            }

            $lineGross = round($quantity * $unitPrice, 2);

            if ($itemDiscount > $lineGross) {
                throw ValidationException::withMessages([
                    'items' => 'An item discount cannot exceed its line amount.',
                ]);
            }

            $lineTotal = round($lineGross - $itemDiscount, 2);

            $subtotal += $lineGross;
            $itemDiscountTotal += $itemDiscount;

            $newProductIds[] = (int) $variant->product_id;

            $saleItems[] = [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $itemDiscount,
                'total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $itemDiscountTotal = round($itemDiscountTotal, 2);
        $invoiceDiscount = round((float) ($validated['discount'] ?? 0), 2);

        if ($invoiceDiscount > ($subtotal - $itemDiscountTotal)) {
            throw ValidationException::withMessages([
                'discount' => 'Invoice discount cannot exceed the remaining sale amount.',
            ]);
        }

        $totalDiscount = round(
            $itemDiscountTotal + $invoiceDiscount,
            2
        );

        $grandTotal = round($subtotal - $totalDiscount, 2);
        $paidAmount = round((float) $validated['paid_amount'], 2);

        if ($paidAmount > $grandTotal) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot exceed the sale total.',
            ]);
        }

        $dueAmount = round($grandTotal - $paidAmount, 2);

        $paymentStatus = $dueAmount <= 0
            ? 'paid'
            : ($paidAmount > 0 ? 'partial' : 'unpaid');

        /*
         * Recalculate each affected variant stock:
         * current stock + old sold quantity - new sold quantity.
         */
        $newQuantities = collect($validated['items'])
            ->groupBy('product_variant_id')
            ->map(function ($items) {
                return (float) collect($items)->sum('quantity');
            });

        foreach ($variantIds as $variantId) {
            $variant = $lockedVariants[$variantId];

            $currentStock = (float) $variant->stock;
            $oldQuantity = (float) ($oldQuantities[$variantId] ?? 0);
            $newQuantity = (float) ($newQuantities[$variantId] ?? 0);

            $newStock = $currentStock + $oldQuantity - $newQuantity;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Insufficient stock to update this sale.',
                ]);
            }

            $variant->update([
                'stock' => $newStock,
            ]);
        }

        // Update the sale header.
        $sale->update([
            'customer_id' => $customer?->id,
            'customer_name' => $customer?->name
                ?? ($validated['customer_name'] ?? null),
            'customer_phone' => $customer?->phone
                ?? ($validated['customer_phone'] ?? null),
            'sale_date' => $validated['sale_date'],
            'subtotal' => $subtotal,
            'discount' => $totalDiscount,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'note' => $validated['note'] ?? null,
        ]);

        // Replace the old items with the updated items.
        $sale->items()->delete();

        foreach ($saleItems as $saleItem) {
            $sale->items()->create($saleItem);
        }

        // Synchronize stock for every affected product.
        $productIds = array_values(array_unique(array_merge(
            $oldProductIds,
            $newProductIds
        )));

        sort($productIds);

        foreach ($productIds as $productId) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }
    });

    return redirect()
        ->route('sales.show', $sale)
        ->with('success', 'Sale updated successfully.');
}


public function destroy(Sale $sale)
{
    DB::transaction(function () use ($sale) {

        $sale = Sale::whereKey($sale->id)
            ->lockForUpdate()
            ->firstOrFail();

        $items = $sale->items()->get();

        $variantQuantities = $items
            ->groupBy('product_variant_id')
            ->map(function ($group) {
                return (float) $group->sum('quantity');
            });

        $productIds = $items
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $variantIds = $variantQuantities->keys()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        // Lock variants before restoring stock.
        foreach ($variantIds as $variantId) {
            $variant = ProductVariant::whereKey($variantId)
                ->lockForUpdate()
                ->firstOrFail();

            $quantityToRestore = (float) $variantQuantities[$variantId];

            $variant->increment('stock', $quantityToRestore);
        }

        // Remove sale items and the sale.
        $sale->items()->delete();
        $sale->delete();

        // Synchronize affected product stock.
        sort($productIds);

        foreach ($productIds as $productId) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }
    });

    return redirect()
        ->route('sales.index')
        ->with('success', 'Sale deleted and stock restored successfully.');
}
}
