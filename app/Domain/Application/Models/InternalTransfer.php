<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
