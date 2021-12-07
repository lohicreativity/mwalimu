<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Student;

class AnnualRemark extends Model
{
    use HasFactory;

    protected $table = 'annual_remarks';

    /**
     * Establish one to many relationship with students
     */
    public function student()
    {
    	return $this->belongsTo(Student::class,'student_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Set remark attribute
     */
    public function setRemarkAttribute($value)
    {
        $this->attributes['remark'] = strtoupper($value);
    }

    /**
     * Get remark attribute
     */
    public function getRemarkAttribute($value)
    {
        return strtoupper($value);
    }
}
