<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Domain\Academic\Models\Graduant;

class GraduantsPerProgramSheet implements FromQuery, WithTitle, WithMapping
{
    private $program_name;
    private $program_id;
    private $study_academic_year_id;

    public function __construct(int $program_id, string $program_name, int $study_academic_year_id)
    {
        $this->program_name = $program_name;
        $this->program_id = $program_id;
        $this->study_academic_year_id = $study_academic_year_id;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return Graduant::query()->whereHas('student.campusProgram',function($query){
        	     $query->where('program_id',$this->program_id);
               })->with(['student.campusProgram.program.ntaLevel','student.campusProgram.campus','student.overallRemark'])->where('study_academic_year_id',$this->study_academic_year_id)->where('status','GRADUATING');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->program_name;
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