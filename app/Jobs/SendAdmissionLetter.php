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
    {
        set_time_limit(240);
        //ini_set('memory_limit', '1024M');
        ini_set('memory_limit', '-1');

        $request = $this->request;
        $staff = User::find(Auth::user()->id)->staff;

        $applicants = Applicant::select('id','campus_id','application_window_id','intake_id','nationality')->whereHas('selections',function($query){$query->where('status','SELECTED');})
                                ->with(['intake:id,name','selections'=>function($query){$query->select('id','status','campus_program_id','applicant_id')->where('status','SELECTED');},
                                        'selections.campusProgram:id,program_id,campus_id','selections.campusProgram.program:id,name,award_id,min_duration','selections.campusProgram.program.award:id,name',
                                        'campus:id,name','applicationWindow:id,end_date'])
                                ->where('program_level_id',$request->get('program_level_id'))->where('status','SELECTED')
                                ->where('campus_id', $staff->campus_id)->where('application_window_id',$request->get('application_window_id'))
                                ->where(function($query){$query->where('multiple_admissions',0)->orWhere('confirmation_status','CONFIRMED');})->get();

        foreach($applicants as $applicant){
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
                   $query->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');
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
                   $query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
                  })->first();
               }
               

               if(!$nacte_quality_assurance_fee){
                   $this->response = ['message'=>'NACTVET Quality Assurance fee not defined','status'=>'error']; //redirect()->back()->with('error','NACTE fee not defined');
               }

               $practical_training_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Practical%');
               })->first();

               if(!$practical_training_fee){
                   $this->response = ['message'=>'Practical training fee not defined','status'=>'error']; //redirect()->back()->with('error','Practical training fee not defined');
               }

               $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%MNMASO%')->orWhere('name','LIKE','%Student Organization%')->orWhere('name','LIKE','%MASO%')
                   ->orWhere('name','LIKE','%Students Union%');})->first();

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
                   $query->where('name','LIKE','%New ID Card%');
               })->first();

               if(!$identity_card_fee){
                   $this->response = ['message'=>'ID card fee for new students not defined','status'=>'error']; //redirect()->back()->with('error','Identity card fee not defined');
               }

               $late_registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
                   $query->where('name','LIKE','%Late Registration%');
               })->first();

               if(!$late_registration_fee){
                   $this->response = ['message'=>'Late registration fee not defined','status'=>'error']; //redirect()->back()->with('error','Late registration fee not defined');
               }

               $orientation_date = SpecialDate::where('name','Orientation')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)->first();

               if(!$orientation_date){
                   return redirect()->back()->with('error','Orientation date not defined');
               }

               $numberToWords = new NumberToWords();
               $numberTransformer = $numberToWords->getNumberTransformer('en');

               $data = [
                 'applicant'=>$applicant,
                 'campus_name'=>$applicant->selections[0]->campusProgram->campus->name,
                 'orientation_date'=>$orientation_date,
                 //'applicant_name'=>$applicant->first_name.' '.$applicant->surname,
                 'reference_number'=>$request->reference_number,
                 'program_name'=>$applicant->selections[0]->campusProgram->program->name,
                 'program_code_name'=>$applicant->selections[0]->campusProgram->program->award->name,
                 'study_year'=>$study_academic_year->academicYear->year,
                 'commencement_date'=>$study_academic_year->begin_date,
                 'program_fee'=>str_contains($applicant->nationality,'Tanzania')? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
                 'program_duration'=>$numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
                 'program_fee_words'=>str_contains($applicant->nationality,'Tanzania')? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
                 'currency'=>str_contains($applicant->nationality,'Tanzania')? 'Tsh' : 'Usd',
                 'medical_insurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,   
                 'medical_examination_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,    
                 'registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,     
                 'late_registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,    
                 'practical_training_fee'=>str_contains($applicant->nationality,'Tanzania')? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd,
                 'identity_card_fee'=>str_contains($applicant->nationality,'Tanzania')? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
                 'caution_money_fee'=>str_contains($applicant->nationality,'Tanzania')? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
                 'nacte_quality_assurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $nacte_quality_assurance_fee->amount_in_tzs : $nacte_quality_assurance_fee->amount_in_usd,
                 'students_union_fee'=>str_contains($applicant->nationality,'Tanzania')? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
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
