<?php

namespace Database\Factories;

use App\Models\Geofence;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeofenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Geofence::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $first_point = new Point($this->faker->latitude, $this->faker->longitude);
        return [
            'name' => $this->faker->text(77),
            'polygon' => new Polygon([
                new LineString([
                    $first_point,
                    new Point($this->faker->latitude, $this->faker->longitude),
                    new Point($this->faker->latitude, $this->faker->longitude),
                    $first_point
                ])
            ]),
        ];
    }
}
