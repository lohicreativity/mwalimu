<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NTALevel extends Model
{
    use HasFactory;

    protected $table = 'nta_levels';

    /**
     * Establish one to many relationship with programs
     */
    public function programs()
    {
    	return $this->hasMany(App\Domain\Academic\Models\Program::class,'program_id');
    }
}
