<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\ApplicationWindow;

class Intake extends Model
{
    use HasFactory;

    protected $table = 'intakes';

    /**
     * Establish one to many relationship with applicants
     */
    public function applicants()
    {
    	return $this->hasMany(Applicant::class,'intake_id');
    }

    /**
     * Establish one to many relationship with application windows
     */
    public function applicationWindows()
    {
        return $this->hasMany(ApplicationWindow::class,'intake_id');
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
