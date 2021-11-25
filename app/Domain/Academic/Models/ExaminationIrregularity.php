<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class ExaminationIrregularity extends Model
{
    use HasFactory;

    protected $table = 'examination_irregularities';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many relationship with semesters
     */
    public function semester()
    {
    	return $this->belongsTo(Semester::class,'semester_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with module assignments
     */
    public function moduleAssignment()
    {
    	return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }
}
