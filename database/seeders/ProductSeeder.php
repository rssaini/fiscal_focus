<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => '10 MM', 'code' => '10MM', 'description' => '10 MM Stone Aggregate'],
            ['name' => 'DUST', 'code' => 'DUST', 'description' => 'Stone Dust'],
            ['name' => '20MM', 'code' => '20MM', 'description' => '20 MM Stone Aggregate'],
            ['name' => 'Stone Boulder', 'code' => 'BOULDER', 'description' => 'Stone Boulder'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
