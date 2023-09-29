<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Application\Models\Applicant;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\ProgramFee;
use NumberToWords\NumberToWords;
use App\Mail\AdmissionLetterCreated;
use App\Models\User;
use Mail, PDF;
use App\Domain\Settings\Models\SpecialDate;

class SendAdmissionLetterToSelectedApplicantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Applicant $applicant;
    public $program_level_id;
    public $reference_number;

    public function __construct(Applicant $applicant, $program_level_id, $reference_number)
    {
        $this->applicant = $applicant;
        $this->program_level_id = $program_level_id;
        $this->reference_number = $reference_number;
    }

    public function handle()
    {
        $applicant = $this->applicant;

        try {
            $ac_year = date('Y', strtotime($applicant->applicationWindow->end_date));
            $ac_year += 1;

            $study_academic_year = StudyAcademicYear::select('id', 'academic_year_id')
                ->whereHas('academicYear', fn($query) => $query->where('year', 'LIKE', '%/' . $ac_year . '%'))
                ->with('academicYear:id,year')->first();

            $special_dates = SpecialDate::where('name','Orientation')
                                        ->where('study_academic_year_id',$study_academic_year->id)
                                        ->where('intake',$applicant->intake->name)->where('campus_id',$applicant->campus_id)->get();
            
            $orientation_date = null;
            if(count($special_dates) == 0){
                return redirect()->back()->with('error','Orientation date has not been defined');
            }else{
                foreach($special_dates as $special_date){
                    if(!in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                        return redirect()->back()->with('error','Orientation date for '.$applicant->selections[0]->campusProgram->program->award->name.' has not been defined');
                    }else{
                        $orientation_date = $special_date->date;
                    }
                }
            }

        // Checks for Masters
        if($this->program_level_id == 5){
            $research_supervision = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                            ->where('name','LIKE','%Master%')->where('name','LIKE','%Supervision%')->orWhere('name','LIKE','%Research Supervison%');})->first(); 

            if(!$research_supervision){
                return redirect()->back()->with('error','Research supervision fee has not been defined');
            } 
            $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
            return redirect()->back()->with('error','Medical insurance fee has not been defined');
            }

            $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

            if(!$students_union_fee){
            return redirect()->back()->with('error','Students union fee has not been defined');
            }

            $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','%Master%')->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
            return redirect()->back()->with('error','Caution money fee has not been defined');
            }

            $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','%Master%')->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
            return redirect()->back()->with('error','Medical examination fee has not been defined');
            }

            $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','%Master%')->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
            return redirect()->back()->with('error','Registration fee has not been defined');
            }

            $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','%Master%')->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
            return redirect()->back()->with('error','ID card fee for new students has not been defined');
            }

            $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','%Master%')->where('name','LIKE','%Late Registration%');})->first();

            if(!$late_registration_fee){
            return redirect()->back()->with('error','Late registration fee has not been defined');
            }

            $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','%Master%')->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergence%');})->first();

            if(!$welfare_emergence_fund){
            return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined");
            }

            $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                ->where('name','%Master%')->where('name','LIKE','%TCU%');})->first();
            if(!$quality_assurance_fee){
                return redirect()->back()->with('error','TCU quality assurance fee has not been defined');
            }
   
        // Checks for Undergraduates
        }else{
            $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
            return redirect()->back()->with('error','Medical insurance fee has not been defined');
            }

            $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','NOT LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

            if(!$students_union_fee){
            return redirect()->back()->with('error','Students union fee has not been defined');
            }

            $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
            return redirect()->back()->with('error','Caution money fee has not been defined');
            }

            $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
            return redirect()->back()->with('error','Medical examination fee has not been defined');
            }

            $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
            return redirect()->back()->with('error','Registration fee has not been defined');
            }

            $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
            return redirect()->back()->with('error','ID card fee for new students has not been defined');
            }

            $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Late Registration%');})->first();

            if(!$late_registration_fee){
            return redirect()->back()->with('error','Late registration fee has not been defined');
            }

            $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergence%');})->first();

            if(!$welfare_emergence_fund){
            return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined");
            }

            if($this->program_level_id >= 4){
                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                        ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                        ->where('name','LIKE','%TCU%');})->first();
                $message = 'TCU quality assurance fee has not been defined';
            }else{
                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                        ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                        ->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');})->first();
                $message = 'NACTVET qualtity assurance fee has not been defined';
            }
            
            if(!$quality_assurance_fee){
                return redirect()->back()->with('error',$message);
            }
        }

        $program_fee = ProgramFee::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('year_of_study',1)
                                ->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

        if(!$program_fee){
            return redirect()->back()->with('error','Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name);
        }

        $practical_training_fee = null;
        if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'bachelor') && str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'education')){
            $practical_training_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Practical%'); })->first();

            if(!$practical_training_fee){
                return redirect()->back()->with('error','Practical training fee not defined');
            }
        }
        
        if ($practical_training_fee) {
            $practical_training_fee = str_contains($applicant->nationality, 'Tanzania') ? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd;
        }


        $research_supervision_fee = null;
        if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->award->name), 'master')){
            $research_supervision_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                        ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                        ->where('name','LIKE','%Master%')->where('name','LIKE','%Supervision%')->orWhere('name','LIKE','%Research Supervison%');})->first(); 

            if(!$research_supervision_fee){
                return redirect()->back()->with('error','Research supervision fee has not been defined');
            }else{
                $research_supervision_fee = str_contains($applicant->nationality, 'Tanzania') && $research_supervision_fee ? $research_supervision_fee->amount_in_tzs : $research_supervision_fee->amount_in_usd;

            }
        }

            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            
            $data = [
                'applicant' => $applicant,
                'campus_name' => $applicant->selections[0]->campusProgram->campus->name,
                'applicant_name' => $applicant->first_name . ' ' . $applicant->surname,
                'reference_number' => $this->reference_number,
                'program_name' => $applicant->selections[0]->campusProgram->program->name,
                'program_code_name' => $applicant->selections[0]->campusProgram->program->award->name,
                'study_year' => $study_academic_year->academicYear->year,
                'program_duration_no' => $applicant->selections[0]->campusProgram->program->min_duration,
                'orientation_date' => $orientation_date,
                'program_fee' => str_contains($applicant->nationality, 'Tanzania') ? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
                'program_duration' => $numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
                'program_fee_words' => str_contains($applicant->nationality, 'Tanzania') ? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
                'annual_program_fee_words' => str_contains($applicant->nationality, 'Tanzania') ? $numberTransformer->toWords(($program_fee->amount_in_tzs)/2) : $numberTransformer->toWords(($program_fee->amount_in_usd)/2),
                'research_supervision_fee'=> $research_supervision_fee,
                'currency' => str_contains($applicant->nationality, 'Tanzania') ? 'Tsh' : 'Usd',
                'medical_insurance_fee' => str_contains($applicant->nationality, 'Tanzania') ? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,
                'medical_examination_fee' => str_contains($applicant->nationality, 'Tanzania') ? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,
                'registration_fee' => str_contains($applicant->nationality, 'Tanzania') ? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,
                'late_registration_fee' => str_contains($applicant->nationality, 'Tanzania') ? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,
                'practical_training_fee' => $practical_training_fee,
                'identity_card_fee' => str_contains($applicant->nationality, 'Tanzania') ? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
                'caution_money_fee' => str_contains($applicant->nationality, 'Tanzania') ? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
                'nacte_quality_assurance_fee' => str_contains($applicant->nationality, 'Tanzania') ? $quality_assurance_fee->amount_in_tzs : $quality_assurance_fee->amount_in_usd,
                'students_union_fee' => str_contains($applicant->nationality, 'Tanzania') ? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
                'welfare_emergence_fund' => str_contains($applicant->nationality, 'Tanzania') ? $welfare_emergence_fund->amount_in_tzs : $welfare_emergence_fund->amount_in_usd,
            ];
            
            if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->award->name), 'master')){
                $pdf = PDF::loadView('dashboard.application.reports.msc-admission-letter', $data, [], [
                    'margin_top' => 20,
                    'margin_bottom' => 20,
                    'margin_left' => 20,
                    'margin_right' => 20
                    ])->save(base_path('public/uploads').'/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');
            }else{
                $pdf = PDF::loadView('dashboard.application.reports.admission-letter', $data, [], [
                'margin_top' => 20,
                'margin_bottom' => 20,
                'margin_left' => 20,
                'margin_right' => 20
                ])->save(base_path('public/uploads').'/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');
            }

            $user = new User;
            $user->email = $applicant->email;
            $user->username = $applicant->first_name . ' ' . $applicant->surname;
            Mail::to($user)->send(new AdmissionLetterCreated($applicant, $study_academic_year, $pdf));

            $app = Applicant::find($applicant->id);
            $app->status = 'ADMITTED';
            $app->documents_complete_status = 0;
            $app->save();
            $this->response = ['message' => 'Admission package sent successfully', 'status' => 'message']; //redirect()->back()->with('message','Admission package sent successfully');
        } catch (\Exception $e) {
            $this->response = ['message' => $e->getMessage(), 'status' => 'error'];
        }

    }


}