<?php

namespace App\Domain\Registration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResult;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

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
