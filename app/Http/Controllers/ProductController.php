<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductVariant;
use App\Support\AdvancedListFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
public function index(Request $request)
{
    $filterFields = [
        'name' => ['label' => 'Product Name', 'column' => 'products.name', 'type' => 'text'],
        'sku' => ['label' => 'SKU', 'column' => 'products.sku', 'type' => 'text'],
        'barcode' => ['label' => 'Barcode', 'column' => 'products.barcode', 'type' => 'text'],
        'category' => ['label' => 'Category', 'relation' => 'category', 'column' => 'name', 'type' => 'text'],
        'brand' => ['label' => 'Brand', 'relation' => 'brand', 'column' => 'name', 'type' => 'text'],
        'purchase_price' => ['label' => 'Purchase Price', 'column' => 'products.purchase_price', 'type' => 'number'],
        'sale_price' => ['label' => 'Sale Price', 'column' => 'products.sale_price', 'type' => 'number'],
        'stock' => ['label' => 'Stock', 'column' => 'products.stock', 'type' => 'number'],
        'minimum_stock' => ['label' => 'Minimum Stock', 'column' => 'products.minimum_stock', 'type' => 'number'],
        'status' => [
            'label' => 'Status',
            'column' => 'products.status',
            'type' => 'select',
            'options' => ['1' => 'Active', '0' => 'Inactive'],
        ],
    ];
    $filters = AdvancedListFilter::validatedFilters($request, $filterFields);
    $query = Product::with([
        'category',
        'brand',
        'unit',
        'variants.color',
        'variants.size',
    ]);

    if ($filters['search'] !== '') {
        $search = $filters['search'];
        $query->where(function ($query) use ($search) {
            $query->where('products.name', 'like', '%'.$search.'%')
                ->orWhere('products.sku', 'like', '%'.$search.'%')
                ->orWhere('products.barcode', 'like', '%'.$search.'%')
                ->orWhereHas('category', fn ($related) => $related->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('brand', fn ($related) => $related->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('variants', fn ($related) => $related->where('sku', 'like', '%'.$search.'%'));
        });
    }

    $products = AdvancedListFilter::apply($query, $filters, $filterFields)
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('products.product-list', compact('products', 'filterFields', 'filters'));
}

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get();

        // আপাতত empty
        $brands = Brand::where('status', 1)
            ->orderBy('name')
            ->get();
        $units = Unit::orderBy('name')
            ->get();

        // Colors
        $colors = Color::where('status', 1)
            ->orderBy('name')
            ->get();

        // Sizes
        $sizes = Size::where('status', 1)
            ->orderBy('name')
            ->get();

        return view('products.create', compact(
            'categories',
            'brands',
            'units',
            'colors',
            'sizes'
        ));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name'           => 'required|string|max:150',
        'category_id'    => 'required|exists:categories,id',
        'brand_id'       => 'nullable|exists:brands,id',
        'unit_id'        => 'required|exists:units,id',

        'purchase_price' => 'required|numeric|min:0',
        'sale_price'     => 'required|numeric|min:0',
        'tax_rate'       => 'nullable|numeric|min:0|max:999.99',

        'status'         => 'required|boolean',
        'note'           => 'nullable|string',
        'description'    => 'nullable|string',

        'colors'         => 'required|array|min:1',
        'colors.*'       => 'required|integer|distinct|exists:colors,id',

        'sizes'          => 'required|array|min:1',
        'sizes.*'        => 'required|integer|distinct|exists:sizes,id',

        'variants'                   => 'required|array|min:1',
        'variants.*.color_id'        => 'required|integer|exists:colors,id',
        'variants.*.size_id'         => 'required|integer|exists:sizes,id',
        'variants.*.sku'             => 'nullable|string|max:100|distinct|unique:product_variants,sku',
        'variants.*.barcode'         => 'nullable|string|max:100|distinct|unique:product_variants,barcode',
        'variants.*.purchase_price'  => 'required|numeric|min:0',
        'variants.*.sale_price'      => 'required|numeric|min:0',
        'variants.*.opening_stock'   => 'required|numeric|min:0',
    ]);

    $variantColors = collect($validated['variants'])
        ->pluck('color_id')
        ->unique()
        ->sort()
        ->values();

    $variantSizes = collect($validated['variants'])
        ->pluck('size_id')
        ->unique()
        ->sort()
        ->values();

    if (
        $variantColors->diff(collect($validated['colors']))->isNotEmpty() ||
        $variantSizes->diff(collect($validated['sizes']))->isNotEmpty()
    ) {
        return back()
            ->withInput()
            ->withErrors([
                'variants' => 'Generated variants do not match the selected colors and sizes.',
            ]);
    }

    // Prevent duplicate color-size combinations.
    $combinations = collect($validated['variants'])->map(
        fn ($variant) => $variant['color_id'] . '-' . $variant['size_id']
    );

    if ($combinations->unique()->count() !== $combinations->count()) {
        return back()
            ->withInput()
            ->withErrors([
                'variants' => 'Duplicate color and size combinations are not allowed.',
            ]);
    }

    DB::transaction(function () use ($validated) {

        $totalStock = collect($validated['variants'])
            ->sum('opening_stock');

        $product = Product::create([
            'name'           => $validated['name'],
            'sku'            => 'PRD-' . strtoupper(Str::random(12)),
            'barcode'        => null,
            'category_id'    => $validated['category_id'],
            'brand_id'       => $validated['brand_id'] ?? null,
            'unit_id'        => $validated['unit_id'],
            'purchase_price' => $validated['purchase_price'],
            'sale_price'     => $validated['sale_price'],
            'tax_rate'       => $validated['tax_rate'] ?? 0,
            'opening_stock'  => $totalStock,
            'stock'          => $totalStock,
            'minimum_stock'  => 0,
            'status'         => $validated['status'],
            'note'           => $validated['note'] ?? null,
            'description'    => $validated['description'] ?? null,
        ]);

        foreach ($validated['variants'] as $variant) {

            ProductVariant::create([
                'product_id'     => $product->id,
                'color_id'       => $variant['color_id'],
                'size_id'        => $variant['size_id'],
                'sku'            => $variant['sku'] ?: (
                    'VAR-' . strtoupper(Str::random(12))
                ),
                'barcode'        => $variant['barcode'] ?: null,
                'purchase_price' => $variant['purchase_price'],
                'sale_price'     => $variant['sale_price'],
                'stock'          => $variant['opening_stock'],
            ]);
        }
    });

    return redirect()
        ->route('products.index')
        ->with('success', 'Product and variants created successfully.');
}

    /**
     * Display the specified product.
     */
