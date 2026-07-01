<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'code' => 'T'.fake()->unique()->numerify('######'),
            'name' => fake()->words(3, true),
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'base_unit' => 'piece',
            'base_unit_label' => 'قطعة',
            'category' => fake()->randomElement(['عام', 'إلكترونيات', 'ملابس']),
            'purchase_price' => fake()->randomFloat(2, 5, 500),
            'selling_price' => fake()->randomFloat(2, 10, 600),
            'is_active' => true,
            'product_type' => 'standard',
            'is_manufactured' => false,
        ];
    }
}
