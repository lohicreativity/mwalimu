<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\GPAClassification;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Registration\Models\Student;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Settings\Models\Currency;
use App\Domain\Finance\Models\FeeAmount;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Utils\Util;
use Carbon\Carbon;
use Auth, Validator, DB;

class AppealController extends Controller
{

	/**
	 * Display a list of appeals
	 */
	public function index(Request $request)
	{
        $result_pub = ResultPublication::latest()->first();
        $appeal_deadline = Carbon::parse($result_pub->created_at)->addDays(30);
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'appeals'=>$request->has('query')? Appeal::whereHas('moduleAssignment',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->whereHas('invoice.gatewayPayment',function($query) use($request, $appeal_deadline){
                 $query->where('created_at','<=',$appeal_deadline);
            })->whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->with(['student','moduleAssignment.studyAcademicYear.academicYear','moduleAssignment.module'])->where('is_paid',1)->latest()->paginate(20) : Appeal::whereHas('moduleAssignment',function($query) use($request){
            	 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->whereHas('invoice.gatewayPayment',function($query) use($request, $appeal_deadline){
                 $query->where('created_at','<=',$appeal_deadline);
            })->with(['student','moduleAssignment.studyAcademicYear.academicYear','moduleAssignment.module'])->where('is_paid',1)->latest()->paginate(20)
        ];
        return view('dashboard.academic.appeals',$data)->withTitle('Appeals');
	}

    /**
     * Download appeal list
     */
    public function downloadAppealList(Request $request)
    {
              $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=appeals-list.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

              $list = Appeal::whereHas('moduleAssignment',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->with(['student','moduleAssignment.studyAcademicYear.academicYear','moduleAssignment.module'])->where('is_paid',1)->where('is_downloaded',0)->get();

            Appeal::whereHas('moduleAssignment',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->with(['student','moduleAssignment.studyAcademicYear.academicYear','moduleAssignment.module'])->where('is_paid',1)->update(['is_downloaded'=>1]);

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  foreach ($list as $row) { 
                      fputcsv($file_handle, [$row->student->first_name.' '.$row->student->middle_name.' '.$row->student->surname,$row->student->registration_number,$row->moduleAssignment->module->code]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Upload appeals file
     */
    public function uploadAppealList(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'appeals_file'=>'required|mimes:csv,txt'
         ]);

         if($validation->fails()){
             if($request->ajax()){
                return response()->json(array('error_messages'=>$validation->messages()));
             }else{
                return redirect()->back()->withInput()->withErrors($validation->messages());
             }
         }

         if($request->hasFile('appeals_file')){

              $destination = public_path('uploads/');
              $request->file('appeals_file')->move($destination, $request->file('appeals_file')->getClientOriginalName());

              $file_name = $request->file('appeals_file')->getClientOriginalName();

              $uploaded_students = [];
              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (($output = fgetcsv($file_handle, 0, ',')) != false) {
                  $line_of_text[] = $output;
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                    $uploaded_students[] = $line;
              }

              foreach($uploaded_students as $student){

                  $result = ExaminationResult::whereHas('student',function($query) use($student){
                      $query->where('registration_number',$student[1]);
                  })->whereHas('moduleAssignment.module',function($query) use($student){
                       $query->where('code',$student[2]);
                  })->with(['moduleAssignment.programModuleAssignment'])->first();

                  $result->final_score = ($student[3]*$result->moduleAssignment->programModuleAssignment->final_min_mark)/100;
                  $result->final_remark = $result->moduleAssignment->programModuleAssignment->final_pass_score <= $student[3]? 'PASS' : 'FAIL';
                  $result->exam_type = 'APPEAL';
                  $result->save();

                  Appeal::where('student_id',$result->student_id)->where('module_assignment_id',$result->module_assignment_id)->update(['is_attended'=>1]);

                  // $response = redirect()->to('academic/results/'.$result->student_id.'/'.$result->moduleAssignment->study_academic_year_id.'/'.$result->moduleAssignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$result->moduleAssignment->programModuleAssignment->semester_id);

                  try{
                    $student_id = $result->student_id;
                    $ac_yr_id = $result->moduleAssignment->study_academic_year_id;
                    $yr_of_study = $result->moduleAssignment->programModuleAssignment->year_of_study;
                    $semester_id = $result->moduleAssignment->programModuleAssignment->semester_id;
                        DB::beginTransaction();
                        $student = Student::findOrFail($student_id);
                        $campus_program = CampusProgram::with(['program.ntaLevel'])->find($student->campus_program_id);
                        $semester = Semester::find($semester_id);
                        $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study){
                                  $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study);
                                 })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                                $query->where('program_id',$campus_program->program->id);
                              })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

                         $annual_module_assignments = $module_assignments;
                    
                          $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study, $semester_id){
                                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study)->where('semester_id',$semester_id);
                              })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                            $query->where('program_id',$campus_program->program->id);
                              })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

                              
                          if(count($module_assignments) == 0){
                              DB::rollback();
                              return redirect()->back()->with('error','No results to process');
                          }

                          foreach($module_assignments as $assign){

                            foreach ($uploaded_students as $value) {

                              if ($value[2] == $assign->module->code) {

                                if($assign->course_work_process_status != 'PROCESSED'){
                                  DB::rollback();
                                  return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
                                }
                                if($assign->final_upload_status == null){
                                  DB::rollback();
                                  return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
                                }

                              }
                            }
                            
                          }

                      $student_buffer = [];
                      $annual_credit = 0;

                      foreach ($module_assignments as $assignment) {
                        $results = ExaminationResult::with(['retakeHistory.retakableResults'=>function($query){
                               $query->latest();
                            },'carryHistory.carrableResults'=>function($query){
                               $query->latest();
                            }])->where('module_assignment_id',$assignment->id)->where('student_id',$student->id)->get();
                        $policy = ExaminationPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->study_academic_year_id)->where('type',$assignment->programModuleAssignment->campusProgram->program->category)->first();
                        
                          // if(!$policy){
                          //    return redirect()->back()->with('error','Some programmes are missing examination policy');
                          // }

                          if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

                              $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
                          }else{
                            $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
                          }

                          if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                                 $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
                            })->whereNotNull('final_uploaded_at')->distinct()->count('module_assignment_id') < count($core_programs)){
                              
                              $available_programs = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                                 $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
                                })->whereNotNull('final_uploaded_at')->where('student_id',$student->id)->distinct()->get(['module_assignment_id']);
                              $available_program_ids = [];
                              $missing_programs = [];
                              foreach($available_programs as $pr){
                                  $available_program_ids[] = $pr->moduleAssignment->programModuleAssignment->id;
                              }
                              foreach($core_programs as $prog){
                                  if(!in_array($prog->id, $available_program_ids)){
                                     $missing_programs[] = $prog->module->code;
                                  }
                              }
                              
                              DB::rollback();
                              return redirect()->back()->with('error','Some modules are missing final marks ('.implode(',', $missing_programs).')');
                          }

                          $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester->id)->first();

                          if($elective_policy){
                            if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                                   $query->where('campus_program_id',$campus_program->id)->where('category','OPTIONAL');
                              })->whereNotNull('final_uploaded_at')->where('student_id',$student->id)->distinct()->count('module_assignment_id') != $elective_policy->number_of_options){
                                DB::rollback();
                                return redirect()->back()->with('error','Some optional modules as missing final marks');
                            }
                          }
                        $total_credit = 0;
                        
                        foreach($core_programs as $prog){
                          if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){          
                            $annual_credit += $prog->module->credit;
                            }

                          if($prog->semester_id == $semester_id){
                             $total_credit += $prog->module->credit;
                            }
                        }

                        $student_buffer[$student->id]['opt_credit'] = 0;

                        foreach($results as $key=>$result){          
                          
                                $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student,$semester_id){
                                  $query->where('student_id',$student->id);
                                    })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$semester_id)->where('category','OPTIONAL')->get();

                               foreach($optional_programs as $prog){
                                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                               }
                               $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;

                                if($result->retakeHistory && isset($result->retakeHistory->retakeHistory->retakableResults[0])){
                                    $processed_result = ExaminationResult::find($result->retakeHistory->retakeHistory->retakableResults[0]->id);
                                }elseif($result->carryHistory && isset($result->carryHistory->carrableResults[0])){
                                        $processed_result = ExaminationResult::find($result->carryHistory->carrableResults[0]->id);
                                }else{
                                        $processed_result = ExaminationResult::find($result->id);
                                }
                                if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
                                    $processed_result->total_score = null;
                                }else{
                                  $processed_result->total_score = round($result->course_work_score + $result->final_score);
                                }

                                $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->total_score))->where('max_score','>=',round($processed_result->total_score))->first();

                                if($processed_result->appeal_score){
                                  $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->appeal_score))->where('max_score','>=',round($processed_result->appeal_score))->first();
                                }
                  
                                if(!$grading_policy){
                                   // DB::rollback();
                                   return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
                                }
                                
                                if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                                  $processed_result->grade = null;
                                    $processed_result->point = null;
                                  if($processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                                      $processed_result->final_exam_remark = $processed_result->final_remark;
                                  }
                                  if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->course_work_remark == 'POSTPONED'){
                                      $processed_result->final_exam_remark = $processed_result->course_work_remark;
                                  }
                                }else{
                                  $processed_result->grade = $grading_policy? $grading_policy->grade : null;
                                    $processed_result->point = $grading_policy? $grading_policy->point : null;
                                    $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';

                                    if($processed_result->course_work_remark == 'FAIL' || $processed_result->final_remark == 'FAIL'){
                                       $processed_result->final_exam_remark = 'FAIL';
                                       // $processed_result->grade = 'F';
                                       // $processed_result->point = 0;
                                    }
                                      
                                    

                                    if($processed_result->supp_score){
                                      if(Util::stripSpacesUpper($assignment->module->ntaLevel->name) == Util::stripSpacesUpper('NTA Level 7')){
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'CARRY';
                                      }else{
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                                      }

                                      $processed_result->supp_processed_at = now();
                                      $processed_result->supp_processed_by_user_id = Auth::user()->id;
                                      
                                    }
                                }

                                if($result->exam_type == 'SUPP'){
                                   $processed_result->total_score = $result->final_score;
                                   $processed_result->grade = 'C';
                                }
                                
                                if($result->exam_category == 'CARRY'){
                                   $processed_result->course_work_score = null;
                                   $processed_result->course_work_remark = null;
                                }

                                
                                $processed_result->final_processed_by_user_id = Auth::user()->id;
                                $processed_result->final_processed_at = now();
                                $processed_result->save();

                                $student_buffer[$student->id]['results'][] =  $processed_result;
                                $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
                                $student_buffer[$student->id]['total_credit'] = $total_credit;
                                $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;

                                if($processed_result->final_exam_remark == 'RETAKE'){
                                        if($hist = RetakeHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
                                          $history = $hist;
                                        }else{
                                          $history = new RetakeHistory;
                                        }

                                        $history->student_id = $student->id;
                                        $history->study_academic_year_id = $ac_yr_id;
                                        $history->module_assignment_id = $assignment->id;
                                        $history->examination_result_id = $processed_result->id;
                                        $history->save();
                                      }

                                      if($processed_result->final_exam_remark == 'CARRY'){
                                        if($hist = CarryHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
                                          $history = $hist;
                                        }else{
                                          $history = new CarryHistory;
                                        }

                                        $history->student_id = $student->id;
                                        $history->study_academic_year_id = $ac_yr_id;
                                        $history->module_assignment_id = $assignment->id;
                                        $history->examination_result_id = $processed_result->id;
                                        $history->save();
                                      }

                        }
                      }
                      
                      foreach ($annual_module_assignments as $assign) {
                        $annual_results = ExaminationResult::where('module_assignment_id',$assign->id)->where('student_id',$student->id)->get();

                        if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

                          $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
                        }else{
                          $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
                        }
                  
                      $annual_credit = 0;
                      foreach($core_programs as $prog){            
                            $annual_credit += $prog->module->credit;
                      }
                        
                      foreach($annual_results as $key=>$result){
                            
                            $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
                              $query->where('id',$student->id);
                                })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                           
                           $student_buffer[$student->id]['annual_results'][] =  $result;
                           $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
                           $student_buffer[$student->id]['annual_credit'] = $annual_credit;
                           foreach($optional_programs as $prog){
                               $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                               $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
                           }
                        }
                    }

                      foreach($student_buffer as $key=>$buffer){
                           $pass_status = 'PASS';
                           $supp_exams = [];
                           $retake_exams = [];
                           $carry_exams = [];
                           foreach($buffer['results'] as $res){
                              if($res->final_exam_remark == 'INCOMPLETE'){
                                  $pass_status = 'INCOMPLETE';
                                  break;
                              }

                              if($res->final_exam_remark == 'POSTPONED'){
                                  $pass_status = 'POSTPONED';
                                  break;
                              }

                              if($res->final_exam_remark == 'RETAKE'){
                                  $pass_status = 'RETAKE'; 
                                  $retake_exams[] = $res->moduleAssignment->module->code;
                                  break;
                              }  

                              if($res->final_exam_remark == 'CARRY'){
                                  $pass_status = 'CARRY'; 
                                  $carry_exams[] = $res->moduleAssignment->module->code;
                                  break;
                              } 

                              if($res->final_exam_remark == 'FAIL'){
                                  $pass_status = 'SUPP'; 
                                  $supp_exams[] = $res->moduleAssignment->module->code;
                              }       
                            }
                           
                           if($rem = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester_id)->where('year_of_study',$buffer['year_of_study'])->first()){
                              $remark = $rem;  
                           }else{
                              $remark = new SemesterRemark;
                           }
                            $remark->study_academic_year_id = $ac_yr_id;
                            $remark->student_id = $key;
                            $remark->semester_id = $semester_id;
                            $remark->remark = $pass_status;
                            if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED'){
                                 $remark->gpa = null;
                            }else{
                               $remark->gpa = Util::computeGPA($buffer['total_credit'],$buffer['results']);
                               $remark->point = Util::computeGPAPoints($buffer['total_credit'],$buffer['results']);
                               $remark->credit = $buffer['total_credit'];
                            }
                            $remark->year_of_study = $buffer['year_of_study'];
                            $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                            $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                            if($remark->gpa && $gpa_class){
                              $remark->class = $gpa_class->name;
                            }else{
                              $remark->class = null;
                            }
                            $remark->save();
                           
                           
                             $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();
                            
                                
                               if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->first()){
                                    $rem = $rm;
                                  
                                }else{
                                    $rem = new AnnualRemark;
                                }
                                $rem->student_id = $key;
                                $rem->year_of_study = $buffer['year_of_study'];
                                $rem->study_academic_year_id = $ac_yr_id;
                                $rem->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
                                if($rem->remark == 'INCOMPLETE' || $rem->remark == 'INCOMPLETE' || $rem->remark == 'POSTPONED'){
                                   $rem->gpa = null;
                                }else{
                                     $rem->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                                     $rem->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                                     $remark->credit = $buffer['annual_credit'];
                                }
                                $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($rem->gpa,1,1))->where('max_gpa','>=',bcdiv($rem->gpa,1,1))->first();
                                if($rem->gpa && $gpa_class){
                                  $rem->class = $gpa_class->name;
                                }else{
                                  $rem->class = null;
                                }
                                $rem->save();
                           }

                       DB::commit();

                    }catch(\Exception $e){
                       return $e->getMessage();
                    }
              }
          }

          return redirect()->back()->with('message','Appeals processed successfully');
    }

    /**
     * Get control number 
     */
    public function appealResults(Request $request)
    {
    	// $headers = array('Accept' => 'application/json');
     //    $options = array('auth' => array('user', 'pass'));
     //    $request = WpOrg\Requests\Requests::get('https://api.github.com/gists', $headers, $options);
    	$student = User::find(Auth::user()->id)->student;
    	$results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();

    	$years = [];
    	$years_of_studies = [];
    	$academic_years = [];
    	foreach($results as $key=>$result){
    		if(!array_key_exists($result->moduleAssignment->programModuleAssignment->year_of_study, $years)){
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study] = [];  
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
    		}
            if(!in_array($result->moduleAssignment->studyAcademicYear->id, $years[$result->moduleAssignment->programModuleAssignment->year_of_study])){

            	$years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
            }
    	}

    	foreach($years as $key=>$year){
    		foreach ($year as $yr) {
    			$years_of_studies[$key][] = StudyAcademicYear::with('academicYear')->find($yr);
    		}
    	}

    	$data = [
    	   'years_of_studies'=>$years_of_studies,
           'student'=>$student
    	];
    	return view('dashboard.student.appeal-examination-results',$data)->withTitle('Examination Results');
    }

    /**
     * Display student academic year results
     */
    public function showAcademicYearResults(Request $request, $ac_yr_id, $yr_of_study)
    {
    	 $student = User::find(Auth::user()->id)->student;
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->where('id',$request->get('semester_id'))->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id){
         	   $query->where('study_academic_year_id',$ac_yr_id);
         })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study){
               $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();

        //  return $results->moduleAssignment->module->nta_level_id;

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	   $query->where('id',$student->id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();

          $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->get();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }
          $unpublished = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','UNPUBLISHED')->count();
    

         if($unpublished > 0) {
          return redirect()->back()->with('error','Results have not yet published');
         }

          $appeals = Appeal::where('student_id',$student->id)->get();

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'publications'=>$publications,
         	'optional_programs'=>$optional_programs,
         	'year_of_study'=>$yr_of_study,
            'appeals'=>$appeals,
            'student'=>$student
         ];
         return view('dashboard.student.appeal-examination-results-report',$data)->withTitle('Examination Results');
    }


    /**
     * Store appeals
     */
    public function store(Request $request)
    {
    	 $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($request, $student){
         	   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($request){
         	 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'));
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();
        
         $usd_currency = Currency::where('code','USD')->first();
         $count = 0;
         foreach($results as $result){
             if($request->get('result_'.$result->id)){
                 $count++;
             }
         }

         if($count == 0){
            return redirect()->back()->with('error','No subject selected for appeal');
         }

         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Appeal%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$results[0]->moduleAssignment->study_academic_year_id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for results appeal');
         }

         if($student->applicant->country->code == 'TZ'){
             $amount = $count*$fee_amount->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $count*$fee_amount->amount_in_usd*$usd_currency->factor;
             $currency = 'TZS';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->actual_amount = $amount;
        $invoice->amount = $amount;
        $invoice->currency = $currency;
		$invoice->applicable_id = $request->get('study_academic_year_id');
		$invoice->applicable_type = 'academic_year';
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
        $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
        $invoice->save();

        $count = 0;
         foreach($results as $result){
             if($request->get('result_'.$result->id)){
                 $appeal = new Appeal;
                 $appeal->examination_result_id = $result->id;
                 $appeal->module_assignment_id = $result->module_assignment_id;
                 $appeal->student_id = $result->student_id;
                 $appeal->invoice_id = $invoice->id;
                 $appeal->save();
                 $count++;
             }
         }

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name; 
        $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

        $number_filter = preg_replace('/[^0-9]/','',$student->email);
        $payer_email = empty($number_filter)? $student->email : 'admission@mnma.ac.tz';

        $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_amount->feeItem->feeType->description,
                                    $fee_amount->feeItem->feeType->gfs_code,
                                    $fee_amount->feeItem->feeType->payment_option,
                                    $student->id,
                                    $first_name.' '.$surname,
                                    $student->phone,
                                    $payer_email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_amount->feeItem->feeType->duration,
                                    $invoice->currency);

        return redirect()->to('student/request-control-number')->with('message','Results appeals submitted successfully');
    }

    /**
     * Request control number
     */
    public function requestControlNumber(Request $request,$billno,$inst_id,$amount,$description,$gfs_code,$payment_option,$payerid,$payer_name,$payer_cell,$payer_email,$generated_by,$approved_by,$days,$currency){
            $data = array(
                'payment_ref'=>$billno,
                'sub_sp_code'=>$inst_id,
                'amount'=> $amount,
                'desc'=> $description,
                'gfs_code'=> $gfs_code,
                'payment_type'=> $payment_option,
                'payerid'=> $payerid,
                'payer_name'=> $payer_name,
                'payer_cell'=> $payer_cell,
                'payer_email'=> $payer_email,
                'days_expires_after'=> $days,
                'generated_by'=>$generated_by,
                'approved_by'=>$approved_by,
                'currency'=>$currency
            );

            //$txt=print_r($data, true);
            //$myfile = file_put_contents('/var/public_html/ifm/logs/req_bill.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            $url = url('bills/post_bill');
            $result = Http::withHeaders([
                        'X-CSRF-TOKEN'=> csrf_token()
                      ])->post($url,$data);

            
        return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
                        
        }
}
