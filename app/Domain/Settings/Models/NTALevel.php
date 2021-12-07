<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\Program;

class NTALevel extends Model
{
    use HasFactory;

    protected $table = 'nta_levels';

    /**
     * Establish one to many relationship with programs
     */
    public function programs()
    {
    	return $this->hasMany(Program::class,'nta_level_id');
    }

    /**
     * Establish one to many relationship with awards
     */
    public function award()
    {
    	return $this->belongsTo(Award::class,'award_id');
    }
}
