<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Application\Models\Applicant;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    /**
     * Establish one to many relationship with departments
     */
    public function department()
    {
    	return $this->belongsTo(Department::class,'department_id');
    }

    /**
     * Establish many to many relationship with departments
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class,'program_department','program_id','department_id')->withPivot('campus_id','department_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusPrograms()
    {
        return $this->hasMany(CampusProgram::class,'program_id');
    }

    /**
     * Establish one to many relationship with awards
     */
    public function award()
    {
        return $this->belongsTo(Award::class,'award_id');
    }

    /**
     * Establish one to many relationship with NTA levels
     */
    public function ntaLevel()
    {
        return $this->belongsTo(NTALevel::class,'nta_level_id');
    }

    /**
     * Establish one to many relationship with module assignments
     */
    public function moduleAssignments()
    {
    	return $this->hasMany(ProgramModuleAssignment::class,'program_id');
    }

    /**
     * Establish one to many relationship with elective module limits
     */
    public function electiveModuleLimits()
    {
    	return $this->hasMany(ElectiveModuleLimit::class,'program_id');
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

        /** 
     * Check if applicant has confirmed results
     */
    public static function hasBeenSelected(Program $program)
    {
        $status = false;
        $applicant_count = Applicant::whereHas('selections.campusProgram',function($query)use($program){$query->where('program_id',$program->id);})->count();
        if($applicant_count == 0){
            $status = true;
        }
        return $status;
    }
}
