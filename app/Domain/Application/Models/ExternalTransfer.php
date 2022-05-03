<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Registration\Models\Student;
use App\Models\User;

class ExternalTransfer extends Model
{
    use HasFactory;

    protected $table = 'external_transfers';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many relationship with applicants
     */
    public function previousProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'previous_campus_program_id');
    }

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
    	return $this->belongsTo(User::class,'transfered_by_user_id');
    }

}
