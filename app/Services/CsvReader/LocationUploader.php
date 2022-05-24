<?php

namespace App\Services\CsvReader;

use App\Exceptions\UnknownExtensionError;
use App\Models\Location;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class LocationUploader
{

    private $csv;

    public function __construct(UploadedFile $file)
    {
        if($extension = $file->getMimeType() !== 'text/csv'){
            throw new UnknownExtensionError($extension);
        }

//        read the csv file as array
        $this->csv = Reader::createFromFileObject($file->openFile());

        $this->csv->setHeaderOffset(0);

    }

    public function upload()
    {
        Location::query()->truncate();
        //        go thru each csv record
        $records = $this->csv->getRecords([
            'id',
            'created_at',
            'updated_at',
            'name',
            'longitude',
            'latitude',
            'address',
            'postcode',
            'country'
        ]);

        DB::transaction(function () use ($records) {

            collect($records)->each(function($record){

                Location::query()->create([
                    'name' => data_get($record, 'name'),
                    'longitude' => data_get($record, 'longitude'),
                    'latitude' => data_get($record, 'latitude'),
                    'address' => data_get($record, 'address'),
                    'postcode' => data_get($record, 'postcode'),
                    'country' => data_get($record, 'country'),
                ]);

            });

            //        create in db
        });

        return $this;

    }
}