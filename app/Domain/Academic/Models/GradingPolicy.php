<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\NTALevel;

class GradingPolicy extends Model
{
    use HasFactory;

    protected $table = 'grading_policies';

    /**
     * Establish one to many relationship with nta levels
     */
    public function ntaLevel()
    {
        return $this->belongsTo(NTALevel::class,'nta_level_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
        return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }
}
