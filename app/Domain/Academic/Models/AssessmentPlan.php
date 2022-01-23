<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentPlan extends Model
{
    use HasFactory;

    protected $table = 'assessment_plans';

    /**
     * Establish one to many relationship with module assignments
     */
    public function moduleAssignment()
    {
    	return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }

    /**
     * Establish one to many relationship with module assignments
     */
    public function courseWorkResults()
    {
    	return $this->hasMany(CourseWorkResult::class,'assessment_plan_id');
    }
}
