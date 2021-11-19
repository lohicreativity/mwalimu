<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramModuleAssignment extends Model
{
    use HasFactory;

    protected $table = 'program_module_assignments';

    /**
     * Establish one to many relationship with modules
     */
    public function module()
    {
    	return $this->belongsTo(Module::class,'module_id');
    }

    /**
     * Establish one to many relationship with semesters
     */
    public function semester()
    {
    	return $this->belongsTo(Semester::class,'semester_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }
}
