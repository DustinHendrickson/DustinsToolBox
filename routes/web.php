<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', function () {
    return view('homepage');
});

Route::get('/banner-tool', function () {

    $bannerTool = new \App\Http\Controllers\BannerController();

    $response = $bannerTool->getBanners();
    $response = json_decode($response, true);

    return view('banner-tool')->with($response);
});