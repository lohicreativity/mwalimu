<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class ProgramModuleAssignment extends Model
{
    use HasFactory;

    protected $table = 'program_module_assignments';

    /**
     * Establish one to many relationship with modules
     */
    public function module()
    {
    	return $this->belongsTo(Module::class,'module_id');
    }


    /**
     * Establish one to many relationship with opted students pivot
     */
    public function optedStudents()
    {
        return $this->hasMany(StudentProgramModuleAssignment::class,'program_module_assignment_id');
    }


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

    /**
     * Establish one to many relationship with module assignments
     */
    public function moduleAssignments()
    {
        return $this->hasMany(ModuleAssignment::class,'program_module_assignment_id');
    }


    /**
     * Establish one to many through relationship with examination results
     */
    public function examinationResults()
    {
        return $this->hasManyThrough(ExaminationResult::class,ModuleAssignment::class);
    }

    /**
     * Establish many to many relationship with students
     */
    public function students()
    {
        return $this->belongsToMany(Student::class,'student_program_module_assignment','program_module_assignment_id','student_id');
    }

    /**
     * Establish one to many relationship with program module assignment requests
     */
    public function programModuleAssignmentRequests()
    {
        return $this->hasMany(ProgramModuleAssignmentRequest::class,'program_module_assignment_id');
    }
}