public function show(Product $product)
{
    $product->load([
        'category',
        'unit',
        'variants.color',
        'variants.size',
    ]);

    return view('products.view', compact('product'));
}

    /**
     * Show the form for editing the product.
     */
 
public function edit(Product $product)
{
    $categories = Category::where('status', 1)
        ->orderBy('name')
        ->get();

    // $brands = Brand::where('status', 1)
    //     ->orderBy('name')
    //     ->get();

    $units = Unit::orderBy('name')
        ->get();

    $colors = Color::where('status', 1)
        ->orderBy('name')
        ->get();

    $sizes = Size::where('status', 1)
        ->orderBy('name')
        ->get();

    $product = Product::with([
        'variants.color',
        'variants.size'
    ])->findOrFail($product->id);

    return view('products.create', compact(
        'product',
        'categories',
        'units',
        'colors',
        'sizes'
    ));
}


    /**
     * Update the specified product.
     */


public function update(Request $request, Product $product)
{
    // 1. Basic validation rules
    $rules = [
        'name'           => 'required|string|max:150',
        'category_id'    => 'required|exists:categories,id',
        'brand_id'       => 'nullable|exists:brands,id',
        'unit_id'        => 'required|exists:units,id',

        'purchase_price' => 'required|numeric|min:0',
        'sale_price'     => 'required|numeric|min:0',
        'tax_rate'       => 'nullable|numeric|min:0|max:999.99',

        'status'         => 'required|boolean',
        'note'           => 'nullable|string',
        'description'    => 'nullable|string',

        'colors'         => 'required|array|min:1',
        'colors.*'       => 'required|integer|distinct|exists:colors,id',

        'sizes'          => 'required|array|min:1',
        'sizes.*'        => 'required|integer|distinct|exists:sizes,id',

        'variants'                    => 'required|array|min:1',
        'variants.*.id'                => [
            'nullable',
            'integer',
            Rule::exists('product_variants', 'id')
                ->where('product_id', $product->id),
        ],
        'variants.*.color_id'          => 'required|integer|exists:colors,id',
        'variants.*.size_id'           => 'required|integer|exists:sizes,id',
        'variants.*.sku'               => 'nullable|string|max:100|distinct',
        'variants.*.barcode'           => 'nullable|string|max:100|distinct',
        'variants.*.purchase_price'    => 'required|numeric|min:0',
        'variants.*.sale_price'        => 'required|numeric|min:0',
        'variants.*.opening_stock'     => 'required|numeric|min:0',
    ];

    // 2. Validate SKU and barcode uniqueness.
    // Existing variant নিজের SKU/barcode রাখতে পারবে।
    foreach ($request->input('variants', []) as $index => $variant) {

        $variantId = $variant['id'] ?? null;

        $existingVariant = $variantId
            ? $product->variants()
                ->where('id', $variantId)
                ->first()
            : null;

        $skuRule = Rule::unique('product_variants', 'sku');

        $barcodeRule = Rule::unique('product_variants', 'barcode');

        if ($existingVariant) {
            $skuRule->ignore($existingVariant->id);
            $barcodeRule->ignore($existingVariant->id);
        }

        $rules["variants.$index.sku"][] = $skuRule;
        $rules["variants.$index.barcode"][] = $barcodeRule;
    }

    // 3. Validate request
    $validated = $request->validate($rules);

    // 4. Check that submitted variants match selected colors and sizes
    $selectedColors = collect($validated['colors'])
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->sort()
        ->values();

    $selectedSizes = collect($validated['sizes'])
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->sort()
        ->values();

    $variantColors = collect($validated['variants'])
        ->pluck('color_id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->sort()
        ->values();

    $variantSizes = collect($validated['variants'])
        ->pluck('size_id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->sort()
        ->values();

    if (
        $variantColors->diff($selectedColors)->isNotEmpty() ||
        $variantSizes->diff($selectedSizes)->isNotEmpty()
    ) {
        return back()
            ->withInput()
            ->withErrors([
                'variants' => 'Generated variants do not match the selected colors and sizes.',
            ]);
    }

    // 5. Prevent duplicate color-size combinations
    $combinations = collect($validated['variants'])->map(
        fn ($variant) =>
            $variant['color_id'] . '-' . $variant['size_id']
    );

    if ($combinations->unique()->count() !== $combinations->count()) {
        return back()
            ->withInput()
            ->withErrors([
                'variants' => 'Duplicate color and size combinations are not allowed.',
            ]);
    }

    // 6. Update product and variants in a transaction
    DB::transaction(function () use ($validated, $product) {

        $product->update([
            'name'           => $validated['name'],
            'category_id'    => $validated['category_id'],
            'brand_id'       => $validated['brand_id'] ?? null,
            'unit_id'        => $validated['unit_id'],
            'purchase_price' => $validated['purchase_price'],
            'sale_price'     => $validated['sale_price'],
            'tax_rate'       => $validated['tax_rate'] ?? 0,
            'status'         => $validated['status'],
            'note'           => $validated['note'] ?? null,
            'description'    => $validated['description'] ?? null,
        ]);

        foreach ($validated['variants'] as $variant) {

            $variantData = [
                'color_id'       => $variant['color_id'],
                'size_id'        => $variant['size_id'],
                'sku'            => $variant['sku'] ?: (
                    'VAR-' . strtoupper(Str::random(12))
                ),
                'barcode'        => $variant['barcode'] ?: null,
                'purchase_price' => $variant['purchase_price'],
                'sale_price'     => $variant['sale_price'],
                'stock'          => $variant['opening_stock'],
            ];

            if (!empty($variant['id'])) {

                // Update an existing variant belonging to this product.
                $existingVariant = $product->variants()
                    ->where('id', $variant['id'])
                    ->firstOrFail();

                $existingVariant->update($variantData);

            } else {

                // Create a new variant.
                $product->variants()->create($variantData);
            }
        }

        // Recalculate total stock from all variants.
        $totalStock = $product->variants()->sum('stock');

        $product->update([
            'stock' => $totalStock,
        ]);
    });

    return redirect()
        ->route('products.index')
        ->with('success', 'Product and variants updated successfully.');
}

    /**
     * Remove the specified product.
     */
public function destroy(Product $product)
{
    DB::transaction(function () use ($product) {
        $product->variants()->delete();
        $product->delete();
    });

    return redirect()
        ->route('products.index')
        ->with('success', 'Product deleted successfully.');
}
}