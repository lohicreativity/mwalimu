<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Domain\Academic\Models\Program;
use App\Exports\Sheets\GraduantsPerProgramSheet;

class GraduantsExport implements WithMultipleSheets, WithMapping
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
            $sheets[] = new GraduantsPerProgramSheet($program->id, $program->name,$this->study_academic_year_id);
        }

        return $sheets;
    }

    public function map($graduant): array
    {
        return [
            $graduant->student->first_name.' '.$graduant->student->middle_name.' '.$graduant->student->surname,
            $graduant->student->gender,
            $graduant->student->registration_number,
        ];
    }
}