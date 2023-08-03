<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;

class CampusProgram extends Model
{
    use HasFactory;

    protected $table = 'campus_program';

    /**
     * Establish one to many relationship with students
     */
    public function students()
    {
        return $this->hasMany(Student::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with selections
     */
    public function selections()
    {
        return $this->hasMany(ApplicantProgramSelection::class,'campus_program_id');
    }

     /**
     * Establish one to many relationship with students
     */
    public function entryRequirements()
    {
        return $this->hasMany(EntryRequirement::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with streams
     */
    public function streams()
    {
        return $this->hasMany(Stream::class,'campus_program_id');
    }

    /**
     * Establish one to many through relationship with groups
     */
    public function groups()
    {
        return $this->hasManyThrough(Group::class,Stream::class);
    }

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

    /**
     * Set regulator code attribute
     */
    public function setRegulatorCodeAttribute($value)
    {
        $this->attributes['regulator_code'] = $value;
    }

    /**
     * Get regulator code attribute
     */
    public function getRegulatorCodeAttribute($value)
    {
        return $value;
    }

    /**
     * Estalish many to many relationship with programs
     */
    public function applicationWindows()
    {
        return $this->belongsToMany(ApplicationWindow::class,'application_window_campus_program','campus_program_id','application_window_id');
    }

}
