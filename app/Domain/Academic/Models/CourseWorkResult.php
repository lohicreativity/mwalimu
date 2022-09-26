<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class CourseWorkResult extends Model
{
    use HasFactory;

    protected $table = 'course_work_results';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many polymorphic relationship with examination result changes
     */
    public function changes()
    {
        return $this->morphMany(ExaminationResultChange::class,'resultable');
    }

    /**
     * Establish one to many relationship with assessment plan
     */
    public function assessmentPlan()
    {
        return $this->belongsTo(AssessmentPlan::class,'assessment_plan_id');
    }

}
