<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Domain\Application\Models\ApplicantProgramSelection;
use DB, Config;

class GetNectaResultDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campus_program_id;
    protected $application_window_id;
    protected $has_must_subjects;

    public $tries = 5;

    private $response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campusProg,$app_window,$hasMustSubjects)
    {
        $this->campus_program_id = $campusProg;
        $this->application_window_id = $app_window;
        $this->has_must_subjects = $hasMustSubjects;
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
        $application_window = ApplicationWindow::select('id','intake_id','campus_id')->with('intake:name,id')->find($this->application_window_id);
        $campus_program = CampusProgram::select('id','regulator_code', 'program_id','campus_id')->with(['program:name,id','entryRequirements' => function($query) {
            $query->select('id','pass_grade','must_subjects','other_must_subjects','campus_program_id','max_capacity')->where('campus_program_id', $this->campus_program_id);
        }])->find($this->campus_program_id);
        $program = $campus_program;

        $applicants = Applicant::select('id','index_number')->where('application_window_id',$application_window->id)
        ->whereHas('selections', function($query) use($campus_program,$application_window) {
        $query->where('campus_program_id', $campus_program->id)->where('application_window_id',$application_window->id)
        ->where('status', 'ELIGIBLE');})
        ->with(['selections' => function($query){ $query->where('status', 'ELIGIBLE');}])
        ->where('is_tamisemi',1)->whereNull('status')->get();

        if($this->has_must_subjects){
    
            foreach($applicants as $applicant){
    
                $parts=explode("/",$applicant->index_number);
                //create format from returned form four index format 
    
                if(str_contains($applicant->index_number,'EQ')){
                    $exam_year = explode('/',$applicant->index_number)[1];
                    $index_no = $parts[0];
                }else{
                    $exam_year = explode('/', $applicant->index_number)[2];
                    $index_no = $parts[0]."-".$parts[1];
                }
                // $exam_year = $parts[2];
                if($det = NectaResultDetail::where('index_number', $applicant->index_number)->where('exam_id', 1)
                          ->where('verified', 1)->first()){
                    $detail = new NectaResultDetail;
                    $detail->center_name = $det->center_name;
                    $detail->center_number = $det->center_number;
                    $detail->first_name = $det->first_name;
                    $detail->middle_name = $det->middle_name;
                    $detail->last_name = $det->last_name;
                    $detail->sex = $det->sex;
                    $detail->index_number = $det->index_number; //json_decode($response)->particulars->index_number;
                    $detail->division = $det->division;
                    $detail->points = $det->points;
                    $detail->exam_id = 1;
                    $detail->applicant_id = $applicant->id;
                    $detail->verified = 1;
                    $detail->save();
    
                    $result = NectaResult::where('necta_result_detail_id', $det->id)->get();
    
                    foreach($result as $res){
                        $newRes = new Nectaresult;
                        $newRes->subject_name = $res->subject_name;
                        $newRes->subject_code = $res->subject_code;
                        $newRes->grade = $res->grade;
                        $newRes->applicant_id = $applicant->id;
                        $newRes->necta_result_detail_id = $detail->id;
                        $newRes->save();
                    }
                    
                } else{
                    $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                        'api_key'=>config('constants.NECTA_API_KEY'),
                        'exam_year'=>$exam_year,
                        'index_number'=>$index_no,
                        'exam_id'=>'1'
                    ]);
    
                    if(!isset(json_decode($response)->results)){
                        return redirect()->back()->with('error','Invalid Index number or year');
                    }
    
                        $detail = new NectaResultDetail;
                        $detail->center_name = json_decode($response)->particulars->center_name;
                        $detail->center_number = json_decode($response)->particulars->center_number;
                        $detail->first_name = json_decode($response)->particulars->first_name;
                        $detail->middle_name = json_decode($response)->particulars->middle_name;
                        $detail->last_name = json_decode($response)->particulars->last_name;
                        $detail->sex = json_decode($response)->particulars->sex;
                        $detail->index_number = $applicant->index_number; //json_decode($response)->particulars->index_number;
                        $detail->division = json_decode($response)->results->division;
                        $detail->points = json_decode($response)->results->points;
                        $detail->exam_id = 1;
                        $detail->applicant_id = $applicant->id;
                        $detail->verified = 1;
                        $detail->save();
    
                    foreach(json_decode($response)->subjects as $subject){
                        $res = new NectaResult;
                        $res->subject_name = $subject->subject_name;
                        $res->subject_code = $subject->subject_code;
                        $res->grade = $subject->grade;
                        $res->applicant_id = $applicant->id;
                        $res->necta_result_detail_id = $detail->id;
                        $res->save();
                    }
    
                }
            }
    
                    $applicants = Applicant::select('id','index_number','rank_points','status')->with([
                'nectaResultDetails' =>function($query){
                     $query->select('id','exam_id','applicant_id')->where('verified',1)->where('exam_id', 1);
                },'nectaResultDetails.results:id,grade,subject_name'])->where('is_tamisemi',1)->whereHas('selections', function($query) use($campus_program) {
                    $query->select('id')->where('campus_program_id', $campus_program->id)->where('status', 'ELIGIBLE');
                })->whereNull('status')->where('application_window_id',$application_window->id)->get();
            
            foreach($applicants as $applicant){
    
    
            $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
    
            
               $index_number = $applicant->index_number;
               if(str_contains($applicant->index_number,'EQ')){
                    $exam_year = explode('/',$applicant->index_number)[1];
                }else{
                    $exam_year = explode('/', $applicant->index_number)[2];
                }
              
    
    
            //    if($exam_year < 2014 || $exam_year > 2015){
            //      $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
            //      $diploma_principle_pass_grade = 'E';
            //      $diploma_subsidiary_pass_grade = 'S';
            //      $principle_pass_grade = 'D';
            //      $subsidiary_pass_grade = 'S';
            //    }else{
            //      $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
            //      $diploma_principle_pass_grade = 'D';
            //      $diploma_subsidiary_pass_grade = 'E';
            //      $principle_pass_grade = 'C';
            //      $subsidiary_pass_grade = 'E';
            //    }
    
               // $selected_program[$applicant->id] = false;
               $subject_count = 0;
                    
                      
                      if(count($campus_program->entryRequirements) == 0){
                        return redirect()->back()->with('error',$campus_program->program->name.' does not have entry requirements');
                      }
    
                      // if($program->entryRequirements[0]->max_capacity == null){
                      //   return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                      // }
    
                       // Certificate
                    //    if(str_contains($award->name,'Certificate')){
                    //        $o_level_pass_count = 0;
                    //        foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                    //          if($detail->exam_id == 1){
                    //            $other_must_subject_ready = false;
                    //            foreach ($detail->results as $key => $result) {
                                  
                    //               if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){
    
                    //                 $applicant->rank_points += $o_level_grades[$result->grade];
                    //                 $subject_count += 1;
    
                    //                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                    //                     if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                    //                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                    //                          $o_level_pass_count += 1;
                    //                        }
                    //                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                    //                          $o_level_pass_count += 1;
                    //                          $other_must_subject_ready = true;
                    //                        }
                    //                     }else{
                    //                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                    //                          $o_level_pass_count += 1;
                    //                        }
                    //                     }
                    //                  }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                    //                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                    //                          $o_level_pass_count += 1;
                    //                     }
                    //                  }else{
                    //                     $o_level_pass_count += 1;
                    //                  }
                    //               }
                    //            }
                    //          }
                    //          if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                    //            $select = ApplicantProgramSelection::find($selection->id);
                    //            $select->status = 'SELECTED';
                    //            $select->status_changed_at = now();
                    //            $select->save();
                    //          }
                    //        }
                    //    }
    
                       //NEW
                    // Certificate
                    $must_subject_count = 0;
                    $counted_must_subjects = 0;
                    $counted_other_must_subjects = 0;
                    $o_level_pass_count = $o_level_points = 0;
                    $o_level_other_pass_count = 0;
                    foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                          $other_must_subject_ready = false;
                          foreach ($detail->results as $key => $result) {
                             
                             if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){
    
                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;
    
                                    $must_subject_count = count(unserialize($program->entryRequirements[0]->must_subjects));
    
                                   
                                    if($counted_must_subjects == $must_subject_count && unserialize($program->entryRequirements[0]->other_must_subjects) == ''){
                                        $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                        $select->status = 'SELECTED';
                                        $select->status_changed_at = now();
                                        $select->save();
    
                                        $applicant->status = 'SELECTED';
                                        $applicant->save();
                                        break;
                                    }elseif($counted_must_subjects == $must_subject_count && $counted_other_must_subjects > 0){
                                        $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                        $select->status = 'SELECTED';
                                        $select->status_changed_at = now();
                                        $select->save();
    
                                        $applicant->status = 'SELECTED';
                                        $applicant->save();
                                        break;
                                    }
    
                                if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $counted_must_subjects++;
    
                                    }
                                }else if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $counted_other_must_subjects++;
                                    }
                                }else {
                                    continue;
                                }
                                 
                             }
                          }
    
    
                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                            $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                            $select->status = 'SELECTED';
                            $select->status_changed_at = now();
                            $select->save();
    
                            $applicant->status = 'SELECTED';
                            $applicant->save();
                        }
                    }
    
                    if($counted_must_subjects != $must_subject_count && unserialize($program->entryRequirements[0]->other_must_subjects) == ''){
                        $applicant->status = 'NOT SELECTED';
                        $applicant->save();
                    }elseif($counted_must_subjects == $must_subject_count && $counted_other_must_subjects == 0 && unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                        $applicant->status = 'NOT SELECTED';
                        $applicant->save();
                    }
                
            }
    
            
            }else {

                foreach($applicants as $applicant){
                    $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                    $select->status = 'SELECTED';
                    $select->status_changed_at = now();
                    $select->save();
        
                    $applicant->status = 'SELECTED';
                    $applicant->save();
                }
          }

        DB::commit();
    }
}
