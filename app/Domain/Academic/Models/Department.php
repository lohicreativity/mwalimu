<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\UnitCategory;

class Department extends Model
{
    use HasFactory;

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
}
