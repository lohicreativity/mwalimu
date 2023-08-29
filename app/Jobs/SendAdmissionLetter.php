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

        $applicants = Applicant::whereHas('selections',function($query) use($request){
             $query->where('status','SELECTED');
        })->with(['nextOfKin','intake','selections'=>function($query){
             $query->where('status','SELECTED');
        },'selections.campusProgram.program','applicationWindow','country','selections.campusProgram.campus'])->where('program_level_id',$request->program_level_id)->where('status','SELECTED')->where('application_window_id',$request->application_window_id)->get();

        // Applicant::whereHas('intake.applicationWindows',function($query) use($request){
        //      $query->where('id',$request->application_window_id);
        // })->whereHas('selections',function($query) use($request){
        //      $query->where('status','APPROVING');
        // })->with(['nextOfKin','intake','selections'=>function($query){
        //      $query->where('status','APPROVING');
        // },'selections.campusProgram.program.award','applicationWindow','country'])->where('program_level_id',$request->program_level_id)->update(['admission_reference_no'=>$request->reference_number]);

        foreach($applicants as $key=>$applicant){
           try{
               
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
