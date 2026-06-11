<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Categories
        $topup = Category::create([
            'name' => 'Top Up Games',
            'slug' => 'top-up-games',
            'icon' => '🎮'
        ]);

        $joki = Category::create([
            'name' => 'Joki MLBB',
            'slug' => 'joki-mlbb',
            'icon' => '⚔️'
        ]);

        $voucher = Category::create([
            'name' => 'Voucher',
            'slug' => 'voucher',
            'icon' => '🎫'
        ]);

        $pulsa = Category::create([
            'name' => 'Pulsa & Data',
            'slug' => 'pulsa-data',
            'icon' => '📱'
        ]);

        // 2. Seed Products
        // Mobile Legends
        $mlbb = Product::create([
            'category_id' => $topup->id,
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'developer' => 'Moonton',
            'placeholder_id' => 'User ID',
            'placeholder_zone' => 'Zone ID',
            'logo' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=400&q=80', // Beautiful gaming illustration
            'description' => 'Top up MLBB Diamonds in seconds! Enter ID, choose amount, and pay.',
            'is_popular' => true,
        ]);

        // Mobile Legends Paket Irit
        $mlbbIrit = Product::create([
            'category_id' => $topup->id,
            'name' => 'MLBB Paket Irit',
            'slug' => 'mlbb-paket-irit',
            'developer' => 'Moonton',
            'placeholder_id' => 'User ID',
            'placeholder_zone' => 'Zone ID',
            'logo' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=400&q=80',
            'description' => 'MLBB Diamonds at discounted bundle rates. Instant delivery.',
            'is_popular' => true,
        ]);

        // PUBG Mobile
        $pubg = Product::create([
            'category_id' => $topup->id,
            'name' => 'PUBG Mobile',
            'slug' => 'pubg-mobile',
            'developer' => 'Tencent Games',
            'placeholder_id' => 'Character ID',
            'logo' => 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=400&q=80',
            'description' => 'Top up PUBG Mobile UC instantly. Enter your Character ID to purchase.',
            'is_popular' => true,
        ]);

        // Free Fire
        $ff = Product::create([
            'category_id' => $topup->id,
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'developer' => 'Garena',
            'placeholder_id' => 'Player ID',
            'logo' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=400&q=80',
            'description' => 'Top up FF Diamonds. Quick checkout with E-wallets and QRIS.',
            'is_popular' => true,
        ]);

        // Joki MLBB Eceran
        $jokiEceran = Product::create([
            'category_id' => $joki->id,
            'name' => 'Joki Rank Eceran',
            'slug' => 'joki-rank-eceran',
            'developer' => 'Maitri Store',
            'placeholder_id' => 'Account Email/ID',
            'placeholder_zone' => 'Password / Request Note',
            'logo' => 'https://images.unsplash.com/photo-1553481187-be93c21490a9?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1553481187-be93c21490a9?w=400&q=80',
            'description' => 'Rank boosting by pro players. Safe and fast star acquisition.',
            'is_popular' => true,
        ]);

        // Joki MLBB Paketan
        $jokiPaket = Product::create([
            'category_id' => $joki->id,
            'name' => 'Joki Rank Paketan',
            'slug' => 'joki-rank-paketan',
            'developer' => 'Maitri Store',
            'placeholder_id' => 'Account Email/ID',
            'placeholder_zone' => 'Password / Request Note',
            'logo' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=400&q=80',
            'description' => 'Full division rank boosting packages. Safe & professional.',
            'is_popular' => true,
        ]);

        // Joki Main Bareng
        $jokiMabar = Product::create([
            'category_id' => $joki->id,
            'name' => 'Joki Main Bareng',
            'slug' => 'joki-main-bareng',
            'developer' => 'Maitri Store',
            'placeholder_id' => 'WhatsApp/Game ID',
            'logo' => 'https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?w=400&q=80',
            'description' => 'Play directly with top-tier players and learn advanced game tactics.',
            'is_popular' => true,
        ]);

        // ROBLOX
        $roblox = Product::create([
            'category_id' => $topup->id,
            'name' => 'ROBLOX',
            'slug' => 'roblox',
            'developer' => 'Roblox Corporation',
            'placeholder_id' => 'Roblox Username',
            'logo' => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?w=400&q=80',
            'description' => 'Buy Robux instantly! Fast and secure delivery directly to your account.',
            'is_popular' => true,
        ]);

        // Honor of Kings
        $hok = Product::create([
            'category_id' => $topup->id,
            'name' => 'Honor of Kings',
            'slug' => 'honor-of-kings',
            'developer' => 'Tencent Games',
            'placeholder_id' => 'Player ID',
            'logo' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=400&q=80',
            'description' => 'Top up HOK Tokens. Secure transactions with various payment gateways.',
            'is_popular' => false,
        ]);

        // Magic Chess
        $magicChess = Product::create([
            'category_id' => $topup->id,
            'name' => 'Magic Chess',
            'slug' => 'magic-chess',
            'developer' => 'Moonton',
            'placeholder_id' => 'User ID',
            'placeholder_zone' => 'Zone ID',
            'logo' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=120&q=80',
            'banner' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=400&q=80',
            'description' => 'Top up Magic Chess coins/diamonds and customize your commander skins.',
            'is_popular' => false,
        ]);

        // 3. Seed Items
        // MLBB Items
        $mlbbItems = [
            ['name' => '86 Diamonds', 'price' => 19500, 'original_price' => 22000],
            ['name' => '172 Diamonds', 'price' => 38000, 'original_price' => 44000],
            ['name' => '257 Diamonds', 'price' => 57000, 'original_price' => 65000],
            ['name' => '706 Diamonds', 'price' => 152000, 'original_price' => 170000],
            ['name' => '1412 Diamonds', 'price' => 300000, 'original_price' => 340000],
        ];
        foreach ($mlbbItems as $index => $itemData) {
            Item::create(array_merge($itemData, [
                'product_id' => $mlbb->id,
                'sku' => 'MLBB-' . ($index + 1) * 50,
            ]));
        }

        // Roblox Items
        $robloxItems = [
            ['name' => '400 Robux', 'price' => 75000, 'original_price' => 85000],
            ['name' => '800 Robux', 'price' => 148000, 'original_price' => 165000],
            ['name' => '1700 Robux', 'price' => 295000, 'original_price' => 320000],
            ['name' => '4500 Robux', 'price' => 745000, 'original_price' => 800000],
        ];
        foreach ($robloxItems as $index => $itemData) {
            Item::create(array_merge($itemData, [
                'product_id' => $roblox->id,
                'sku' => 'RBLX-' . ($index + 1) * 100,
            ]));
        }

        // PUBG Items
        $pubgItems = [
            ['name' => '60 UC', 'price' => 14000, 'original_price' => 16000],
            ['name' => '325 UC', 'price' => 69000, 'original_price' => 75000],
            ['name' => '660 UC', 'price' => 135000, 'original_price' => 150000],
            ['name' => '1800 UC', 'price' => 345000, 'original_price' => 380000],
        ];
        foreach ($pubgItems as $index => $itemData) {
            Item::create(array_merge($itemData, [
                'product_id' => $pubg->id,
                'sku' => 'PUBG-' . ($index + 1) * 60,
            ]));
        }

        // FF Items
        $ffItems = [
            ['name' => '50 Diamonds', 'price' => 8000, 'original_price' => 10000],
            ['name' => '140 Diamonds', 'price' => 20000, 'original_price' => 25000],
            ['name' => '355 Diamonds', 'price' => 48000, 'original_price' => 55000],
            ['name' => '720 Diamonds', 'price' => 95000, 'original_price' => 110000],
        ];
        foreach ($ffItems as $index => $itemData) {
            Item::create(array_merge($itemData, [
                'product_id' => $ff->id,
                'sku' => 'FF-' . ($index + 1) * 50,
            ]));
        }

        // 4. Seed Transactions
        $item1 = Item::where('product_id', $mlbb->id)->first();
        Transaction::create([
            'invoice_id' => 'INV-20260612-0001',
            'product_id' => $mlbb->id,
            'item_id' => $item1->id,
            'user_id_input' => '87654321',
            'zone_id_input' => '1234',
            'whatsapp_number' => '081234567890',
            'payment_method' => 'QRIS',
            'price_paid' => $item1->price,
            'status' => 'completed',
            'created_at' => now()->subMinutes(10),
        ]);

        User::factory()->create([
            'name' => 'Maitri Admin',
            'email' => 'admin@maitri.com',
            'password' => bcrypt('password'),
        ]);
    }
}
