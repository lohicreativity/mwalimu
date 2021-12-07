<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Application\Models\Applicant;

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
}
