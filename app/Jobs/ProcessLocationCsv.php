<?php

namespace App\Jobs;

use App\Services\CsvReader\LocationUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $csvBase64)
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
