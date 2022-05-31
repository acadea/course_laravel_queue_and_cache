<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class JobServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        RateLimiter::for('upload-csv', function($job){
            return Limit::perMinute(5);
        });

        Queue::before(function(JobProcessing $event){
            $connectionName = $event->connectionName;

            $job = $event->job;

            $payload = $job->payload();

            dump('before', $payload);
        });

        Queue::after(function (JobProcessed $event){
            $connectionName = $event->connectionName;

            $job = $event->job;

            $payload = $job->payload();
            dump('after', $payload);
        });

        Queue::looping(function (){
            // ..

            dump('looping');
        });
    }
}
