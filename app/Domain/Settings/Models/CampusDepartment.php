<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Department;

class CampusDepartment extends Model
{
    use HasFactory;

    protected $table = 'campus_department';

    public function department()
    {
        return $this->hasOne(Department::class);
    }

}
