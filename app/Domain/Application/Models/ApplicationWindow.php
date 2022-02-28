<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;

class ApplicationWindow extends Model
{
    use HasFactory;

    protected $table = 'application_windows';

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Estalish many to many relationship with programs
     */
    public function campusPrograms()
    {
    	return $this->belongsToMany(CampusProgram::class,'application_window_campus_program','application_window_id','campus_program_id');
    }
}
