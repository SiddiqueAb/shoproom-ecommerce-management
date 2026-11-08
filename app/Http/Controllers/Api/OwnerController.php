<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $shop = $this->shopFor($request);

        return response()->json([
            'shop' => $shop,
            'listings' => $shop->products()->count(),
            'active_listings' => $shop->products()->wherePivot('is_active', true)->count(),
            'catalog_products' => Product::count(),
            'recent_listings' => $shop->products()->latest('shop_product.updated_at')->take(5)->get(),
        ]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;
        $listedIds = $shop?->products()->pluck('products.id')->all() ?? [];

        return response()->json([
            'shop' => $shop,
            'products' => Product::with('creator:id,name')->orderBy('name')->get()->map(function (Product $product) use ($listedIds) {
                return [...$product->toArray(), 'already_listed' => in_array($product->id, $listedIds)];
            }),
        ]);
    }

    public function listings(Request $request): JsonResponse
    {
        $shop = $this->shopFor($request);

        return response()->json([
            'shop' => $shop,
            'listings' => $shop->products()->with('creator:id,name')->orderBy('products.name')->get(),
        ]);
    }

    public function addListing(Request $request): JsonResponse
    {
        $shop = $this->shopFor($request);
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        abort_if($shop->products()->where('products.id', $data['product_id'])->exists(), 422, 'That product is already in your shop.');
        $shop->products()->attach($data['product_id'], [
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Product added to your shop.'], 201);
    }

    public function createProduct(Request $request): JsonResponse
    {
        $shop = $this->shopFor($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'category' => ['nullable', 'string', 'max:120'],
            'catalog_description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $product = Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'category' => $data['category'] ?? null,
            'description' => $data['catalog_description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $shop->products()->attach($product->id, [
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['product' => $product, 'message' => 'Product added to the shared catalog and your shop.'], 201);
    }

    public function updateListing(Request $request, Product $product): JsonResponse
    {
        $shop = $this->shopFor($request);
        abort_unless($shop->products()->where('products.id', $product->id)->exists(), 404);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        $shop->products()->updateExistingPivot($product->id, $data);

        return response()->json(['message' => 'Shop listing updated.']);
    }

    public function removeListing(Request $request, Product $product): JsonResponse
    {
        $shop = $this->shopFor($request);
        abort_unless($shop->products()->where('products.id', $product->id)->exists(), 404);
        $shop->products()->detach($product->id);

        return response()->json(['message' => 'Product removed from your shop.']);
    }

    private function shopFor(Request $request): Shop
    {
        $shop = $request->user()->shop;
        abort_unless($shop, 422, 'Your account is not assigned to a shop yet. Ask an administrator to assign one.');

        return $shop;
    }
}
