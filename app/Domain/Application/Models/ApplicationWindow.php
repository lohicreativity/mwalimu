<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Intake;

class ApplicationWindow extends Model
{
    use HasFactory;

    protected $table = 'application_windows';

    public $fillable = ['enrollment_report_download_status'];

    /**
     * Establish one to many relationship with study academic years
     */
    public function studyAcademicYear()
    {
    	return $this->belongsTo(StudyAcademicYear::class,'study_academic_year_id');
    }

    /**
     * Establish one to many relationship with campus
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish one to many relationship with intakes
     */
    public function intake()
    {
        return $this->belongsTo(Intake::class,'intake_id');
    }

    /**
     * Estalish many to many relationship with programs
     */
    public function campusPrograms()
    {
    	return $this->belongsToMany(CampusProgram::class,'application_window_campus_program','application_window_id','campus_program_id');
    }


    public function applicationBatches()
    {
        return $this->hasMany(ApplicationBatch::class,'application_window_id');
    }
}
