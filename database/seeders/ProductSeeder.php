<?php

namespace Database\Seeders;

use App\Models\FeaturedProduct;
use App\Models\ProductSize;
use App\Services\ProductService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = FeaturedProduct::first();
        if ($product) {
            $product->update(ProductService::DEFAULT_PRODUCT);
        } else {
            FeaturedProduct::create(ProductService::DEFAULT_PRODUCT);
        }

        foreach (ProductService::DEFAULT_SIZES as $size) {
            ProductSize::firstOrCreate(
                ['label' => $size['label']],
                $size
            );
        }
    }
}
