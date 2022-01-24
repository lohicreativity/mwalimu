<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\HumanResources\Models\Staff;
use App\Models\User;

class ModuleAssignmentRequest extends Model
{
    use HasFactory;

    protected $table = 'module_assignment_requests';

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }


    /**
     * Establish one to many relationship with campus programs
     */
    public function campusProgram()
    {
        return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function programModuleAssignment()
    {
    	return $this->belongsTo(ProgramModuleAssignment::class,'program_module_assignment_id');
    }

    /**
     * Establish one to many relationship with staffs
     */
    public function staff()
    {
    	return $this->belongsTo(Staff::class,'staff_id');
    }

    /**
     * Establish one to many relationship with departments
     */
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    /**
     * Establish one to many relationship with modules
     */
    public function module()
    {
    	return $this->belongsTo(Module::class,'module_id');
    }

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
        return $this->belongsTo(User::class,'requested_by_user_id');
    }
}
