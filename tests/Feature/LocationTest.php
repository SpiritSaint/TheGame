<?php

namespace Tests\Feature;

use App\Models\Geofence;
use App\Models\Location;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLocationAgainstGeofence()
    {
        $geofence = Geofence::factory()->create();
        $geofence->update([
            'name' => 'Corrupt ex-police doing drug dealing and gun shooting at night',
            'polygon' => new Polygon([new LineString([
                new Point(-33.50583050835206, -70.79457905215473),
                new Point(-33.50580590678955, -70.79446841103041),
                new Point(-33.50588642096814, -70.79444225949193),
                new Point(-33.5059121407594, -70.79454686564584),
                new Point(-33.50583050835206, -70.79457905215473),
            ])])
        ]);

        // This point should intersect the geofence of the drug dealer
        $intersected_point = Location::factory()->create();
        $intersected_point->update([
            'point' => new Point(-33.50586565217031, -70.79451124284385),
        ]);
        $intersected_count = Geofence::intersects('polygon', $intersected_point->point)->count();
        $this->assertEquals(1, $intersected_count);

        // This point shouldn't intersect the geofence of the drug dealer
        $non_intersected_point = Location::factory()->create();
        $non_intersected_point->update([
            'point' => new Point(-33.50625614218783, -70.79425349732479),
        ]);
        $non_intersected_count = Geofence::intersects('polygon', $non_intersected_point->point)->count();
        $this->assertEquals(0, $non_intersected_count);
    }
}
