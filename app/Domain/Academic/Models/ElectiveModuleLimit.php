<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;

class ElectiveModuleLimit extends Model
{
    use HasFactory;

    protected $table = 'elective_module_limits';

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
     * Establish one to many relationship with awards
     */
    public function award()
    {
        return $this->belongsTo(Award::class,'award_id');
    }

    /**
     * Establish one to many relationship with campuses
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class,'campus_id');
    }
}
