<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResult;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    /**
     * Establish one to many relationship with examination results
     */
    public function examinationResults()
    {
    	return $this->hasMany(ExaminationResult::class,'student_id');
    }

    /**
     * Establish one to many relationship with course work results
     */
    public function courseWorkResults()
    {
    	return $this->hasMany(CourseWorkResult::class,'student_id');
    }
}
