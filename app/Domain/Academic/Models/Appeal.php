<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;
use App\Domain\Finance\Models\Payment;
use App\Domain\Finance\Models\Invoice;

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
        return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }

    /**
     * Establish one to many polymorphic relationship with payments
     */
    public function payment()
    {
        return $this->morphOne(Payment::class,'payable');
    }

    /**
     * Establish one to many relationship with invoices
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'invoice_id');
    }

    public static function exists($appeals,$result)
    {
        $status = false;
        foreach($appeals as $appeal){
            if($appeal->examination_result_id == $result->id){
                $status = true;
            }
        }
        return $status;
    }
}
