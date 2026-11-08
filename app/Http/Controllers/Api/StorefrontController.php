<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class StorefrontController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $shop = Shop::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $shop->products()
            ->wherePivot('is_active', true)
            ->orderBy('products.name')
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category,
                'price' => $product->pivot->price,
                'description' => $product->pivot->description ?: $product->description,
                'thumbnail_url' => $product->pivot->thumbnail_url,
            ]);

        return response()->json([
            'shop' => $shop->only(['name', 'slug', 'location', 'phone']),
            'products' => $products,
        ]);
    }
}
