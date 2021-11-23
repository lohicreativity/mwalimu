<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Registration;

class Stream extends Model
{
    use HasFactory;

    protected $table = 'streams';

    /**
     * Establish one to many relationship with programs
     */
    public function campusProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
        return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with groups
     */
    public function groups()
    {
        return $this->hasMany(Group::class,'stream_id');
    }

    /**
     * Establish one to many relationship with students
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class,'stream_id');
    }
}
