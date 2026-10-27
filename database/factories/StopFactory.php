<?php

namespace Database\Factories;

use App\Enums\StopStatus;
use App\Models\Driver;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Stop> */
class StopFactory extends Factory
{
    public function definition(): array
    {
        $windowStartsAt = fake()->dateTimeBetween('+1 day', '+7 days');

        return [
            'driver_id' => Driver::factory(),
            'address' => fake()->streetAddress(),
            'window_starts_at' => $windowStartsAt,
            'window_ends_at' => (clone $windowStartsAt)->modify('+2 hours'),
            'status' => StopStatus::Queued,
        ];
    }
}
