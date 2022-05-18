<?php

namespace App\Services\Geolocation;

use Illuminate\Support\Facades\Http;

class LocationDistance
{
    public static function absoluteDistance($originLon, $originLat, $destLon, $destLat)
    {
        // https://stackoverflow.com/questions/14750275/haversine-formula-with-php

        // convert from degrees to radians
        $latA = deg2rad($originLat);
        $longA = deg2rad($originLon);
        $latB = deg2rad($destLat);
        $longB = deg2rad($destLon);

        $latDelta = $latB - $latA;
        $lonDelta = $longB - $longA;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latA) * cos($latB) * pow(sin($lonDelta / 2), 2)));

        $earthRadius = 6371000;

        return $angle * $earthRadius;
    }

    public static function travelDistance($originLon, $originLat, $destLon, $destLat)
    {

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'destinations' => $destLat . ',' . $destLon,
            'origins' => $originLat . ',' . $originLon,
            'key' => config('services.google.key')
        ]);

        $json = $response->json();

        return data_get($json, 'rows.0.elements.0.distance.value', 'NA');

    }
}