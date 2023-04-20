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
use App\Domain\Settings\Models\Campus;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\CampusProgram;
use App\Models\User;

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
     * Establish one to one relationship with students
     */
    public function student()
    {
        return $this->hasOne(Student::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with campuses
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Establish one to many relationship with regions
     */
    public function region()
    {
    	return $this->belongsTo(Region::class,'region_id');
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
     * Establish one to one relationship with program levels
     */
    public function applicationWindow()
    {
        return $this->belongsTo(ApplicationWindow::class,'application_window_id');
    }

    /**
     * Establish one to many relationship with selections
     */
    public function selections()
    {
        return $this->hasMany(ApplicantProgramSelection::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with payment
     */
    public function payment()
    {
        return $this->hasMany(Invoice::class,'payable_id');
    }

    /**
     * Establish one to many relationship with health insurances
     */
    public function insurances()
    {
        return $this->hasMany(HealthInsurance::class,'applicant_id');
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

    /**
     * Establish one to many relationship with out result details
     */
    public function outResultDetails()
    {
        return $this->hasMany(OutResultDetail::class,'applicant_id');
    }

    /**
     * Check if applicant has requested control number
     */
    public static function hasRequestedControlNumber(Applicant $applicant)
    {
        $status = false;
        $invoice = Invoice::where('payable_id',$applicant->id)->whereNotNull('control_no')->where('payable_type','applicant')->latest()->first();
        // $invoice = Invoice::whereHas('payable',function($query) use($applicant){
        //            $query->where('user_id',$applicant->user_id);
        // })->latest()->first();
        if($invoice){
            $status = true;
        }
        return $status;
    }

    /** 
     * Check if applicant has confirmed results
     */
    public static function hasConfirmedResults(Applicant $applicant)
    {
        $status = false;
        $result_count = NectaResultDetail::where('applicant_id',$applicant->id)->where('verified',1)->count();
        if($result_count != 0){
            $status = true;
        }
        return $status;
    }
}
