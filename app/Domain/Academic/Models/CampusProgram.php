<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;

class CampusProgram extends Model
{
    use HasFactory;

    protected $table = 'campus_program';

    /**
     * Establish one to many relationship with programs
     */
    public function program()
    {
    	return $this->belongsTo(Program::class,'program_id');
    }

    /**
     * Establish one to many relationship with campuses
     */
    public function campus()
    {
    	return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish many to many relationship with study academic years
     */
    public function studyAcademicYears()
    {
    	return $this->belongsToMany(StudyAcademicYear::class,'study_academic_year_campus_program','campus_program_id','study_academic_year_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function programModuleAssignments()
    {
        return $this->hasMany(ProgramModuleAssignment::class,'campus_program_id');
    }
}
