<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;
use App\Models\User;

class InsuranceRegistration extends Model
{
    use HasFactory;

    protected $table = 'insurance_registrations';

    /**
     * Establish one to many relationship with applicants
     */
    public function applicant()
    {
    	return $this->belongsTo(Applicant::class,'applicant_id');
    }

    /**
     * Establish one to one relationship with students
     */
    public function student()
    {
        return $this->belongsTo(Student::class,'student_id');
    }

}
