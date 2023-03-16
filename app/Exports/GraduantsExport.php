<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Domain\Academic\Models\CampusProgram;
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
        $sheets = [];

        $programs = CampusProgram::whereHas('program', function($query) use($this->program_level_id)
		{$query->where('award_id', $this->program_level_id);})->whereHas('student.registrations',function($query) use($this->study_academic_year_id){
		$query->where('study_academic_year_id',$this->study_academic_year_id);})->whereHas('student.studentshipStatus',function($query){
		$query->where('name','GRADUANT');})->with(['program.departments','campus'])->where('campus_id', $this->campus_id)->get();

        foreach ($programs as $key => $program) {
            foreach($program->program->departments as $dpt){
                if($dpt->pivot->campus_id == $program->campus_id){
                    $department = $dpt;
                }
             }
            $sheets[] = new GraduantsPerProgramSheet($program->program->id, $program->program->code, $program->program->name, $department->name, $program->campus->name,$this->study_academic_year_id);
        }

        return $sheets;
    }

    
}