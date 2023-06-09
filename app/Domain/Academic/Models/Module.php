<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Settings\Models\NTALevel;

class Module extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'modules';

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
        return $this->belongsToMany(Department::class,'module_department','module_id','department_id')->withPivot('campus_id');
    }

    /**
     * Establish one to many relationship with nta levels
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
    	return $this->hasMany(ModuleAssignment::class,'module_id');
    }


    /**
     * Establish one to many relationship with program module assignments
     */
    public function programModuleAssignments()
    {
        return $this->hasMany(ProgramModuleAssignment::class,'module_id');
    }

    /**
     * Set name attribute
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(substr($value, 0,3)).' '.strtoupper(str_replace(' ','',substr($value, 3)));
    }

    /**
     * Set name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    /**
     * Get name attribute
     */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
}
