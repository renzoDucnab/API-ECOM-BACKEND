<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'name' => 'Macbook Pro',
                'description' => 'Apple Macbook Pro 16-inch',
                'price' => 2399.99,
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
            ],
            [
                'name' => 'Iphone 17 Pro',
                'description' => 'Latest Apple Phone',
                'price' => 999.99,
                'stock' => 100,
                'image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
            ],
            [
                'name' => 'Samsung TV',
                'description' => '55-inch 4k Smart TV',
                'price' => 699.99,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
            ],
        ];

        foreach($products as $product){
            Product::create($product);
        }
    }
}
