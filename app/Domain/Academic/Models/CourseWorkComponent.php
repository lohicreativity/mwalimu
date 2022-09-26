<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseWorkComponent extends Model
{
    use HasFactory;

    protected $table = 'course_work_components';

    /**
     * Establish one to many relationship with module assignment
     */
    public function moduleAssignment()
    {
    	return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }
}
