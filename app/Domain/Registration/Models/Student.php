<?php

namespace App\Domain\Registration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\OverallRemark;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Models\AcademicStatus;
use App\Domain\Application\Models\Applicant;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Domain\Finance\Models\Invoice;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'students';

    /**
     * Establish one to many relationship with registrations
     */
    public function registrations()
    {
    	return $this->hasMany(Registration::class,'student_id');
    }

    /**
     * Establish one to many relationship with semester remarks
     */
    public function semesterRemarks()
    {
        return $this->hasMany(SemesterRemark::class,'student_id');
    }

    /**
     * Establish one to many relationship with annual remarks
     */
    public function annualRemarks()
    {
        return $this->hasMany(AnnualRemark::class,'student_id');
    }

    /**
     * Establish one to many relationship with postponements
     */
    public function postponements()
    {
        return $this->hasMany(Postponement::class,'student_id');
    }

    /**
     * Establish one to many relationship with special exams
     */
    public function specialExams()
    {
        return $this->hasMany(SpecialExam::class,'student_id');
    }

    /**
     * Establish one to one relationship with overall remarks
     */
    public function overallRemark()
    {
        return $this->hasOne(OverallRemark::class,'student_id');
    }

    /**
     * Establish one to many relationship with examination results
     */
    public function examinationResults()
    {
        return $this->hasMany(ExaminationResult::class,'student_id');
    }

    /**
     * Establish one to many relationship with course work results
     */
    public function courseWorkResults()
    {
    	return $this->hasMany(CourseWorkResult::class,'student_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusProgram()
    {
        return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with studentship statuses
     */
    public function studentshipStatus()
    {
        return $this->belongsTo(StudentshipStatus::class,'studentship_status_id');
    }

    /**
     * Establish one to many relationship with academic statuses
     */
    public function academicStatus()
    {
        return $this->belongsTo(AcademicStatus::class,'academic_status_id');
    }

    /**
     * Establish one to one relationship with applicants
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class,'applicant_id');
    }

    /**
     * Establish many to many relationship with program module assignments
     */
    public function options()
    {
        return $this->belongsToMany(ProgramModuleAssignment::class,'student_program_module_assignment','student_id','program_module_assignment_id');;
    }

    /**
     * Set Surname attribute
     */
    public function setSurnameAttribute($value)
    {
        $this->attributes['surname'] = strtoupper($value);
    }

    /**
     * Get surname attribute
     */
    public function getSurnameAttribute($value)
    {
        return strtoupper($value);
    }


    /**
     * Establish one to many relationship with disability statuses
     */
    public function disabilityStatus()
    {
        return $this->belongsTo(DisabilityStatus::class,'disability_status_id');
    }

        /**
     * Check if applicant has requested control number
     */
    public static function hasRequestedControlNumber($student)
    {
        $status = false;
        $invoice = Invoice::where('payable_id',$student)
                          ->where('payable_type','student')
                          ->latest()
                          ->first();
        //$invoice = Invoice::where('payable_id',$applicant->id)->whereNotNull('control_no')->where('payable_type','applicant')->latest()->first();
        // $invoice = Invoice::whereHas('payable',function($query) use($applicant){
        //            $query->where('user_id',$applicant->user_id);
        // })->latest()->first();
        if($invoice){
            $status = true;
        }
        return $status;
    }
}
