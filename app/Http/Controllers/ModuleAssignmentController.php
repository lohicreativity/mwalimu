<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ResultFile;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\CourseWorkComponent;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResultLog;
use App\Domain\Academic\Models\ExaminationResultLog;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Actions\ModuleAssignmentAction;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Validator, Auth, PDF, DB;

class ModuleAssignmentController extends Controller
{
	/**
	 * Display a list of staffs to assign modules
	 */
	public function index(Request $request)
	{
		$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campuses'=>Campus::with(['campusPrograms.program','campusPrograms.programModuleAssignments.module','campusPrograms.programModuleAssignments.semester','campusPrograms.programModuleAssignments.module.moduleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
           },'campusPrograms.programModuleAssignments.module.moduleAssignments.staff'])->get(),
           'staffs'=>Staff::with('designation')->get()
      ];
		return view('dashboard.academic.assign-staff-modules',$data)->withTitle('Staff Module Assignment');
	}

  /**
     * Disaplay staff assigned modules 
     */
    public function showStaffAssignedModules(Request $request)
    {
        $staff = Staff::with(['assignedModules.studyAcademicYear'=>function($query){
                   $query->where('status','ACTIVE');
            }])->where('user_id',Auth::user()->id)->first();
        $data = [
           'study_academic_years'=>StudyAcademicYear::all(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'staff'=>$staff,
           'assignments'=>$staff? ModuleAssignment::whereHas('studyAcademicYear',function($query) use ($request){
                  $query->where('id',$request->get('study_academic_year_id'));
             })->with(['studyAcademicYear.academicYear','module','programModuleAssignment.campusProgram.program','programModuleAssignment.campusProgram.campus','programModuleAssignment.semester'])->where('staff_id',$staff->id)->latest()->paginate(20) : [],
        ];
        return view('dashboard.academic.staff-assigned-modules',$data)->withTitle('Staff Assigned Modules');
    }

    /**
     * Show assessment plans to assigned staff
     */
    public function showAssessmentPlans(Request $request,$id)
    {
        try{
            $data = [
               'module_assignment'=>ModuleAssignment::with('module')->findOrFail($id),
               'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$id)->get(),
               'course_work_components'=>CourseWorkComponent::where('module_assignment_id',$id)->get(),
            ];
            return view('dashboard.academic.assessment-plans',$data)->withTitle('Module Assessment Plans');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show module attendance
     */
    public function showAttendance(Request $request, $id)
    {
         //try{
             $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','staff','module'])->findOrFail($id);
             if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>$module_assignment->programModuleAssignment->students
                 ];
                 $pdf = PDF::loadView('dashboard.academic.reports.students-in-optional-module', $data)->setPaper('a4','landscape');
                 return $pdf->stream();
             }else{
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>Student::where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get()
                 ];
                 $pdf = PDF::loadView('dashboard.academic.reports.students-in-core-module', $data)->setPaper('a4','landscape');
                 return $pdf->stream();
             }
         // }catch(\Exception $e){
         //     return $e->getMessage();
         //     return redirect()->back()->with('error','Unable to get the resource specified in this request');
         // }
    }

    /**
     * Store module assignment into database
     */
    public function store(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'staff_id'=>'required',
            'module_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('staff_id',$request->get('staff_id'))->where('module_id',$request->get('module_id'))->count() != 0){

             if($request->ajax()){
                return response()->json(array('error_messages'=>'Module already assigned for this staff in this study academic year'));
             }else{
                return redirect()->back()->with('error','Module already assigned for this staff in this study academic year');
             }
        }


        (new ModuleAssignmentAction)->store($request);

        return Util::requestResponse($request,'Module assignment created successfully');
    }

    /**
     * Upload module assignment results
     */
    public function showResultsUpload(Request $request,$id)
    {
         $data = [
            'module_assignment'=>ModuleAssignment::with('assessmentPlans','module')->findOrFail($id),
         ];
         return view('dashboard.academic.assessment-results',$data)->withTitle('Upload Module Assignment Results');
    }

    /**
     * Upload module assignment results
     */
    public function uploadResults(Request $request)
    {
         $validation = Validator::make($request->all(),[
            'assessment_plan_id'=>'required',
            'results_file'=>'required|mimes:csv,txt'
         ],
         [
            'assessment_plan_id'=>'Assessment is required'
         ]);

         if($validation->fails()){
             if($request->ajax()){
                return response()->json(array('error_messages'=>$validation->messages()));
             }else{
                return redirect()->back()->withInput()->withErrors($validation->messages());
             }
         }
         
         if($request->hasFile('results_file')){
          DB::beginTransaction();
              $module = ModuleAssignment::find($request->get('module_assignment_id'))->module;
              $destination = SystemLocation::uploadsDirectory();
              $request->file('results_file')->move($destination, $request->file('results_file')->getClientOriginalName());

              $file_name = SystemLocation::renameFile($destination, $request->file('results_file')->getClientOriginalName(),'csv', $module->code.'_'.Auth::user()->id.'_'.time());

              
              // Validate clean results
              $validationStatus = true;
              $csvFileName = $file_name;
              $csvFile = public_path('uploads/' . $csvFileName);
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                 if(str_replace(' ', '', $line[1]) < 0 || str_replace(' ', '', $line[1]) > 100){
                   $validationStatus = false;
                 }
              }

              if(!$validationStatus){
                 return redirect()->back()->with('error','Result file contains invalid data');
              }

              if($request->get('assessment_plan_id') != 'FINAL_EXAM'){
                  $plan = AssessmentPlan::find($request->get('assessment_plan_id'));
              }else{
                  $plan = null;
              }
              
              $file = new ResultFile;
              $file->file_name = $file_name;
              $file->extension = $request->file('results_file')->guessClientExtension();
              $file->mime_type = $request->file('results_file')->getClientMimeType();
              //$file->size = $request->file('results_file')->getClientSize();
              $file->module_assignment_id = $request->get('module_assignment_id');
              $file->filable_id = $plan? $plan->id : 0;
              $file->filable_type = $plan? 'assessment_plan' : null;
              $file->uploaded_by_user_id = Auth::user()->id;
              $file->save();

              $csvFileName = $file_name;
              $csvFile = public_path('uploads/' . $csvFileName);
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                $student = Student::where('registration_number',str_replace(' ', '', $line[0]))->first();
                if($student){
                  if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                      $result_log = new ExaminationResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->student_id = $student->id;
                      $result_log->final_score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result_log->exam_type = 'FINAL';
                      $result_log->uploaded_by_user_id = Auth::user()->id;
                      $result_log->save();
                      
                      if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                          $result = $res;
                      }else{
                         $result = new ExaminationResult;
                      }
                      $result->module_assignment_id = $request->get('module_assignment_id');
                      $result->student_id = $student->id;
                      $result->final_score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result->exam_type = 'FINAL';
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }elseif($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                      $result_log = new ExaminationResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->student_id = $student->id;
                      $result_log->final_score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result_log->exam_type = 'SUP';
                      $result_log->uploaded_by_user_id = Auth::user()->id;
                      $result_log->save();
                      
                      if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','SUP')->first()){
                          $result = $res;
                      }else{
                         $result = new ExaminationResult;
                      }
                      $result->module_assignment_id = $request->get('module_assignment_id');
                      $result->student_id = $student->id;
                      $result->final_score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result->exam_type = 'SUP';
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }else{
                      $result_log = new CourseWorkResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->assessment_plan_id = $plan->id;
                      $result_log->student_id = $student->id;
                      $result_log->score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result_log->uploaded_by_user_id = Auth::user()->id;
                      $result_log->save();
                      
                      if($res = CourseWorkResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->first()){
                          $result = $res;
                      }else{
                          $result = new CourseWorkResult;
                      }
                      $result->module_assignment_id = $request->get('module_assignment_id');
                      $result->assessment_plan_id = $plan->id;
                      $result->student_id = $student->id;
                      $result->score = (str_replace(' ', '', $line[1])*$plan->weight)/100;
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }
                }
              }
              DB::commit();
          }
          return redirect()->back()->with('message','Results uploaded successfully');
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(Request $request, $id)
    {
        try{
            $assignment = ModuleAssignment::findOrFail($id);
            $assignment->delete();
            return redirect()->back()->with('message','Module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
