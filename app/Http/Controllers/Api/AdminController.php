<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'shops' => Shop::count(),
            'owners' => User::where('role', 'owner')->count(),
            'products' => Product::count(),
            'listings' => DB::table('shop_product')->where('is_active', true)->count(),
            'recent_shops' => Shop::with('owner:id,name')->latest()->take(5)->get(),
        ]);
    }

    public function shops(): JsonResponse
    {
        return response()->json([
            'shops' => Shop::with('owner:id,name,email')->withCount('products')->latest()->get(),
            'owners' => User::where('role', 'owner')->where('is_active', true)->with('shop:id,owner_id,name')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function storeShop(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:shops,slug'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['owner_id'])) {
            $this->ensureAvailableOwner((int) $data['owner_id']);
        }

        $data['slug'] = $this->shopSlug($data['slug'] ?? $data['name']);
        $shop = Shop::create($data);

        return response()->json(['shop' => $shop->load('owner:id,name,email')], 201);
    }

    public function updateShop(Request $request, Shop $shop): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash', Rule::unique('shops', 'slug')->ignore($shop->id)],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (! empty($data['owner_id'])) {
            $this->ensureAvailableOwner((int) $data['owner_id'], $shop->id);
        }

        $shop->update($data);

        return response()->json(['shop' => $shop->fresh()->load('owner:id,name,email')]);
    }

    public function deleteShop(Shop $shop): JsonResponse
    {
        $shop->delete();

        return response()->json(['message' => 'Shop deleted.']);
    }

    public function owners(): JsonResponse
    {
        return response()->json([
            'owners' => User::where('role', 'owner')->with('shop:id,owner_id,name')->orderBy('name')->get(['id', 'name', 'email', 'is_active', 'created_at']),
            'shops' => Shop::orderBy('name')->get(['id', 'name', 'owner_id']),
        ]);
    }

    public function storeOwner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
        ]);

        if (! empty($data['shop_id'])) {
            $this->ensureAvailableShop((int) $data['shop_id']);
        }

        $owner = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'owner',
        ]);

        if (! empty($data['shop_id'])) {
            Shop::whereKey($data['shop_id'])->update(['owner_id' => $owner->id]);
        }

        return response()->json(['owner' => $owner->load('shop')], 201);
    }

    public function updateOwner(Request $request, User $owner): JsonResponse
    {
        abort_unless($owner->role === 'owner', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($owner->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (! empty($data['shop_id'])) {
            $this->ensureAvailableShop((int) $data['shop_id'], $owner->id);
        }

        $owner->fill(['name' => $data['name'], 'email' => $data['email'], 'is_active' => $data['is_active']]);
        if (! empty($data['password'])) {
            $owner->password = Hash::make($data['password']);
        }
        $owner->save();

        Shop::where('owner_id', $owner->id)->update(['owner_id' => null]);
        if (! empty($data['shop_id'])) {
            Shop::whereKey($data['shop_id'])->update(['owner_id' => $owner->id]);
        }

        return response()->json(['owner' => $owner->fresh()->load('shop')]);
    }

    public function deleteOwner(Request $request, User $owner): JsonResponse
    {
        abort_unless($owner->role === 'owner', 404);
        abort_if($owner->is($request->user()), 422, 'You cannot delete your own account.');
        $owner->delete();

        return response()->json(['message' => 'Owner account deleted.']);
    }

    public function products(): JsonResponse
    {
        return response()->json([
            'products' => Product::with('creator:id,name')->withCount('shops')->latest()->get(),
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $product = Product::create([...$data, 'created_by' => $request->user()->id]);

        return response()->json(['product' => $product->load('creator:id,name')], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $product->update($data);

        return response()->json(['product' => $product->fresh()->load('creator:id,name')]);
    }

    public function deleteProduct(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted from the shared catalog.']);
    }

    public function shopListings(Shop $shop): JsonResponse
    {
        return response()->json([
            'shop' => $shop->load('owner:id,name'),
            'listings' => $shop->products()->with('creator:id,name')->orderBy('products.name')->get(),
            'catalog' => Product::orderBy('name')->get(['id', 'name', 'sku', 'category']),
        ]);
    }

    public function addShopListing(Request $request, Shop $shop): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        abort_if($shop->products()->whereKey($data['product_id'])->exists(), 422, 'That product is already listed in this shop.');
        $shop->products()->attach($data['product_id'], $this->listingValues($data));

        return response()->json(['message' => 'Product added to shop.'], 201);
    }

    public function updateShopListing(Request $request, Shop $shop, Product $product): JsonResponse
    {
        abort_unless($shop->products()->whereKey($product->id)->exists(), 404);
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);
        $shop->products()->updateExistingPivot($product->id, $this->listingValues($data));

        return response()->json(['message' => 'Shop listing updated.']);
    }

    public function removeShopListing(Shop $shop, Product $product): JsonResponse
    {
        $shop->products()->detach($product->id);

        return response()->json(['message' => 'Product removed from shop.']);
    }

    private function listingValues(array $data): array
    {
        return [
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }

    private function ensureAvailableOwner(int $ownerId, ?int $shopId = null): void
    {
        $owner = User::whereKey($ownerId)->where('role', 'owner')->where('is_active', true)->first();
        abort_unless($owner, 422, 'Choose an active shop owner.');
        abort_if(Shop::where('owner_id', $ownerId)->when($shopId, fn ($query) => $query->where('id', '!=', $shopId))->exists(), 422, 'That owner is already assigned to another shop.');
    }

    private function ensureAvailableShop(int $shopId, ?int $ownerId = null): void
    {
        abort_if(Shop::whereKey($shopId)->whereNotNull('owner_id')->when($ownerId, fn ($query) => $query->where('owner_id', '!=', $ownerId))->exists(), 422, 'That shop already has an owner.');
    }

    private function shopSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'shop';
        $slug = $base;
        $number = 2;
        while (Shop::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }
}
