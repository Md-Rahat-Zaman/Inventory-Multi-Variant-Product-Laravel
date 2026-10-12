<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->paginate(10);

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $unit = Unit::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            // 'status' => $request->status,
        ]);

            // AJAX request হলে JSON response
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully.',
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
            ],
        ]);
    }

    // Unit page থেকে normal submit
    return redirect()
        ->route('units.index')
        ->with('success', 'Unit created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        //
    }
}
