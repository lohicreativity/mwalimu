<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';

    /**
     * Establish one to many relationship with applicants
     */
    public function awards()
    {
    	return $this->hasMany(App\Domain\Academic\Models\Award::class,'level_id');
    }
}
