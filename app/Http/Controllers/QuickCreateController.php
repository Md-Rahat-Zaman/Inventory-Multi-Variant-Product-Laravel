<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickCreateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Str;

class QuickCreateController extends Controller
{
    public function store(QuickCreateRequest $request)
    {
        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->boolean('status'),
        ];

        $model = match ($request->type) {
            'category' => Category::create($data + ['description' => $request->description]),
            'brand' => Brand::create($data),
            'unit' => Unit::create($data),
        };

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->type).' created successfully.',
            'data' => [
                'id' => $model->id,
                'name' => $model->name,
            ],
            $request->type => [
                'id' => $model->id,
                'name' => $model->name,
            ],
        ]);
    }
}
