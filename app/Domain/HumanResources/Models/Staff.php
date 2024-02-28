<?php

namespace App\Domain\HumanResources\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Department;
use App\Domain\HumanResources\Models\Designation;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;


class Staff extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'staffs';

    /**
     * Establish one to many relationship with assigned modules
     */
    public function assignedModules()
    {
    	return $this->hasMany(ModuleAssignment::class,'staff_id');
    }

    /**
     * Establish one to many relationship with designations
     */
    public function designation()
    {
    	return $this->belongsTo(Designation::class,'designation_id');
    }

    /**
     * Establish one to many relationship with countries
     */
    public function country()
    {
    	return $this->belongsTo(Country::class,'country_id');
    }

    /**
     * Establish one to many relationship with regions
     */
    public function region()
    {
    	return $this->belongsTo(Country::class,'region_id');
    }

    /**
     * Establish one to many relationship with districts
     */
    public function district()
    {
    	return $this->belongsTo(District::class,'district_id');
    }


    /**
     * Establish one to one relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Establish one to one relationship with campuses
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish one to one relationship with departments
     */
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    /**
     * Establish one to many relationship with wards
     */
    public function ward()
    {
    	return $this->belongsTo(Ward::class,'ward_id');
    }

    /**
     * Establish one to many relationship with disability statuses
     */
    public function disabilityStatus()
    {
    	return $this->belongsTo(DisabilityStatus::class,'disability_status_id');
    }



}
