<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminationProcessRecord extends Model
{
    use HasFactory;

    protected $table = 'examination_process_records';

    /**
     * Establish one to many relationship with programs
     */
    public function campusProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with semesters
     */
    public function semester()
    {
    	return $this->belongsTo(Semester::class,'semester_id');
    }
}
