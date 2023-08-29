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
use App\Domain\Settings\Models\SpecialDate;
use NumberToWords\NumberToWords;
use Illuminate\Http\Request;
use App\Mail\AdmissionLetterCreated;
use App\Models\User;
use Mail, PDF, Auth;

class SendAdmissionLetter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;

    public $tries = 5;

    private $response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = (object) $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    { return 1;
        set_time_limit(240);
        //ini_set('memory_limit', '1024M');
        ini_set('memory_limit', '-1');

        $request = $this->request;
        $staff = User::find(Auth::user()->id)->staff;

/*         $applicants = Applicant::select('id','first_name','surname','email','campus_id','application_window_id','intake_id','nationality')->whereHas('selections',function($query){$query->where('status','SELECTED');})
                                ->with(['intake:id,name','selections'=>function($query){$query->select('id','status','campus_program_id','applicant_id')->where('status','SELECTED');},
                                        'selections.campusProgram:id,program_id,campus_id','selections.campusProgram.program:id,name,award_id,min_duration','selections.campusProgram.program.award:id,name',
                                        'campus:id,name','applicationWindow:id,end_date'])
                                ->where('program_level_id',$request->get('program_level_id'))->where('status','SELECTED')
                                ->where('campus_id', $staff->campus_id)->where('application_window_id',$request->get('application_window_id'))
                                ->where(function($query){$query->where('multiple_admissions',0)->orWhere('confirmation_status','CONFIRMED');})->get(); */

                                $applicants = Applicant::whereHas('selections',function($query) use($request){
                                    $query->where('status','SELECTED');
                               })->with(['nextOfKin','intake','selections'=>function($query){
                                    $query->where('status','SELECTED');
                               },'selections.campusProgram.program','applicationWindow','country','selections.campusProgram.campus'])->where('program_level_id',$request->program_level_id)->where('status','SELECTED')->where('application_window_id',$request->application_window_id)->get();
                       

        $ac_year = date('Y',strtotime($applicants[0]->applicationWindow->end_date));
        $ac_year += 1;
        
        $study_academic_year = StudyAcademicYear::select('id','academic_year_id','begin_date')->whereHas('academicYear',function($query) use($ac_year){$query->where('year','LIKE','%/'.$ac_year.'%');})
            ->with('academicYear:id,year')->first();

        $level_orientation_date = null;
        $orientation_dates = SpecialDate::where('name','Orientation')->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicants[0]->intake->name)->where('campus_id',$applicants[0]->campus_id)->get();

        foreach($orientation_dates as $orientation_date){
            if(in_array($applicants[0]->selections[0]->campusProgram->program->award->name, unserialize($orientation_date->applicable_levels))){
                $level_orientation_date = $orientation_date;
                break;
            }
        }
        
        // Checks for Masters
        if($request->get('program_level_id') == 5){



            return 1;


                $research_supervion = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                ->where('name','LIKE','%Research%');})->first(); 

                if(!$research_supervion){
                    return redirect()->back()->with('error','Research supervision fee has not been defined');
                } 
        
            // Checks for Undergraduates
            }else{
                $orientation_date = SpecialDate::where('name','Orientation')->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicants[0]->intake->name)->where('campus_id',$applicants[0]->campus_id)->first();

                $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

                $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                        ->where('name','NOT LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

                $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%Caution Money%');})->first();

                $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                        ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                        ->where('name','LIKE','%Medical Examination%');})->first();

                $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Registration%')->where('name','NOT LIKE','%Late%');})->first();

                $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%New ID Card%');})->first();

                $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Late Registration%');})->first();

                $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergence%');})->first();

                if($request->get('program_level_id') == 4){
                    $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                            ->where('name','LIKE','%TCU%');})->first();
                }else{
                    $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                            ->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');})->first();
                }

            } 

        foreach($applicants as $applicant){ 
           try{$program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

            $practical_training_fee = null;
            if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'bachelor') && str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'education')){

                 $practical_training_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                     ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                     ->where('name','LIKE','%Practical%'); })->first();

             }  


            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');

            if(!empty($practical_training_fee)){
                $practical_training_fee = str_contains($applicant->nationality,'Tanzania')? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd;

            }

               $data = [
                'applicant'=>$applicant,
                'campus_name'=>$applicant->selections[0]->campusProgram->campus->name,
                'applicant_name'=>$applicant->first_name.' '.$applicant->surname,
                'reference_number'=>$request->reference_number,
                'program_name'=>$applicant->selections[0]->campusProgram->program->name,
                'program_code_name'=>$applicant->selections[0]->campusProgram->program->award->name,
                'study_year'=>$study_academic_year->academicYear->year,
                'program_duration_no'=>$applicant->selections[0]->campusProgram->program->min_duration,
                'commencement_date'=>$study_academic_year->begin_date,
                'program_fee'=>str_contains($applicant->nationality,'Tanzania')? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
                'program_duration'=>$numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
                'program_fee_words'=>str_contains($applicant->nationality,'Tanzania')? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
                'currency'=>str_contains($applicant->nationality,'Tanzania')? 'Tsh' : 'Usd',
                'medical_insurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,   
                'medical_examination_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,    
                'registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,     
                'late_registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,    
                'practical_training_fee'=>$practical_training_fee,
                'identity_card_fee'=>str_contains($applicant->nationality,'Tanzania')? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
                'caution_money_fee'=>str_contains($applicant->nationality,'Tanzania')? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
                'nacte_quality_assurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $quality_assurance_fee->amount_in_tzs : $quality_assurance_fee->amount_in_usd,
                'students_union_fee'=>str_contains($applicant->nationality,'Tanzania')? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
                'welfare_emergence_fund'=>str_contains($applicant->nationality,'Tanzania')? $welfare_emergence_fund->amount_in_tzs : $welfare_emergence_fund->amount_in_usd,
              ];

               $pdf = PDF::loadView('dashboard.application.reports.admission-letter',$data,[],[
                   'margin_top'=>20,
                   'margin_bottom'=>20,
                   'margin_left'=>20,
                   'margin_right'=>20
               ])->save(base_path('public/uploads').'/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');
               $user = new User;
               $user->email = $applicant->email;
               $user->username = $applicant->first_name.' '.$applicant->surname;
               Mail::to($user)->send(new AdmissionLetterCreated($applicant,$study_academic_year,$pdf));
               
			   $app = Applicant::find($applicant->id);
               $app->status = 'ADMITTED';
			   $app->documents_complete_status = 0;
               $app->save();
               $this->response = ['message'=>'Admission package sent successfully','status'=>'message']; //redirect()->back()->with('message','Admission package sent successfully');
           }catch(\Exception $e){
              $this->response = ['message'=>$e->getMessage(),'status'=>'error'];
           }
        }

        return;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
