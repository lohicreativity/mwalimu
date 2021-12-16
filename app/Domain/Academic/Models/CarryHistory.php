<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class CarryHistory extends Model
{
    use HasFactory;

    protected $table = 'carry_histories';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many polymorphic relatioship with retakables
     */
    public function carrableResults()
    {
        return $this->morphMany(ExaminationResult::class,'retakable');
    }

    /**
     * Establish one to one relationship with examination result
     */
    public function examinationResult()
    {
        return $this->hasOne(ExaminationResult::class,'examination_result_id');
    }

    /**
     * Establish one to many relationship with students
     */
    public function studyAcademicYear()
    {
        return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }
    
    /**
     * Establish one to many relationship with module assignment
     */
    public function moduleAssignment()
    {
        return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }


}
