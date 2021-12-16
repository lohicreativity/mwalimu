<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class ExaminationResult extends Model
{
    use HasFactory;

    protected $table = 'examination_results';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many polymorphic relatioship with retakables
     */
    public function retakable()
    {
        return $this->morphTo();
    }

    /**
     * Establish one to many relationship with carry histories
     */
    public function carryHistory()
    {
        return $this->hasOne(CarryHistory::class,'examination_result_id');
    }

    /**
     * Establish one to many relationship with retake histories
     */
    public function retakeHistory()
    {
        return $this->hasOne(RetakeHistory::class,'examination_result_id');
    }
    
    /**
     * Establish one to many relationship with module assignment
     */
    public function moduleAssignment()
    {
        return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }

     
}
