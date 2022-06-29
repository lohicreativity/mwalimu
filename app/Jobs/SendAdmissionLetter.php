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
use Illuminate\Http\Request;
use App\Mail\AdmissionLetterCreated;
use App\Models\User;
use Mail, PDF;

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
    {
        set_time_limit(240);
        ini_set('memory_limit', '1024M');

        $request = $this->request;

        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
             $query->where('id',$request->application_window_id);
        })->whereHas('selections',function($query) use($request){
             $query->where('status','SELECTED');
        })->with(['nextOfKin','intake','selections'=>function($query){
             $query->where('status','SELECTED');
        },'selections.campusProgram.program','applicationWindow','country','selections.campusProgram.campus'])->where('program_level_id',$request->program_level_id)->get();

        // Applicant::whereHas('intake.applicationWindows',function($query) use($request){
        //      $query->where('id',$request->application_window_id);
        // })->whereHas('selections',function($query) use($request){
        //      $query->where('status','APPROVING');
        // })->with(['nextOfKin','intake','selections'=>function($query){
        //      $query->where('status','APPROVING');
        // },'selections.campusProgram.program.award','applicationWindow','country'])->where('program_level_id',$request->program_level_id)->update(['admission_reference_no'=>$request->reference_number]);

        foreach($applicants as $key=>$applicant){
           try{
               $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
               $ac_year += 1;
               $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
                      $query->where('year','LIKE','%/'.$ac_year.'%');
                })->with('academicYear')->first();
               if(!$study_academic_year){
                   $this->response = ['message'=>'Admission study academic year not created','status'=>'error']; //redirect()->back()->with('error','Admission study academic year not created');
               }
               
               $program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

               if(!$program_fee){
                   $this->response = ['message'=>'Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name,'status'=>'error']; //redirect()->back()->with('error','Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name);
               }

               $medical_insurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%NHIF%');
               })->first();

               if(!$medical_insurance_fee){
                   $this->response = ['message'=>'Medical insurance fee not defined','status'=>'error']; //redirect()->back()->with('error','Medical insurance fee not defined');
               }
               
               if(str_contains($applicant->selections[0]->campusProgram->program->award->name,'Bachelor')){
                  $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%TCU%');
                  })->first();
               }else{
                  $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%NACTE%');
                  })->first();
               }
               

               if(!$nacte_quality_assurance_fee){
                   $this->response = ['message'=>'NACTE fee not defined','status'=>'error']; //redirect()->back()->with('error','NACTE fee not defined');
               }

               $practical_training_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Practical%');
               })->first();

               if(!$practical_training_fee){
                   $this->response = ['message'=>'Practical training fee not defined','status'=>'error']; //redirect()->back()->with('error','Practical training fee not defined');
               }

               $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%MNMASO%');
               })->first();

               if(!$students_union_fee){
                   $this->response = ['message'=>'Students union fee not defined','status'=>'error']; //redirect()->back()->with('error','Students union fee not defined');
               }

               $caution_money_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Caution Money%');
               })->first();

               if(!$caution_money_fee){
                   $this->response = ['message'=>'Caution money fee not defined','status'=>'error']; //redirect()->back()->with('error','Caution money fee not defined');
               }

               $medical_examination_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Medical Examination%');
               })->first();

               if(!$medical_examination_fee){
                   $this->response = ['message'=>'Medical examination fee not defined','status'=>'error']; //redirect()->back()->with('error','Medical examination fee not defined');
               }

               $registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Registration%');
               })->first();

               if(!$registration_fee){
                   $this->response = ['message'=>'Registration fee not defined','status'=>'error']; //redirect()->back()->with('error','Registration fee not defined');
               }

               $identity_card_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Identity Card%');
               })->first();

               if(!$identity_card_fee){
                   $this->response = ['message'=>'Identity card fee not defined','status'=>'error']; //redirect()->back()->with('error','Identity card fee not defined');
               }

               $late_registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Late Registration%');
               })->first();

               if(!$late_registration_fee){
                   $this->response = ['message'=>'Late registration fee not defined','status'=>'error']; //redirect()->back()->with('error','Late registration fee not defined');
               }

               $numberToWords = new NumberToWords();
               $numberTransformer = $numberToWords->getNumberTransformer('en');

               $data = [
                 'applicant'=>$applicant,
                 'campus_name'=>$applicant->selections[0]->campusProgram->campus->name,
                 'applicant_name'=>$applicant->first_name.' '.$applicant->surname,
                 'reference_number'=>$applicant->admission_reference_no,
                 'program_name'=>$applicant->selections[0]->campusProgram->program->name,
                 'program_code_name'=>$applicant->selections[0]->campusProgram->program->award->name,
                 'study_year'=>$study_academic_year->academicYear->year,
                 'commencement_date'=>$study_academic_year->begin_date,
                 'program_fee'=>$applicant->country->code == 'TZ'? $program_fee->amount_in_tzs : $program->amount_in_usd,
                 'program_duration'=>$numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
                 'program_fee_words'=>$applicant->country->code == 'TZ'? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
                 'currency'=>$applicant->country->code == 'TZ'? 'Tsh' : 'Usd',
                 'medical_insurance_fee'=>$applicant->country->code == 'TZ'? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,   
                 'medical_examination_fee'=>$applicant->country->code == 'TZ'? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,    
                 'registration_fee'=>$applicant->country->code == 'TZ'? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,     
                 'late_registration_fee'=>$applicant->country->code == 'TZ'? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,    
                 'practical_training_fee'=>$applicant->country->code == 'TZ'? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd,
                 'identity_card_fee'=>$applicant->country->code == 'TZ'? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
                 'caution_money_fee'=>$applicant->country->code == 'TZ'? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
                 'nacte_quality_assurance_fee'=>$applicant->country->code == 'TZ'? $nacte_quality_assurance_fee->amount_in_tzs : $nacte_quality_assurance_fee->amount_in_usd,
                 'students_union_fee'=>$applicant->country->code == 'TZ'? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
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

               $applicant->status = 'ADMITTED';
               $applicant->save();
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
