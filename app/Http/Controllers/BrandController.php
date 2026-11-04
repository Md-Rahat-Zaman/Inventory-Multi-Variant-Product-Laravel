<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreBrandRequest;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        
        return view('brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        //
        $brand = Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
        ]);

            // AJAX request হলে JSON response
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }

    // Brand page থেকে normal submit
    return redirect()
        ->route('brands.index')
        ->with('success', 'Brand created successfully.');

    }

    public function update(Request $request, Brand $brand)
    {
        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status,
        ]);
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }
        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
        return view('brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        //
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
