<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminationResult extends Model
{
    use HasFactory;

    protected $table = 'examination_results';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(App\Domain\Registration\Models\Student::class,'student_id');
    }

    /**
     * Establish one to many relationship with examinations
     */
    public function examination()
    {
    	return $this->belongsTo(App\Domain\Academic\Models\Examination::class,'examination_id');
    }
}
