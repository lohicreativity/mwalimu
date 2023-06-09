<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';

    /**
     * Establish one to many relationship with elective policies
     */
    public function electivePolicies()
    {
    	return $this->hasMany(ElectivePolicy::class,'semester_id');
    }

    /**
     * Establish one to many relationship with semester remarks
     */
    public function remarks()
    {
    	return $this->hasMany(SemesterRemark::class,'semester_id');
    }

    /**
     * Establish one to many relationship with elective module limits
     */
    public function electiveDeadlines()
    {
    	return $this->hasMany(ElectiveModuleLimit::class,'semester_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function programModuleAssignments()
    {
    	return $this->hasMany(ProgramModuleAssignment::class,'semester_id');
    }

    /**
     * Set name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }

    /**
     * Get name attribute
     */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
}
