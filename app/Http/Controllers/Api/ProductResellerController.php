<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductResellerController extends Controller
{
    public function index()
    {
        $products = ProductReseller::all()->map(function ($p) {
            $p->image_url = $p->image ? url('storage/' . $p->image) : null;
            return $p;
        });
        return response()->json($products);
    }

    public function show($id)
    {
        $p = ProductReseller::find($id);
        if (!$p) return response()->json(['message' => 'Not found'], 404);
        $p->image_url = $p->image ? url('storage/' . $p->image) : null;
        return response()->json($p);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('product_resellers', 'public');
            $data['image'] = $path;
        }

        $p = ProductReseller::create($data);
        $p->image_url = $p->image ? url('storage/' . $p->image) : null;

        return response()->json($p, 201);
    }

    public function update(Request $request, $id)
    {
        $p = ProductReseller::find($id);
        if (!$p) return response()->json(['message' => 'Not found'], 404);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($p->image && Storage::disk('public')->exists($p->image)) {
                Storage::disk('public')->delete($p->image);
            }
            $path = $request->file('image')->store('product_resellers', 'public');
            $data['image'] = $path;
        }

        $p->update($data);
        $p->image_url = $p->image ? url('storage/' . $p->image) : null;

        return response()->json($p);
    }

    public function destroy($id)
    {
        $p = ProductReseller::find($id);
        if (!$p) return response()->json(['message' => 'Not found'], 404);

        if ($p->image && Storage::disk('public')->exists($p->image)) {
            Storage::disk('public')->delete($p->image);
        }

        $p->delete();
        return response()->json(['message' => 'deleted']);
    }
}
