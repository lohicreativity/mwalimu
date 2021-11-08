<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    /**
     * Establish one to many relationship with programs
     */
    public function department()
    {
    	return $this->hasMany(App\Domain\Academic\Program::class,'department_id');
    }
}
