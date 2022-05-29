<?php

namespace App\Services\Cache;

use App\Models\Location;
use App\Services\Geolocation\LocationDistance;
use Illuminate\Support\Facades\Cache;

class LocationDistanceCacher
{

    public static function distance(Location $locationA, Location $locationB)
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
    public static function calculateDistances(Location $centralLocation, array $locationIds)
    {
        // calculate the distance between central location and each of the location in location_ids
        // loop thru location_ids
        return collect($locationIds)->map(function ($locationId) use($centralLocation){

            // for each location calculate the distance from central location
            return Cache::lock("distance:{$centralLocation->id}-{$locationId}")
                ->block(15, function()use($centralLocation, $locationId){
                    $key = "{$centralLocation->id}-$locationId";
                    return Cache::tags(['location-distance'])->rememberForever($key, fn() => self::distance(
                        $centralLocation,
                        Location::query()->findOrFail($locationId))
                    );
                });
        });
    }
}