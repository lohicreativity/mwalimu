<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Hash, Config, DB;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\ApplicationWindow;
use Illuminate\Http\Client\ConnectionException;
use App\Domain\Application\Models\TamisemiStudent;
use App\Models\User;
use App\Models\Role;
use App\Utils\DateMaker;
use App\Domain\Academic\Models\Award;
use App\Domain\Application\Models\ApplicationBatch;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Jobs\GetNectaResultDetails;

class GetNacteResultdetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $application_window_id;
    protected $campus_program_id;

    public $tries = 5;

    private $response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($application_window, $campus_prog_id)
    {
        $this->application_window_id = $application_window;
        $this->campus_program_id = $campus_prog_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);
        DB::beginTransaction();
        $ac_year = StudyAcademicYear::with('academicYear:id,year')->where('status','ACTIVE')->first();
        // explode('/', $ac_year->academicYear->year)[0];
        $applyr = explode('/', $ac_year->academicYear->year)[0];
        $application_window = ApplicationWindow::select('id','intake_id','campus_id')->with('intake:name,id')->find($this->application_window_id);
        $campus_program = CampusProgram::select('id','regulator_code', 'program_id','campus_id')->with(['program:name,id','entryRequirements' => function($query) {
            $query->select('id','pass_grade','must_subjects','other_must_subjects','campus_program_id','max_capacity')->where('campus_program_id', $this->campus_program_id);
        }])->find($this->campus_program_id);
        $program = $campus_program;

        if(count($program->entryRequirements) === 0){
            return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
        }

        if($program->entryRequirements[0]->max_capacity == null){
            return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
        }

        $has_must_subjects = false;

        if(unserialize($campus_program->entryRequirements[0]->must_subjects) != null){
           $has_must_subjects = true;
        }

        $appacyr = $ac_year->academicYear->year;
        $intake = $application_window->intake->name;
        $nactecode = $campus_program->regulator_code;
        if($application_window->campus_id == 1){
            $token = config('constants.NACTE_API_KEY_KIVUKONI');
        }elseif($application_window->campus_id == 2){
            $token = config('constants.NACTE_API_KEY_KARUME');
        }elseif($application_window->campus_id == 3){
            $token = config('constants.NACTE_API_KEY_PEMBA');
        }
        $url="https://www.nacte.go.tz/nacteapi/index.php/api/tamisemiconfirmedlist/".$nactecode."-".$applyr."-".$intake."/".$token;
        // dd($url);
        $returnedObject = null;
        try{
        $arrContextOptions=array(
            "ssl"=>array(
              "verify_peer"=> false,
              "verify_peer_name"=> false,
            ),
          );

          $jsondata = file_get_contents($url,false, stream_context_create($arrContextOptions)); 

          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
          curl_setopt($curl, CURLOPT_POST, true);
          //curl_setopt($curl, CURLOPT_POSTFIELDS, $jsondata);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
          $jsondata= curl_exec($curl);

            curl_close($curl);

             $returnedObject = json_decode($jsondata);

			 }catch(\Exception $e){}
            //  dd($returnedObject);
            //  if(!isset($returnedObject->params)){
            //     return redirect()->back()->with('error','No students to retrieve from TAMISEMI for selected programme');
            //  }
       
             if($returnedObject->code == 404){
                return redirect()->back()->with('error','No students to retrieve from TAMISEMI for selected programme');
             }


          //echo $returnedObject->params[0]->student_verification_id."-dsdsdsdsds-<br />";
          // check for parse errors json_last_error() == JSON_ERROR_NONE

          if (isset($returnedObject->params)) {
            if(count($returnedObject->params)>0){
              for($i=0;$i<count($returnedObject->params);$i++){
                // $parts=explode("/",$returnedObject->params[$i]->username);
                // //create format from returned form four index format 
                // $form4index=$parts[0]."-".$parts[1];
                // $year=$parts[2];
                // if (strpos($returnedObject->params[$i]->username, ',') !== false) {
                //   $form4index=$parts[0]."-".$parts[1]."-".$parts[2];
                //   $year=$parts[3];
                // }
                $form4index = $returnedObject->params[$i]->username;
                $student = null;    
                if(!TamisemiStudent::where('f4indexno',$form4index)->first()){
                    $student = new TamisemiStudent;
                    $student->f4indexno = $form4index;
                    $student->year = $applyr;
                    $student->fullname = $returnedObject->params[$i]->fullname == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->fullname);
                    $student->year = $returnedObject->params[$i]->application_year;
                    $student->programme_id = $nactecode;
                    $student->programme_name = $returnedObject->params[$i]->programe_name;
                    $student->campus = $returnedObject->params[$i]->institution_name;
                    $student->gender = $returnedObject->params[$i]->sex;
                    $student->date_of_birth = $returnedObject->params[$i]->date_of_birth == '' ? null : DateMaker::toDBDate($returnedObject->params[$i]->date_of_birth);
                    $student->phone_number = $returnedObject->params[$i]->phone_number;
                    $student->email = $returnedObject->params[$i]->email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->email);
                    $student->address = $returnedObject->params[$i]->address == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->address);
                    $student->district = $returnedObject->params[$i]->district == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->district);
                    $student->region = $returnedObject->params[$i]->region == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->region);
                    $student->next_of_kin_fullname = $returnedObject->params[$i]->Next_of_kin_fullname == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_fullname);
                    $student->next_of_kin_phone_number = $returnedObject->params[$i]->Next_of_kin_phone_number;
                    $student->next_of_kin_email = $returnedObject->params[$i]->Next_of_kin_email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_email);
                    $student->next_of_kin_address = $returnedObject->params[$i]->Next_of_kin_address == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_address);
                    $student->next_of_kin_region = $returnedObject->params[$i]->Next_of_kin_region == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_region);
                    $student->relationship = $returnedObject->params[$i]->relationship;
                    $student->appacyr = $appacyr;
                    $student->intake = $intake;
                    $student->receiveDate = now();
                    $student->save();

                //    $surname = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1]);

                   if($us = User::where('username',$form4index)->first()){
                       $user = $us;
                   }else{
                       $user = new User;
                   }
                   $user->username = $form4index;
                   $user->email = $returnedObject->params[$i]->email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->email);;
                   $user->password = Hash::make($form4index);
                   $user->save();
   
                   $role = Role::select('id')->where('name','applicant')->first();
                   $user->roles()->sync([$role->id]);

                   $program_level = Award::select('id')->where('name','LIKE','%Basic%')->first();
                   $current_batch = ApplicationBatch::select('batch_no')->where('program_level_id', $program_level->id)->where('application_window_id', $application_window->id)->latest()->first();
                   $prev_batch = ApplicationBatch::select('id')->where('application_window_id',$application_window->id)->where('program_level_id',$program_level->id)
                                       ->where('batch_no', $current_batch->batch_no - 1)->first();
                   // $next_of_kin = new NextOfKin;
                   // $next_of_kin->first_name = explode(' ', $student->next_of_kin_fullname)[0];
                   // $next_of_kin->middle_name = count(explode(' ', $student->next_of_kin_fullname)) == 3? explode(' ',$student->next_of_kin_fullname)[1] : null;
                   // $next_of_kin->surname = count(explode(' ', $student->next_of_kin_fullname)) == 3? explode(' ', $student->next_of_kin_fullname)[2] : explode(' ',$student->next_of_kin_fullname)[1];
                   // $next_of_kin->address = $student->next_of_kin_address;
                   // $next_of_kin->phone = $student->next_of_kin_phone;
                   // $next_of_kin->email = $student->Next_of_kin_email;
                   // $next_of_kin->relationship = $student->relationship;
                   // $next_of_kin->save();
   
                   // $region = Region::where('name',$student->region)->first();
                   // $district = District::where('name',$student->district)->first();
                   // $ward = Ward::where('district_id',$district->id)->first();
   
   
                   if(Applicant::where('index_number',$form4index)->where('campus_id',$campus_program->campus_id)
                       ->where('application_window_id',$application_window->id)->where('is_tamisemi',1)->first()){
                      continue;
                   }else{
                      $applicant = new Applicant;
                   
                   $applicant->first_name = $student->fullname == '' ? '' : explode(' ', $student->fullname)[0];
                   $applicant->middle_name = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ',$student->fullname)[1] : null);
                   $applicant->surname = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1]);
                   $applicant->phone = $student->phone_number == '' ? '' : '225'.substr($student->phone_number,1);
                   $applicant->email = $student->email;
                   $applicant->address = $student->address;
                   $applicant->gender = substr($student->gender, 0,1);
                   $applicant->campus_id = $campus_program->campus_id;
                   $applicant->program_level_id = $program_level->id;
                   // $applicant->next_of_kin_id = $next_of_kin->id;
                   $applicant->application_window_id = $application_window->id;
                   $applicant->batch_id = $prev_batch->id;
                   $applicant->payment_complete_status = 1;
                   $applicant->intake_id = $application_window->intake->id;
                   $applicant->index_number = $form4index;
                   $applicant->admission_year = $applyr;
                   $applicant->entry_mode = 'DIRECT';
                   $applicant->nationality = 'Tanzanian';
                   $applicant->birth_date = $student->date_of_birth;
                   $applicant->country_id = 1;
                   $applicant->user_id = $user->id;
                   $applicant->is_tamisemi = 1;
                   $applicant->save();
   
                   $selection = new ApplicantProgramSelection;
                   $selection->campus_program_id = $campus_program->id;
                   $selection->applicant_id = $applicant->id;
                   $selection->batch_id = $prev_batch->id;
                   $selection->application_window_id = $application_window->id;
                   $selection->order = 1;
                   $selection->status = 'ELIGIBLE';
                   $selection->save();
                   
                }
   
                //    try{
                //        Mail::to($user)->queue(new TamisemiApplicantCreated($student,$applicant,$campus_program->program->name));
                //    }catch(\Exception $e){}
                }
                                
            }
          }
        }//end

        DB::commit();

        GetNectaResultDetails::dispatch(
            $this->campus_program_id,
            $this->application_window_id,
            $has_must_subjects
        );

    }
}
