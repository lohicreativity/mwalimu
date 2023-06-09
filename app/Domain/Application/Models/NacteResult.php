<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NacteResult extends Model
{
    use HasFactory;

    protected $table = 'nacte_results';

    /**
     * Establish one to many relationship with applicant
     */
    public function applicant()
    {
    	return $this->belongsTo(Applicant::class,'applicant_id');
    }

}
