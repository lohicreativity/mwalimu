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
     * Establish one to many relationship with special exams
     */
    public function specialExams()
    {
        return $this->hasMany(SpecialExam::class,'student_id');
    }

    /**
     * Establish one to one relationship with overall remarks
     */
    public function overallRemarks()
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
     * Establish one to many relationship with campus programs
     */
    public function studentshipStatus()
    {
        return $this->belongsTo(StudentshipStatus::class,'studentship_status_id');
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

}
