<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index()
    {
        return response()->json(
            Category::all()->map(fn ($cat) => $this->mapCategory($cat))
        );
    }

    /**
     * GET /api/categories/{slug}
     */
    public function show($slug)
    {
        $cat = Category::where('slug', $slug)->first();

        if (!$cat) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($this->mapCategory($cat));
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image'       => 'nullable|image|max:5120',
        ]);

        // auto slug
        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('category', 'public');
        }

        $cat = Category::create($data);

        return response()->json(
            $this->mapCategory($cat),
            201
        );
    }

    /**
     * PUT /api/categories/{id}
     */
    public function update(Request $request, $id)
    {
        $cat = Category::find($id);

        if (!$cat) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'image'       => 'nullable|image|max:5120',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($cat->image && Storage::disk('public')->exists($cat->image)) {
                Storage::disk('public')->delete($cat->image);
            }

            $data['image'] = $request->file('image')
                ->store('category', 'public');
        }

        $cat->update($data);

        return response()->json($this->mapCategory($cat));
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function destroy($id)
    {
        $cat = Category::find($id);

        if (!$cat) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($cat->image && Storage::disk('public')->exists($cat->image)) {
            Storage::disk('public')->delete($cat->image);
        }

        $cat->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    /**
     * 🔧 Helper mapper
     */
    private function mapCategory(Category $cat): array
    {
        return [
            'id'          => (string) $cat->id,
            'name'        => $cat->name,
            'description' => $cat->name,
            'slug'        => $cat->slug,
            'image'       => $cat->image,
            'image_url'   => $cat->image
                ? url('storage/' . $cat->image)
                : null,
        ];
    }
}
