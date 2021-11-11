<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\HumanResources\Models\Staff;

class ModuleAssignment extends Model
{
    use HasFactory;

    protected $table = 'module_assignments';

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with assessment plans
     */
    public function assessmentPlans()
    {
    	return $this->hasMany(AssessmentPlan::class,'module_assignment_id');
    }

    /**
     * Establish one to many relationship with staffs
     */
    public function staff()
    {
    	return $this->belongsTo(Staff::class,'staff_id');
    }

    /**
     * Establish one to many relationship with modules
     */
    public function module()
    {
    	return $this->belongsTo(Module::class,'module_id');
    }
}
