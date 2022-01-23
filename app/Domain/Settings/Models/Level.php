<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Award;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';

    /**
     * Establish one to many relationship with applicants
     */
    public function awards()
    {
    	return $this->hasMany(Award::class,'level_id');
    }

    /**
     * Set name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }

    /**
     * Get name attribute
     */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
}
