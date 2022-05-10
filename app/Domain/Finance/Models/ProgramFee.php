<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\Applicant;

class ProgramFee extends Model
{
    use HasFactory;

    protected $table = 'program_fees';

     /**
     * Establish one to many relationship with programs
     */
    public function campusProgram()
    {
        return $this->belongsTo(CampusProgram::class,'campus_program_id');
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

    /**
     * Check if is used
     */
    public static function isUsed($campus_program_id, $year)
    {
        $status = false;
        if(Applicant::whereHas('applicationWindow',function($query) use($year){
            $query->whereYear('end_date',explode('/', $year)[0]);
        })->whereHas('selections',function($query) use($campus_program_id){
            $query->where('campus_program_id',$campus_program_id);
        })->where('tuition_fee_payment_check',1)->count() != 0){
            $status = true;
        }
        return $status;
    }

}
