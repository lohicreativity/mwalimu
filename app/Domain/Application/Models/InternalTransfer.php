<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\CampusProgram;

class InternalTransfer extends Model
{
    use HasFactory;

    protected $table = 'internal_transfers';

    /**
     * Establish one to many relationship with applicants
     */
    public function applicant()
    {
    	return $this->belongsTo(Applicant::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function previousProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'previous_campus_program_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function currentProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'current_campus_program_id');
    }

}
