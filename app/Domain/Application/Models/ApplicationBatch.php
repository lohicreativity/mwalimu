<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Intake;

class ApplicationBatch extends Model
{
    use HasFactory;

    protected $table = 'application_batches';

    /**
     * Establish one to many relationship with study academic years
     */
    public function applicationWindow()
    {
    	return $this->belongsTo(ApplicationWindow::class,'application_window_id');
    }
}
