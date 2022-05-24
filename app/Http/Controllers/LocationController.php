<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Jobs\ProcessLocationCsv;
use App\Models\Location;
use App\Services\CsvReader\LocationUploader;
use App\Services\Geolocation\LocationDistance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $locations = Location::query()->get();

        return LocationResource::collection($locations)->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }

    public function distance(Location $locationA, Location $locationB)
    {
        return [
            'from_location' => $locationA->toArray(),
            'to_location' => $locationB->toArray(),
            'absolute_distance' => LocationDistance::absoluteDistance(
                originLon: $locationA->longitude,
                originLat: $locationA->latitude,
                destLon: $locationB->longitude,
                destLat: $locationB->latitude
            ),
            'travel_distance' => LocationDistance::travelDistance(
                originLon: $locationA->longitude,
                originLat: $locationA->latitude,
                destLon: $locationB->longitude,
                destLat: $locationB->latitude
            ),
        ];
    }

    public function calculateDistance(Request $request, Location $centralLocation)
    {

        // clearing the cache items with 'location-distance' tag
//        Cache::tags(['location-distance'])->clear();

        // calculate the distance between central location and each of the location in location_ids
        // loop thru location_ids
        $distances = collect($request->location_ids)->map(function ($locationId) use($centralLocation){

            // for each location calculate the distance from central location
            return Cache::lock("distance:{$centralLocation->id}-{$locationId}")
                ->block(15, function()use($centralLocation, $locationId){
                    $key = "{$centralLocation->id}-$locationId";
                    return Cache::tags(['location-distance'])->rememberForever($key, fn() => $this->distance(
                        $centralLocation,
                        Location::query()->findOrFail($locationId))
                    );
                });


        });

        return new JsonResponse([
            'data' => $distances
        ]);


    }


    public function upload(Request $request)
    {
//        get the file from request
        $file = $request->file('csv');

        $encoded = base64_encode($file->getContent());

        ProcessLocationCsv::dispatch($encoded);

        return new JsonResponse([
            'data' => 'ok'
        ]);



    }
}
