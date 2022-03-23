<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Domain\Academic\Models\Program;

class GraduantsExport implements WithMultipleSheets
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

        $programs = Program::get();

        foreach ($programs as $key => $program) {
            $sheets[] = new InvoicesPerMonthSheet($program->id, $program->name,$this->study_academic_year_id);
        }

        return $sheets;
    }
}