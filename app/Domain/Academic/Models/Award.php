<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Settings\Models\Level;

class Award extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'awards';

    /**
     * Establish one to many relationship with levels
     */
    public function level()
    {
    	return $this->belongsTo(Level::class,'level_id');
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

    /**
     * Set code attribute
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    /**
     * Get code attribute
     */
    public function getCodeAttribute($value)
    {
        return strtoupper($value);
    }
}
