<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Models\User;

class LoanPayment extends Model
{
    use HasFactory;

    protected $table = 'loan_payments';

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
    	return $this->belongsTo(User::class,'uploaded_by_user_id');
    }

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

}
