<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
public function index()
{
    $products = Product::with([
        'category',
        // 'brand',
        'unit',
        'variants.color',
        'variants.size',
    ])
        ->latest()
        ->paginate(15);

    return view('products.product-list', compact('products'));
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
        $brands = [];
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
        $product->load(['category', 'brand', 'unit']);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the product.
     */
    public function edit(Product $product)
    {
        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get();

        $brands = Brand::where('status', 1)
            ->orderBy('name')
            ->get();

        $units = Unit::where('status', 1)
            ->orderBy('name')
            ->get();

        return view('products.edit', compact(
            'product',
            'categories',
            'brands',
            'units'
        ));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',

            'sku'            => 'required|string|max:100|unique:products,sku,' . $product->id,

            'barcode'        => 'nullable|string|max:100|unique:products,barcode,' . $product->id,

            'category_id'    => 'required|exists:categories,id',
            'brand_id'       => 'nullable|exists:brands,id',
            'unit_id'        => 'required|exists:units,id',

            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'tax_rate'       => 'nullable|numeric|min:0|max:999.99',

            'opening_stock'  => 'required|numeric|min:0',
            'minimum_stock'  => 'nullable|numeric|min:0',

            'status'         => 'required|boolean',

            'note'           => 'nullable|string',
            'description'    => 'nullable|string',
        ]);

        /*
         * Current stock is NOT changed during normal product edit.
         * Stock should be changed through inventory transactions.
         */
        unset($validated['opening_stock']);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}