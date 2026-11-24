<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourierStatus;
use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '08'.fake()->numberBetween(1, 9).fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'vehicle_type' => fake()->randomElement(VehicleType::cases()),
            'vehicle_plate' => strtoupper(fake()->bothify('L #### ??')),
            'level' => fake()->numberBetween(Courier::MIN_LEVEL, Courier::MAX_LEVEL),
            'status' => CourierStatus::Active,
        ];
    }

    public function level(int $level): static
    {
        return $this->state(fn () => ['level' => $level]);
    }
}
