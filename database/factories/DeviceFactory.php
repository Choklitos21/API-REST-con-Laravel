<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'serial_number' => strtoupper(fake()->bothify('SN-#####')),
            'name' => fake()->word(),
            'type' => fake()->randomElement(['laptop', 'printer', 'tablet']),
            'status' => fake()->randomElement(['available', 'assigned']),
            'assigned_user_id' => null,
        ];
    }
}
