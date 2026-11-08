<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Shoproom Admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $firstOwner = User::updateOrCreate(['email' => 'owner@example.com'], [
            'name' => 'Rafi Ahmed',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $secondOwner = User::updateOrCreate(['email' => 'maya@example.com'], [
            'name' => 'Maya Rahman',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $firstShop = Shop::updateOrCreate(['slug' => 'green-basket'], [
            'owner_id' => $firstOwner->id,
            'name' => 'Green Basket',
            'location' => 'Dhanmondi, Dhaka',
            'phone' => '+880 1712 345678',
            'is_active' => true,
        ]);
        $secondShop = Shop::updateOrCreate(['slug' => 'little-market'], [
            'owner_id' => $secondOwner->id,
            'name' => 'Little Market',
            'location' => 'Uttara, Dhaka',
            'phone' => '+880 1812 345678',
            'is_active' => true,
        ]);

        $rice = Product::updateOrCreate(['sku' => 'GROC-RICE-01'], [
            'created_by' => $firstOwner->id,
            'name' => 'Miniket rice',
            'category' => 'Groceries',
            'description' => 'Fine, everyday miniket rice.',
        ]);
        $tea = Product::updateOrCreate(['sku' => 'DRINK-TEA-01'], [
            'created_by' => $secondOwner->id,
            'name' => 'Loose black tea',
            'category' => 'Drinks',
            'description' => 'Strong black tea for the family table.',
        ]);
        $water = Product::updateOrCreate(['sku' => 'DRINK-WATER-01'], [
            'created_by' => $admin->id,
            'name' => 'Drinking water, 1 litre',
            'category' => 'Drinks',
            'description' => 'Sealed drinking water bottle.',
        ]);
        Product::updateOrCreate(['sku' => 'HOME-CLEAN-01'], [
            'created_by' => $admin->id,
            'name' => 'Dishwashing liquid',
            'category' => 'Household',
            'description' => 'A mild liquid for everyday dishes.',
        ]);

        $this->list($firstShop, $rice, 82, 'Washed and packed in store.', null);
        $this->list($firstShop, $water, 25, null, null);
        $this->list($secondShop, $rice, 85, 'Fresh stock, 1 kg pack.', null);
        $this->list($secondShop, $tea, 140, 'Loose leaf tea, 250 g.', null);
    }

    private function list(Shop $shop, Product $product, float $price, ?string $description, ?string $thumbnail): void
    {
        $values = [
            'price' => $price,
            'description' => $description,
            'thumbnail_url' => $thumbnail,
            'is_active' => true,
        ];

        if ($shop->products()->where('products.id', $product->id)->exists()) {
            $shop->products()->updateExistingPivot($product->id, $values);
        } else {
            $shop->products()->attach($product->id, $values);
        }
    }
}
