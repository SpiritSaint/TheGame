<?php

namespace App\Repositories;

use App\Models\Access;
use App\Models\Device;
use App\Models\Geofence;
use App\Models\Location;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class GeolocationRepository
{
    /**
     * @param Device $device
     * @param Location $location
     * @return Builder|Model|object|null
     */
    public function get_previous_location_of_device(Device $device, Location $location)
    {
        return Location::query()
            ->where('device_id', $device->id)
            ->where('id', '<', $location->id)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param Device $device
     * @param Location $location
     * @param $previous_location
     */
    public function check_outgoing_geofences(Device $device, Location $location, $previous_location)
    {
        if (!is_null($previous_location)) {
            $accesses = $this->retrieve_accesses_using_location($device, $previous_location);
            if ($accesses->count() > 0) {
                foreach ($accesses as $access) {
                    if (Geofence::intersects('polygon', $location->point)
                        ->where('id', $access->geofence_id)->count() == 0) {
                        $access->update([
                            'status' => 'OUT',
                            'out_point_id' => $location->id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param Device $device
     * @param Location $location
     * @param $previous_location
     */
    public function check_intersected_geofences(Device $device, Location $location, $previous_location)
    {
        $intersected_geofences = Geofence::intersects('polygon', $location->point)->get();
        foreach($intersected_geofences as $intersected_geofence) {
            $this->create_or_update_access($device, $location, $intersected_geofence, $previous_location);
        }
    }

    /**
     * @param Device $device
     * @param Location $location
     * @param Geofence $geofence
     * @param $previous_location
     */
    public function create_or_update_access(Device $device, Location $location, Geofence $geofence, $previous_location)
    {
        if (!is_null($previous_location)) {
            // Retrieve last accesses for that device and geofence
            // considering the last location
            $accesses = $this->retrieve_accesses_of_geofence_using_location($device, $geofence, $previous_location);
            if ($accesses->count() > 0) {
                // Device was in a geofence
                // Should update the access with the stay status
                Access::whereIn('id', $accesses->map(function($access) { return $access->id; }))->update([
                    'current_point_id' => $location->id,
                    'status' => 'STAY'
                ]);
            } else {
                // Device with history was introduced in the geofence
                // Should be create a new access for that access
                $this->create_access($device, $geofence, $location);
            }
        } else {
            // Device hasn't last point
            // Should be create a new access for that geofence
            $this->create_access($device, $geofence, $location);
        }
    }

    /**
     * @param Device $device
     * @param Geofence $geofence
     * @param Location $location
     * @return Builder[]|Collection
     */
    public function retrieve_accesses_of_geofence_using_location(Device $device, Geofence $geofence, Location $location)
    {
        return Access::query()->where([
            'device_id' => $device->id,
            'geofence_id' => $geofence->id,
            'current_point_id' => $location->id,
        ])->get();
    }

    /**
     * @param Device $device
     * @param Location $location
     * @return Builder[]|Collection
     */
    public function retrieve_accesses_using_location(Device $device, Location $location)
    {
        return Access::query()
            ->where([
                'device_id' => $device->id,
                'current_point_id' => $location->id,
            ])->get();
    }

    /**
     * @param Device $device
     * @param Geofence $geofence
     * @param Location $location
     * @return mixed
     */
    public function create_access(Device $device, Geofence $geofence, Location $location)
    {
        return Access::create([
            'geofence_id' => $geofence->id,
            'device_id' => $device->id,
            'in_point_id' => $location->id,
            'current_point_id' => $location->id,
            'status' => 'IN'
        ]);
    }

    /**
     * @param Device $device
     * @param $data
     * @return mixed
     */
    public function create_location(Device $device, $data)
    {
        // Device should create the new location
        return Location::create([
            'device_id' => $device->id,
            'point' => new Point($data['latitude'], $data['longitude']),
        ]);
    }
}
