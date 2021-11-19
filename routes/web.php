<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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

Route::view('/', 'auth.login');

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', [HomeController::class,'dashboard'])->name('dashboard');

Route::get('test',function(){
    // $csvFileName = "countries.csv";
    // $csvFile = public_path('uploads/' . $csvFileName);
    // $file_handle = fopen($csvFile, 'r');
    // while (!feof($file_handle)) {
    //     $line_of_text[] = fgetcsv($file_handle, 0, ',');
    // }
    // fclose($file_handle);
    // foreach($line_of_text as $line){
    //    $country = new App\Domain\Settings\Models\Country;
    //    $country->code = $line[1];
    //    $country->name = $line[2];
    //    $country->nationality = $line[3];
    //    $country->save();
    // }
    //return App\Domain\Settings\Models\Country::all();

    $csvFileName = "regions.csv";
    $csvFile = public_path('uploads/' . $csvFileName);
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 0, ',');
    }
    fclose($file_handle);
    foreach($line_of_text as $line){
       $country = new App\Domain\Settings\Models\Region;
       $country->country_id = 1;
       $country->name = $line[1];
       $country->save();
    }
    //return App\Domain\Settings\Models\Region::all();

    $csvFileName = "districts.csv";
    $csvFile = public_path('uploads/' . $csvFileName);
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 0, ',');
    }
    fclose($file_handle);
    foreach($line_of_text as $line){
       $country = new App\Domain\Settings\Models\District;
       $country->region_id = 1;
       $country->name = $line[1];
       $country->save();
    }
    //return App\Domain\Settings\Models\District::all();

    $csvFileName = "wards.csv";
    $csvFile = public_path('uploads/' . $csvFileName);
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 0, ',');
    }
    fclose($file_handle);
    foreach($line_of_text as $line){
       $country = new App\Domain\Settings\Models\Ward;
       $country->district_id = $line[2];
       $country->name = $line[1];
       $country->save();
    }
    return App\Domain\Settings\Models\Ward::all();
});
