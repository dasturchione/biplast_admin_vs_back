<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);

        // 🔥 category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $products = $query->paginate(10);

        return ProductResource::collection($products);
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return new ProductResource($product);
    }

    public function similar($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $products = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(3)
            ->get();
        return ProductResource::collection($products);
    }
}
