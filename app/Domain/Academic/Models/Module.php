<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';

    /**
     * Establish one to many relationship with departments
     */
    public function department()
    {
    	return $this->belongsTo(Department::class,'department_id');
    }
}
