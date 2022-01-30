<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Program;

class ProgramFee extends Model
{
    use HasFactory;

    protected $table = 'program_fees';

     /**
     * Establish one to many relationship with programs
     */
    public function program()
    {
        return $this->belongsTo(Program::class,'program_id');
    }

    /**
     * Establish one to many relationship with fee types
     */
    public function feeItem()
    {
    	return $this->belongsTo(FeeItem::class,'fee_item_id');
    }

    /**
     * Establish one to many relationship with semesters
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class,'semester_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

}
