<?php

namespace Database\Factories;

use App\Models\Location;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'point' => new Point($this->faker->latitude, $this->faker->longitude),
        ];
    }
}
