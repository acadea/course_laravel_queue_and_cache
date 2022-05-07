<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/playground', function(){

//    CRUD
    Cache::add('abc', 123);

    Cache::add('fruits', ['grape', 'durian']);
//    dump(Cache::get('fruits'));

    $img = \Faker\Factory::create()->image();

    $file = new \Illuminate\Http\UploadedFile($img, 'image');

    $base64 = base64_encode(file_get_contents($file));
    Cache::add('img', $base64);

    $stored = Cache::get('abc', 'default valueee');

//    dump($stored); // 123
//    dump(is_string($stored));

    Cache::add('abc', 'hohoo');
//    dump(Cache::get('abc')); // 123

    // updating
    Cache::put('abc', 'heyaa');
//    dump(Cache::get('abc')); // heyaa

    Cache::put('hey', 'youuuu', now()->addSeconds(1));
//    dump(Cache::get('hey'));
//    sleep(2);
//    dump(Cache::get('hey'));

//    Cache::add('num', 100);
//    Cache::increment('num');
//    dump(Cache::get('num')); // 101
//
//    Cache::decrement('num');
//    dump(Cache::get('num')); // 100

    $result = Cache::remember('hoho', 10, function (){
//        dump('aaa');
        return 'santa claus is never gonna visit you';
    });

//    dump($result);


    Cache::store('file')->add('users', [
        'name' => 'sam'
    ]);

    Cache::forget('hoho');






});