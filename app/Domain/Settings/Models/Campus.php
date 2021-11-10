<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $table = 'campuses';

    /**
     * Establish many to many relationship with programs
     */
    public function programs()
    {
    	return $this->belongsToMany(App\Domain\Application\Models\Program::class,'campus_program','campus_id','program_id')->withPivot('regulator_code');
    }
}
