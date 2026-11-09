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

        $this->list($firstShop, $rice, 82, 'Washed and packed in store.', $this->image('photo-1516684732162-798a0062be99'));
        $this->list($firstShop, $water, 25, null, $this->image('photo-1602143407151-7111542de6e8'));
        $this->list($secondShop, $rice, 85, 'Fresh stock, 1 kg pack.', null);
        $this->list($secondShop, $tea, 140, 'Loose leaf tea, 250 g.', null);

        $newProducts = [
            ['sku' => 'GB-PROD-TOM-01', 'name' => 'Fresh red tomatoes', 'category' => 'Vegetables', 'catalog' => 'Ripe tomatoes selected for everyday cooking.', 'details' => 'Fresh, firm tomatoes from the morning market.', 'price' => 48, 'image' => 'photo-1546094096-0df4bcaaa337'],
            ['sku' => 'GB-PROD-BAN-01', 'name' => 'Sweet bananas, 1 kg', 'category' => 'Fruits', 'catalog' => 'Naturally sweet bananas, sold by the kilogram.', 'details' => 'A fresh bunch, ready to enjoy.', 'price' => 72, 'image' => 'photo-1571771894821-ce9b6c11b08e'],
            ['sku' => 'GB-PROD-APL-01', 'name' => 'Crisp red apples, 1 kg', 'category' => 'Fruits', 'catalog' => 'Crisp red apples for snacks and lunch boxes.', 'details' => 'Washed and packed in a 1 kg bag.', 'price' => 260, 'image' => 'photo-1560806887-1e4cd0b6cbd6'],
            ['sku' => 'GB-PROD-ORG-01', 'name' => 'Juicy oranges, 1 kg', 'category' => 'Fruits', 'catalog' => 'Bright, juicy oranges selected for freshness.', 'details' => 'A good choice for fresh juice or a snack.', 'price' => 180, 'image' => 'photo-1547514701-42782101795e'],
            ['sku' => 'GB-PROD-VEG-01', 'name' => 'Seasonal vegetables basket', 'category' => 'Vegetables', 'catalog' => 'A changing mix of fresh seasonal vegetables.', 'details' => 'Market-fresh vegetables packed together.', 'price' => 220, 'image' => 'photo-1542838132-92c53300491e'],
            ['sku' => 'GB-PROD-BRD-01', 'name' => 'Whole wheat bread', 'category' => 'Bakery', 'catalog' => 'Soft whole wheat bread for breakfast and sandwiches.', 'details' => 'Freshly baked loaf, 400 g.', 'price' => 95, 'image' => 'photo-1509440159596-0249088772ff'],
            ['sku' => 'GB-PROD-EGG-01', 'name' => 'Farm eggs, 12 pieces', 'category' => 'Dairy & eggs', 'catalog' => 'A tray of farm eggs for daily meals.', 'details' => 'Carefully packed, 12 eggs per tray.', 'price' => 165, 'image' => 'photo-1518569656558-1f25e69d93d7'],
            ['sku' => 'GB-PROD-MLK-01', 'name' => 'Full cream milk, 1 litre', 'category' => 'Dairy & eggs', 'catalog' => 'Full cream milk for tea, coffee, and breakfast.', 'details' => 'Keep refrigerated after opening.', 'price' => 105, 'image' => 'photo-1550583724-b2692b85b150'],
            ['sku' => 'GB-PROD-YOG-01', 'name' => 'Plain yogurt, 500 g', 'category' => 'Dairy & eggs', 'catalog' => 'Plain yogurt with a smooth, creamy texture.', 'details' => 'Serve chilled or use in cooking.', 'price' => 120, 'image' => 'photo-1488477181946-6428a0291777'],
            ['sku' => 'GB-PROD-HNY-01', 'name' => 'Wildflower honey, 250 g', 'category' => 'Pantry', 'catalog' => 'A jar of golden wildflower honey.', 'details' => 'Packed in a handy 250 g jar.', 'price' => 210, 'image' => 'photo-1587049352851-8d4e89133924'],
            ['sku' => 'GB-PROD-COF-01', 'name' => 'Ground coffee, 200 g', 'category' => 'Drinks', 'catalog' => 'Aromatic ground coffee for your daily cup.', 'details' => 'Store in a cool, dry place after opening.', 'price' => 320, 'image' => 'photo-1559056199-641a0ac8b55e'],
            ['sku' => 'GB-PROD-TEA-02', 'name' => 'Loose black tea, 250 g', 'category' => 'Drinks', 'catalog' => 'A strong black tea for the family table.', 'details' => 'Packed fresh in a 250 g pouch.', 'price' => 155, 'image' => 'photo-1564890369478-c89ca6d9cde9'],
            ['sku' => 'GB-PROD-PAS-01', 'name' => 'Penne pasta, 500 g', 'category' => 'Pantry', 'catalog' => 'Penne pasta for quick family meals.', 'details' => '500 g pack. Ready in minutes.', 'price' => 135, 'image' => 'photo-1546549032-9571cd6b27df'],
            ['sku' => 'GB-PROD-POT-01', 'name' => 'Potatoes, 1 kg', 'category' => 'Vegetables', 'catalog' => 'Everyday potatoes for curries and sides.', 'details' => 'Sorted and packed in a 1 kg bag.', 'price' => 55, 'image' => 'photo-1518977676601-b53f82aba655'],
            ['sku' => 'GB-PROD-SPN-01', 'name' => 'Baby spinach, 250 g', 'category' => 'Vegetables', 'catalog' => 'Tender spinach leaves for salads and cooking.', 'details' => 'Rinse before use and keep refrigerated.', 'price' => 85, 'image' => 'photo-1512621776951-a57141f2eefd'],
            ['sku' => 'GB-PROD-SAL-01', 'name' => 'Garden salad mix, 200 g', 'category' => 'Vegetables', 'catalog' => 'A fresh mix of leafy greens for quick salads.', 'details' => 'Wash before serving.', 'price' => 110, 'image' => 'photo-1540420773420-3366772f4999'],
            ['sku' => 'GB-PROD-CHS-01', 'name' => 'Cheddar cheese, 200 g', 'category' => 'Dairy & eggs', 'catalog' => 'Mild cheddar cheese for sandwiches and snacks.', 'details' => 'Keep chilled. 200 g pack.', 'price' => 285, 'image' => 'photo-1486297678162-eb2a19b0a32d'],
            ['sku' => 'GB-PROD-OIL-01', 'name' => 'Extra virgin olive oil, 500 ml', 'category' => 'Pantry', 'catalog' => 'Extra virgin olive oil for dressings and cooking.', 'details' => 'Store away from direct sunlight.', 'price' => 590, 'image' => 'photo-1474979266404-7eaacbcd87c5'],
            ['sku' => 'GB-PROD-FSH-01', 'name' => 'Fresh fish fillet, 500 g', 'category' => 'Seafood', 'catalog' => 'Fresh fish fillet prepared for home cooking.', 'details' => 'Keep chilled and cook thoroughly.', 'price' => 340, 'image' => 'photo-1510130387422-82bed34b37e9'],
            ['sku' => 'GB-PROD-SOP-01', 'name' => 'Gentle hand soap, 100 g', 'category' => 'Household', 'catalog' => 'A mild hand soap bar for everyday use.', 'details' => 'Single 100 g bar.', 'price' => 65, 'image' => 'photo-1600857544200-b2f666a9a2ec'],
        ];

        foreach ($newProducts as $item) {
            $product = Product::updateOrCreate(['sku' => $item['sku']], [
                'created_by' => $admin->id,
                'name' => $item['name'],
                'category' => $item['category'],
                'description' => $item['catalog'],
            ]);

            $this->list($firstShop, $product, $item['price'], $item['details'], $this->image($item['image']));
        }
    }

    private function image(string $photoId): string
    {
        return "https://images.unsplash.com/{$photoId}?auto=format&fit=crop&w=900&q=80";
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
