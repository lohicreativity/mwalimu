<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyAcademicYear extends Model
{
    use HasFactory;

    protected $table = 'study_academic_years';

    /**
     * Establish one to many relationship with academic years
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class,'academic_year_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function moduleAssignments()
    {
    	return $this->hasMany(ProgramModuleAssignment::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with streams
     */
    public function streams()
    {
        return $this->hasMany(Stream::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with stream components
     */
    public function streamComponents()
    {
        return $this->hasMany(StreamComponent::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with grading policies
     */
    public function gradingPolicies()
    {
        return $this->hasMany(GradingPolicy::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with examination policies
     */
    public function examinationPolicies()
    {
        return $this->hasMany(ExaminationPolicy::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with elective module limits
     */
    public function electiveModuleLimits()
    {
    	return $this->hasMany(ElectiveModuleLimit::class,'academic_year_id');
    }

    /**
     * Get status attribute
     */
    public function getStatusAttribute($value)
    {
        return ucwords($value);
    }
}
