<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectivePolicy extends Model
{
    use HasFactory;

    protected $table = 'elective_policies';

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
        return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with semesters
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class,'semester_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusProgram()
    {
        return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }
}
