<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\Intake;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\DisabilityStatus;

class Applicant extends Model
{
    use HasFactory;

    protected $table = 'applicants';

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
     * Establish one to many relationship with wards
     */
    public function ward()
    {
    	return $this->belongsTo(Ward::class,'ward_id');
    }

    /**
     * Establish one to many relationship with intakes
     */
    public function disabilityStatus()
    {
        return $this->belongsTo(DisabilityStatus::class,'disability_status_id');
    }

    /**
     * Establish one to many relationship with intakes
     */
    public function intake()
    {
        return $this->belongsTo(Intake::class,'intake_id');
    }

    /**
     * Establish one to one relationship with next of kins
     */
    public function nextOfKin()
    {
        return $this->belongsTo(NextOfKin::class,'next_of_kin_id');
    }

    /**
     * Establish one to one relationship with program levels
     */
    public function programLevel()
    {
        return $this->belongsTo(Award::class,'program_level_id');
    }

    /**
     * Establish one to many relationship with selections
     */
    public function selections()
    {
        return $this->hasMany(ApplicantProgramSelection::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with necta result details
     */
    public function nectaResultDetails()
    {
        return $this->hasMany(NectaResultDetail::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with nacte result details
     */
    public function nacteResultDetails()
    {
        return $this->hasMany(NacteResultDetail::class,'applicant_id');
    }
}
