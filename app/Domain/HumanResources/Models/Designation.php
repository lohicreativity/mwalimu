<?php

namespace App\Domain\HumanResources\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $table = 'designations';

    /**
     * Establish many to many relationship with designations
     */
    public function staffs()
    {
    	return $this->belongsToMany(Stass::class,'staff_designation','designation_id','staff_id');
    }
}
