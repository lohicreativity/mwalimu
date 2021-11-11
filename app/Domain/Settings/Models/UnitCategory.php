<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Department;

class UnitCategory extends Model
{
    use HasFactory;

    protected $table = 'unit_categories';

    /**
     * Establish one to many relationship with departments
     */
    public function departments()
    {
    	return $this->hasMany(Department::class,'unit_category_id');
    }
}
