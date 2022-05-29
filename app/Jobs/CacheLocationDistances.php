<?php

namespace App\Jobs;

use App\Models\Location;
use App\Services\Cache\LocationDistanceCacher;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CacheLocationDistances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private int $start, private int $end)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // get all the location of the target range
        $centralLocations = Location::query()
            ->where('id', '>=', value: $this->start)
            ->where('id', '<', value: $this->end);

//        calculate the location distance for each of the location found
        $centralLocations->each(function (Location $location){
            $otherLocationIds = Location::query()->where('id', '!=', $location->id)
                ->get()
                ->pluck('id')
                ->toArray();

            LocationDistanceCacher::calculateDistances($location, $otherLocationIds);
        });
    }
}
