<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Domain\Academic\Models\CampusProgram;
use App\Exports\Sheets\GraduantsCertPerProgramSheet;

class GraduantsCertExport implements WithMultipleSheets
{
    use Exportable;

    protected $study_academic_year_id;
    
    public function __construct(int $study_academic_year_id)
    {
        $this->study_academic_year_id = $study_academic_year_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $programs = CampusProgram::with(['program.departments','campus'])->get();

        foreach ($programs as $key => $program) {
            foreach($program->program->departments as $dpt){
                if($dpt->pivot->campus_id == $program->campus_id){
                    $department = $dpt;
                }
             }
            $sheets[] = new GraduantsCertPerProgramSheet($program->program->id, $program->program->code, $program->program->name, $department->name, $program->campus->name, $program->campus_id, $this->study_academic_year_id);
        }

        return $sheets;
    }

    
}