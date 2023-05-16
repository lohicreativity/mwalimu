<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;

class FeeAmount extends Model
{
    use HasFactory;

    protected $table = 'fee_amounts';

    /**
     * Establish one to many relationship with fee types
     */
    public function feeItem()
    {
    	return $this->belongsTo(FeeItem::class,'fee_item_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    public function campus()
    {
    	return $this->belongsTo(Campus::class,'campus_id');
    }

}
