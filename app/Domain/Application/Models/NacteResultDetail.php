<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NacteResultDetail extends Model
{
    use HasFactory;

    protected $table = 'nacte_result_details';

    /**
     * Establish one to many relationship with necta results
     */
    public function results()
    {
    	return $this->hasMany(NacteResult::class,'nacte_result_detail_id');
    }

}
