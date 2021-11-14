<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyAcademicYear extends Model
{
    use HasFactory;

    protected $table = 'study_academic_years';

    /**
     * Establish one to many relationship with academic years
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class,'academic_year_id');
    }

    /**
     * Estalish many to many relationship with programs
     */
    public function campusPrograms()
    {
    	return $this->belongsToMany(CampusProgram::class,'study_academic_year_campus_program','study_academic_year_id','campus_program_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function moduleAssignments()
    {
    	return $this->hasMany(ProgramModuleAssignment::class,'academic_year_id');
    }

    /**
     * Establish one to many relationship with elective module limits
     */
    public function electiveModuleLimits()
    {
    	return $this->hasMany(ElectiveModuleLimit::class,'academic_year_id');
    }
}
