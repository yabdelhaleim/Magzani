<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => 'WH-'.fake()->unique()->numerify('####'),
            'status' => 'active',
            'is_active' => true,
        ];
    }
}
