<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NTALevelController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SystemModuleController;
use App\Http\Controllers\GPAClassificationController;
use App\Http\Controllers\SpecialDateController;
use App\Http\Controllers\FacultyController;

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

Route::middleware(['auth:sanctum', 'verified'])->group(function(){

	Route::get('nta-levels', [NTALevelController::class,'index'])->name('nta-levels');
	Route::post('nta-level/store', [NTALevelController::class,'store']);
	Route::post('nta-level/update', [NTALevelController::class,'update']);
	Route::get('nta-level/{id}/destroy', [NTALevelController::class,'destroy']);

	Route::get('currencies', [CurrencyController::class,'index'])->name('currencies');
	Route::post('currency/store', [CurrencyController::class,'store']);
	Route::post('currency/update', [CurrencyController::class,'update']);
	Route::get('currency/{id}/destroy', [CurrencyController::class,'destroy']);


	Route::get('levels', [LevelController::class,'index'])->name('levels');
	Route::post('level/store', [LevelController::class,'store']);
	Route::post('level/update', [LevelController::class,'update']);
	Route::get('level/{id}/destroy', [LevelController::class,'destroy']);


	Route::get('intakes', [IntakeController::class,'index'])->name('levels');
	Route::post('intake/store', [IntakeController::class,'store']);
	Route::post('intake/update', [IntakeController::class,'update']);
	Route::get('intake/{id}/destroy', [IntakeController::class,'destroy']);
	
	Route::get('gpa-classifications', [GPAClassificationController::class,'index']);
	Route::post('gpa-classification/store', [GPAClassificationController::class,'store']);
	Route::post('gpa-classification/update', [GPAClassificationController::class,'update']);
	Route::get('gpa-classification/{id}/destroy', [GPAClassificationController::class,'destroy']);


	Route::get('countries', [CountryController::class,'index'])->name('countries');
	Route::post('country/store', [CountryController::class,'store']);
	Route::post('country/update', [CountryController::class,'update']);
	Route::get('country/{id}/destroy', [CountryController::class,'destroy']);


	Route::get('districts', [DistrictController::class,'index'])->name('districts');
	Route::post('district/store', [DistrictController::class,'store']);
	Route::post('district/update', [DistrictController::class,'update']);
	Route::get('district/{id}/destroy', [DistrictController::class,'destroy']);


	Route::get('wards', [WardController::class,'index'])->name('wards');
	Route::post('ward/store', [WardController::class,'store']);
	Route::post('ward/update', [WardController::class,'update']);
	Route::get('ward/{id}/destroy', [WardController::class,'destroy']);


	Route::get('campuses', [CampusController::class,'index'])->name('campuses');
	Route::post('campus/store', [CampusController::class,'store']);
	Route::post('campus/update', [CampusController::class,'update']);
	Route::get('campus/{id}/destroy', [CampusController::class,'destroy']);

	Route::get('faculties', [FacultyController::class, 'index'])->name('faculties');


	Route::get('roles', [RoleController::class,'index'])->name('roles');
	Route::post('role/store', [RoleController::class,'store']);
	Route::post('role/update', [RoleController::class,'update']);
	Route::get('role/{id}/permissions', [RoleController::class,'showPermissions']);
	Route::get('role/{role_id}/permissions/{perm_id}/revoke', [RoleController::class,'revokePermission']);
	Route::post('role/permission/update',[RoleController::class,'updatePermissions']);
	Route::get('role/{id}/destroy', [RoleController::class,'destroy']);


	Route::get('system-modules', [SystemModuleController::class,'index'])->name('system-modules');
	Route::post('system-module/store', [SystemModuleController::class,'store']);
	Route::post('system-module/update', [SystemModuleController::class,'update']);
	Route::get('system-module/{id}/destroy', [SystemModuleController::class,'destroy']);
	Route::get('system-module/{id}/permissions', [SystemModuleController::class,'showPermissions']);



	Route::post('permission/store', [PermissionController::class,'store']);
	Route::post('permission/update', [PermissionController::class,'update']);
	Route::get('permission/{id}/destroy', [PermissionController::class,'destroy']);

	Route::get('graduation-date',[SpecialDateController::class,'index']);
	Route::post('graduation-date/store',[SpecialDateController::class,'storeGraduationDate']);
	Route::post('graduation-date/update',[SpecialDateController::class,'updateGraduationDate']);

});