<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Sample products for testing
        Product::create([
            'product_name' => 'Laptop',
            'quantity_in_stock' => 15,
            'price_per_item' => 899.99,
        ]);

        Product::create([
            'product_name' => 'Mouse',
            'quantity_in_stock' => 50,
            'price_per_item' => 29.99,
        ]);

        Product::create([
            'product_name' => 'Keyboard',
            'quantity_in_stock' => 30,
            'price_per_item' => 79.99,
        ]);

        Product::create([
            'product_name' => 'Monitor',
            'quantity_in_stock' => 20,
            'price_per_item' => 299.99,
        ]);

        Product::create([
            'product_name' => 'Webcam',
            'quantity_in_stock' => 25,
            'price_per_item' => 89.99,
        ]);
    }
}