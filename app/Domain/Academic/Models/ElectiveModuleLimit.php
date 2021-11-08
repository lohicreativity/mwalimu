<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectiveModuleLimit extends Model
{
    use HasFactory;

    protected $table = 'elective_module_limits';

    /**
     * Establish one to many relationship with programs
     */
    public function program()
    {
    	return $this->belongsTo(App\Domain\Academic\Models\Program::class,'program_id');
    }
}
