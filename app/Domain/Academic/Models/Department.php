<?php

namespace App\Domain\Academic\Models;

use App\Domain\Settings\Models\CampusDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Settings\Models\UnitCategory;
use App\Domain\Settings\Models\Campus;
use App\Domain\HumanResources\Models\Staff;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'departments';

    /**
     * Establish many to many relationship with programs
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class,'program_department','department_id','program_id')->withPivot('campus_id');
    }

	/**
	 * Establish one to many relationship with staff
	 */
	 public function staffs()
	 {
		 return $this->hasMany(Staff::class,'department_id');
	 }

    /**
     * Establish one to many relationship with unit categories
     */
    public function unitCategory()
    {
    	return $this->belongsTo(UnitCategory::class,'unit_category_id');
    }

    /**
     * Establish one to many relationship with parents
     */
    public function parent()
    {
        return $this->belongsTo(Department::class,'parent_id');
    }

    /**
     * Establish many to many relationship with campuses
     */
    public function campuses()
    {
        return $this->belongsToMany(Campus::class,'campus_department','department_id','campus_id');
    }

    public function campus()
    {
        return $this->hasOne(CampusDepartment::class, 'department_id');
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
