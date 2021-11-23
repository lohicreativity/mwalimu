<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Settings\Models\UnitCategory;
use App\Domain\Settings\Models\Campus;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'departments';

    /**
     * Establish one to many relationship with programs
     */
    public function programs()
    {
    	return $this->hasMany(Program::class,'department_id');
    }

    /**
     * Establish one to many relationship with unit categories
     */
    public function unitCategory()
    {
    	return $this->belongsTo(UnitCategory::class,'unit_category_id');
    }

    /**
     * Establish many to many relationship with campuses
     */
    public function campuses()
    {
        return $this->belongsToMany(Campus::class,'campus_department','department_id','campus_id');
    }
}
