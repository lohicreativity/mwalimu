<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model
{
    use HasFactory;

    protected $table = 'intakes';

    /**
     * Establish one to many relationship with applicants
     */
    public function applicants()
    {
    	return $this->hasMany(App\Domain\Application\Models\Applicant::class,'intake_id');
    }
}
