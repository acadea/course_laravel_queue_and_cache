<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Jobs\CacheLocationDistances;
use App\Jobs\ProcessLocationCsv;
use App\Models\Location;
use App\Services\Cache\LocationDistanceCacher;
use App\Services\CsvReader\LocationUploader;
use App\Services\Geolocation\LocationDistance;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
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



    public function calculateDistance(Request $request, Location $centralLocation)
    {

        // clearing the cache items with 'location-distance' tag
//        Cache::tags(['location-distance'])->clear();

        // calculate the distance between central location and each of the location in location_ids
        // loop thru location_ids
        $distances = LocationDistanceCacher::calculateDistances($centralLocation, $request->location_ids);

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

        Bus::chain([
            new ProcessLocationCsv($encoded),

            function(){
                // one off operation
                // divide cache location job into batches of 2
                $totalLocations = Location::query()->count();

                $chunkSize = 2;
                $chunks = $totalLocations / $chunkSize;

                $batches = collect(range(0, $chunks))
                    ->map(fn ($index) => new CacheLocationDistances($index * $chunkSize, $index * $chunkSize + $chunkSize))
                    ->toArray();

                Bus::batch($batches)
//                    ->then(function(){
//
//                    })
//                    ->catch(function(){
//
//                    })
                    ->name('cache location distance')
//                    ->onConnection()
//                    ->onQueue()
                    ->dispatch();


            }
        ])->dispatch();

        return new JsonResponse([
            'data' => 'ok'
        ]);



    }
}
