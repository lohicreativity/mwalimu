<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;
use App\Domain\Finance\Models\Payment;

class TranscriptRequest extends Model
{
    use HasFactory;

    protected $table = 'transcript_requests';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }
}
