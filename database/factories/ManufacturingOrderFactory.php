<?php

namespace Database\Factories;

use App\Models\ManufacturingOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturingOrder>
 */
class ManufacturingOrderFactory extends Factory
{
    protected $model = ManufacturingOrder::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 10, 200);
        $costPerUnit = fake()->randomFloat(2, 20, 150);
        $sellPerUnit = $costPerUnit + fake()->randomFloat(2, 5, 50);

        return [
            'order_number' => 'MO-T-'.fake()->unique()->numerify('########'),
            'product_name' => fake()->words(3, true),
            'product_id' => null,
            'quantity_produced' => $qty,
            'cost_per_unit' => $costPerUnit,
            'total_cost' => round($qty * $costPerUnit, 4),
            'selling_price_per_unit' => $sellPerUnit,
            'status' => 'draft',
            'warehouse_id' => null,
        ];
    }
}
