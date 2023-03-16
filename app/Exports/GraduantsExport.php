<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Registration\Models\Student;
use App\Exports\Sheets\GraduantsPerProgramSheet;

class GraduantsExport implements WithMultipleSheets
{
    use Exportable;

    protected $study_academic_year_id;
	protected $program_level_id;
	protected $campus_id;
    
    public function __construct(int $study_academic_year_id, int $program_level_id, int $campus_id)
    {
        $this->study_academic_year_id = $study_academic_year_id;
		$this->program_level_id = $program_level_id;
		$this->campus_id = $campus_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
		$study_academic_year_id = $this->study_academic_year_id;
		$program_level_id = $this->program_level_id;
		$campus_id = $this->campus_id;
		
        $sheets = [];

        $programs = CampusProgram::whereHas('program', function($query) use($program_level_id)
		{$query->where('award_id', $program_level_id);})->with(['program.departments','campus'])->where('campus_id', $campus_id)->get();
		
		$graduant_exist = Student::whereHas('registrations',function($query) use($study_academic_year_id){
		$query->where('study_academic_year_id',$study_academic_year_id);})->whereHas('studentshipStatus',function($query){
		$query->where('name','GRADUANT');})->whereHas('applicant', function($query) use($program_level_id){$query->where('program_level_id', $program_level_id);})->first();

        foreach ($programs as $key => $program) {
            foreach($program->program->departments as $dpt){
                if($dpt->pivot->campus_id == $program->campus_id){
                    $department = $dpt;
                }
             }
			 if($graduant_exist){
				$sheets[] = new GraduantsPerProgramSheet($program->program->id, $program->program->code, $program->program->name, $department->name, $program->campus->name,$study_academic_year_id);				 
			 }
        }

        return $sheets;
    }

    
}