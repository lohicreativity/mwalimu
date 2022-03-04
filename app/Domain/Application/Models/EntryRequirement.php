<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\CampusProgram;

class EntryRequirement extends Model
{
    use HasFactory;

    protected $table = 'entry_requirements';

    /**
     * Establish one to many relationship with applicant
     */
    public function applicationWindow()
    {
    	return $this->belongsTo(ApplicationWindow::class,'application_window_id');
    }

    /**
     * Establish one to many relationship with campus program
     */
    public function campusProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

}
