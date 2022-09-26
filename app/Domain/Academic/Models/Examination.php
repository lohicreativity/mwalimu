<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';

    /**
     * Establish one to many relationship with examination results
     */
    public function results()
    {
    	return $this->hasMany(ExaminationResult::class,'examination_id');
    }

    /**
     * Establish one to many relationship with course work results
     */
    public function courseWorkResults()
    {
    	return $this->hasMany(CourseWorkResult::class,'examination_id');
    }
}
