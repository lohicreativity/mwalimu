<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\StudyAcademicYear;

class NactePayment extends Model
{
    use HasFactory;

    protected $table = 'nacte_payments';

    /**
     * Establish one to many relationship with campuses
     */
    public function campus()
    {
    	return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

}
