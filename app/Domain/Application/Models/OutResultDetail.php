<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutResultDetail extends Model
{
    use HasFactory;

    protected $table = 'out_results_details';

    /**
     * Establish one to many relationship with out results
     */
    public function results()
    {
    	return $this->hasMany(OutResult::class,'out_result_detail_id');
    }

    /**
     * Establish one to many relationship with applicant
     */
    public function applicant()
    {
    	return $this->belongsTo(Applicant::class,'applicant_id');
    }

}
