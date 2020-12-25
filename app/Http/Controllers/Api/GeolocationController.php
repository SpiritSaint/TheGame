<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GeolocationRequest;
use App\Repositories\GeolocationRepository;
use Illuminate\Http\JsonResponse;

class GeolocationController extends Controller
{
    protected $geolocationRepository;

    public function __construct(GeolocationRepository $geolocationRepository)
    {
        $this->geolocationRepository = $geolocationRepository;
    }

    /**
     * @param GeolocationRequest $request
     * @return JsonResponse
     */
    public function __invoke(GeolocationRequest $request)
    {
        $location = $this->geolocationRepository->create_location(
            $request->user(), $request->only('latitude', 'longitude')
        );
        $last_location = $this->geolocationRepository->get_previous_location_of_device($request->user(), $location);
        $this->geolocationRepository->check_intersected_geofences($request->user(), $location, $last_location);
        $this->geolocationRepository->check_outgoing_geofences($request->user(), $location, $last_location);
        return response()->json([], 200);
    }
}
