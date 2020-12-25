<?php

namespace Tests\Feature;

use App\Models\Device;
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
     * @return void
     */
    public function testLocationAgainstApi()
    {
        $device = Device::factory()->create();
        $geofence = Geofence::factory()->create();

        $geofence->update([
            'name' => 'Mount Meru',
            'polygon' => new Polygon([new LineString([
                new Point(30.880349391520674, 79.01949815522256),
                new Point(30.885800246846003, 79.04678019933264),
                new Point(30.863251515535822, 79.05226547804261),
                new Point(30.852470852359556, 79.01617811810864),
                new Point(30.880349391520674, 79.01949815522256),
            ])])
        ]);

        $this->actingAs($device, 'dev')->json('POST', '/api/geolocation', [
            'latitude' => 30.817642272425815,
            'longitude' => 78.99279350887142,
        ]);

        $this->assertDatabaseCount('accesses', 0);


        $this->actingAs($device, 'dev')->json('POST', '/api/geolocation', [
            'latitude' => 30.864862315142844,
            'longitude' => 79.02902521824514,
        ]);

        $this->assertDatabaseCount('accesses', 1);

        $this->assertDatabaseHas('accesses', [
            'in_point_id' => 2,
            'current_point_id' => 2,
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'out_point_id' => null,
            'status' => 'IN',
        ]);

        $this->actingAs($device, 'dev')->json('POST', '/api/geolocation', [
            'latitude' => 30.87118134454234,
            'longitude' => 79.03725313631008,
        ]);

        $this->assertDatabaseHas('accesses', [
            'in_point_id' => 2,
            'current_point_id' => 3,
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'out_point_id' => null,
            'status' => 'STAY',
        ]);

        $this->actingAs($device, 'dev')->json('POST', '/api/geolocation', [
            'latitude' => 30.908467521692828,
            'longitude' => 79.08734239189845,
        ]);

        $this->assertDatabaseHas('accesses', [
            'in_point_id' => 2,
            'current_point_id' => 3,
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'out_point_id' => 4,
            'status' => 'OUT',
        ]);
    }

    /**
     * @return void
     */
    public function testLocationAgainstGeofence()
    {
        $geofence = Geofence::factory()->create();
        $geofence->update([
            'name' => 'Mount Meru',
            'polygon' => new Polygon([new LineString([
                new Point(30.880349391520674, 79.01949815522256),
                new Point(30.885800246846003, 79.04678019933264),
                new Point(30.863251515535822, 79.05226547804261),
                new Point(30.852470852359556, 79.01617811810864),
                new Point(30.880349391520674, 79.01949815522256),
            ])])
        ]);

        // This point should intersect the geofence of the drug dealer
        $intersected_point = Location::factory()->create();
        $intersected_point->update([
            'point' => new Point(30.870066251976972, 79.0324896047988),
        ]);
        $intersected_count = Geofence::intersects('polygon', $intersected_point->point)->count();
        $this->assertEquals(1, $intersected_count);

        // This point shouldn't intersect the geofence of the drug dealer
        $non_intersected_point = Location::factory()->create();
        $non_intersected_point->update([
            'point' => new Point(30.933110542725696, 78.9939483043893),
        ]);
        $non_intersected_count = Geofence::intersects('polygon', $non_intersected_point->point)->count();
        $this->assertEquals(0, $non_intersected_count);
    }
}
