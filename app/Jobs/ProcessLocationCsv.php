<?php

namespace App\Jobs;

use App\Jobs\Middleware\SetEnvironment;
use App\Services\CsvReader\LocationUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;

class ProcessLocationCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // number of times the job may be attempted
    public $tries = 5;

    // max num of unhandled exception to allow before failing
    public $maxExceptions = 3;

    // job is considered as failed on timeout
    public $failOnTimeout = true;
    // num of sec the job can run before timing out
    public $timeout = 10;

    // num of sec to wait before reattempting the job
    public $backoff = 3;

    public $deleteWhenMissingModels = true;

//    public function backoff()
//    {
//        return [2,3,4];
//    }


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $csvBase64)
    {
        //
    }

    public function middleware()
    {
        return [
            new SetEnvironment('testing'),
            new RateLimitedWithRedis('upload-csv')
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $csvContent = base64_decode($this->csvBase64);

        $path = sys_get_temp_dir() . '/php' . substr(sha1(rand()), 0, 6);
        $bytes = file_put_contents($path, $csvContent);

        if($bytes === false){
            throw new \Exception('Cannot create temp path for csv');
        }

        $file = new UploadedFile($path, 'uploaded_locations.csv', 'text/csv');

        $uploader = new LocationUploader($file);
        $uploader->upload();
    }
}
