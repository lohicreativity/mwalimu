<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;
use App\Domain\Finance\Models\Payment;

class Appeal extends Model
{
    use HasFactory;

    protected $table = 'appeals';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many relationship with examination results
     */
    public function examinationResult()
    {
        return $this->belongsTo(ExaminationResult::class,'examination_result_id');
    }

    /**
     * Establish one to many relationship with students
     */
    public function moduleAssignment()
    {
        return $this->belongsTo(moduleAssignment::class,'module_assignment_id');
    }

    /**
     * Establish one to many polymorphic relationship with payments
     */
    public function payment()
    {
        return $this->morphOne(Payment::class,'payable');
    }
}
