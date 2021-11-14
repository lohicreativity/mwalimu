<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\CampusProgram;

class Campus extends Model
{
    use HasFactory;

    protected $table = 'campuses';

    /**
     * Establish many to many relationship with programs
     */
    public function programs()
    {
    	return $this->belongsToMany(Program::class,'campus_program','campus_id','program_id')->withPivot('regulator_code');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusPrograms()
    {
    	return $this->hasMany(CampusProgram::class,'campus_id');
    }
}
