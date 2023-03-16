<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Domain\Academic\Models\Graduant;
use App\Domain\Settings\Models\SpecialDate;

class GraduantsCertPerProgramSheet implements FromQuery, WithTitle, WithMapping, WithHeadings
{
    private $program_code;
    private $program_name;
    private $department_name;
    private $campus_name;
    private $program_id;
    private $study_academic_year_id;

    public function __construct(int $program_id, string $program_code, string $program_name, string $department_name, string $campus_name, int $campus_id, int $study_academic_year_id)
    {
        $this->program_code = $program_code;
        $this->program_name = $program_name;
        $this->department_name = $department_name;
        $this->campus_name = $campus_name;
		$this->campus_id = $campus_id;
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

     public function headings(): array
    {
        return [
        	[
               config('constants.SITE_NAME')
        	],
        	[
               $this->department_name.' - '.$this->campus_name
        	],
        	[
               $this->program_name
        	],
        	[ 'Name','Registration Number','Programme','NTA Level','Classification','Graduation Date']
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->program_code;
    }

    public function map($graduant): array
    {   
        $date = SpecialDate::where('study_academic_year_id',$this->study_academic_year_id)->where('campus_id',$this->campus_id)->where('name','Graduation')->first();
		if(!$date){
            return [];
        }else{
			return [
				$graduant->student->first_name.' '.$graduant->student->middle_name.' '.$graduant->student->surname,
				$graduant->student->registration_number,
				$graduant->student->campusProgram->program->name,
				$graduant->student->campusProgram->program->ntaLevel->name,
				$graduant->student->overallRemark->class,
				$date->date
			];
		}
    }
}