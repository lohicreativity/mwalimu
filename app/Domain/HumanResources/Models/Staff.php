<?php

namespace App\Domain\HumanResources\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    /**
     * Establish one to many relationship with designations
     */
    public function designation()
    {
    	return $this->belongsTo(Designation::class,'designation_id');
    }
}
