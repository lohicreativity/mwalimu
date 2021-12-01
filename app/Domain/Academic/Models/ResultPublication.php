<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultPublication extends Model
{
    use HasFactory;

    protected $table = 'results_publications';

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
}
