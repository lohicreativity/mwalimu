<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'academic_years';

    /**
     * Estalish many to many relationship with programs
     */
    public function programs()
    {
    	return $this->belongsToMany(Program::class,'academic_year_program','academic_year_id','program_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function moduleAssignments()
    {
    	return $this->hasMany(ProgramModuleAssignment::class,'academic_year_id');
    }

    /**
     * Establish one to many relationship with elective module limits
     */
    public function electiveModuleLimits()
    {
    	return $this->hasMany(ElectiveModuleLimit::class,'academic_year_id');
    }
}
