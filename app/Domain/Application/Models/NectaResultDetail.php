<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NectaResultDetail extends Model
{
    use HasFactory;

    protected $table = 'necta_result_details';

    /**
     * Establish one to many relationship with necta results
     */
    public function results()
    {
    	return $this->hasMany(NectaResult::class,'necta_result_detail_id');
    }

}
