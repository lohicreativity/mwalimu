<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\SpecialExamRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Semester;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\StudentProgramModuleAssignment;
use App\Domain\Academic\Actions\SpecialExamAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Validator, Auth, DB;

class SpecialExamController extends Controller
{
     /**
     * Display a list of exams
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'exams'=>$request->get('query')? SpecialExamRequest::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->with(['student','semester','studyAcademicYear.academicYear','exams.moduleAssignment.module'])->paginate(20) : SpecialExamRequest::with(['student','semester','studyAcademicYear.academicYear','exams.moduleAssignment.module'])->whereNull('approved_by_user_id')->paginate(20),
           'semesters'=>Semester::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.special-exams',$data)->withTitle('Special Exams');
    }

    /**
     * Show postponement form
     */
    public function showPostponement(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;

        $second_semester_publish_status = false;
         if(ResultPublication::whereHas('semester',function($query){
             $query->where('name','LIKE','%2%');
         })->where('study_academic_year_id',session('active_academic_year_id'))->where('status','PUBLISHED')->count() != 0){
            $second_semester_publish_status = true;
         }

        $suppExams = DB::table('examination_results')
        ->join('module_assignments', 'examination_results.module_assignment_id', '=', 'module_assignments.id')
        ->join('program_module_assignments', 'module_assignments.program_module_assignment_id', '=', 'program_module_assignments.id')
        ->join('modules', 'module_assignments.module_id', '=', 'modules.id')
        ->where('examination_results.student_id', $student->id)
        ->where('module_assignments.study_academic_year_id', session('active_academic_year_id'))
        ->where('program_module_assignments.campus_program_id', $student->campus_program_id)
        ->whereIn('examination_results.final_exam_remark', ['FAIL', 'POSTPONED'])
        ->select('module_assignments.id', 'modules.name', 'modules.code', 'examination_results.final_exam_remark')
        ->get();

         $resultPublished = DB::table('students')
         ->join('campus_program', 'students.campus_program_id', '=', 'campus_program.id')
         ->join('programs', 'campus_program.program_id', '=', 'programs.id')
         ->join('results_publications', 'programs.nta_level_id', '=', 'results_publications.nta_level_id')
         ->where('students.id', $student->id)
         ->where('campus_program.id', $student->campus_program_id)
         ->where('results_publications.semester_id', 2)
         ->where('results_publications.study_academic_year_id', session('active_academic_year_id'))
         ->where('results_publications.status', 'PUBLISHED')
         ->get();

        //  $resultsProcessed = DB::table('examination_results')
        // ->join('module_assignments', 'examination_results.module_assignment_id', '=', 'module_assignments.id')
        // ->join('program_module_assignments', 'module_assignments.program_module_assignment_id', '=', 'program_module_assignments.id')
        // ->join('modules', 'module_assignments.module_id', '=', 'modules.id')
        // ->where('module_assignments.study_academic_year_id', session('active_academic_year_id'))
        // ->where('program_module_assignments.semester_id', session('active_semester_id'))
        // ->where('program_module_assignments.campus_program_id', $student->campus_program_id)
        // ->where('examination_results.final_processed_by_user_id', '<>', 0)
        // ->count();

        // if ($resultsProcessed) {
        //     return redirect()->back()->with('error','Examinations phase is over');
        // }

/*         if (sizeof($suppExams) == 0 && sizeof($resultPublished) != 0) {
            return redirect()->back()->with('error','No modules to postpone');
        } */

        if ($student->studentship_status_id == 3) {
            return redirect()->back()->with('error','You have postponed semester or year');
        }

        $specialExams_count = SpecialExamRequest::with(['exams.moduleAssignment.programModuleAssignment','exams.moduleAssignment.module'])->where('student_id',$student->id)->count();

        $Special_exams = SpecialExamRequest::with(['exams.moduleAssignment.programModuleAssignment','exams.moduleAssignment.module'])->where('student_id',$student->id)->paginate(20);

        $Special_exams_requested = SpecialExam::where('student_id',$student->id)->get();

        // $specialExams[] = null;

        if(sizeof($Special_exams_requested) != 0) {

            foreach ($Special_exams_requested as $value) {
                $specialExams[] = $value->module_assignment_id;
            }

            $data =  [
           'second_semester_publish_status'=>$second_semester_publish_status,
           'module_assignments'=>ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
               $query->where('semester_id',session('active_semester_id'))
               ->where('campus_program_id',$student->campus_program_id)
               ->where('year_of_study', $student->year_of_study);
           })->with(['module','programModuleAssignment'])
           ->where('study_academic_year_id',session('active_academic_year_id'))
           ->get(),
           'module_without_special' => ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
            $query->where('semester_id',session('active_semester_id'))
            ->where('campus_program_id',$student->campus_program_id)
            ->where('year_of_study', $student->year_of_study);
        })->with(['module','programModuleAssignment'])
        ->where('study_academic_year_id',session('active_academic_year_id'))
        ->whereNotIn('module_assignments.id', $specialExams)
        ->get(),
           'opted_module'=>ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
            $query->join('student_program_module_assignment', 'program_module_assignments.id', '=', 'student_program_module_assignment.program_module_assignment_id')
            ->where('semester_id',session('active_semester_id'))
            ->where('student_program_module_assignment.student_id', '=', $student->id)
            ->where('campus_program_id',$student->campus_program_id);
            })->with(['module','programModuleAssignment'])
            ->where('study_academic_year_id',session('active_academic_year_id'))
            ->get(), 
            'special_exam_requests'=> $Special_exams,
            'student'=>$student,
            'request'=>$request,
            'suppExams'     => $suppExams,
            'specialExams_count' => $specialExams_count
        ];

        } else {

            $data =  [
                'second_semester_publish_status'=>$second_semester_publish_status,
                'module_assignments'=>ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
                    $query->where('semester_id',session('active_semester_id'))
                    ->where('campus_program_id',$student->campus_program_id)
                    ->where('year_of_study', $student->year_of_study);
                })->with(['module','programModuleAssignment'])
                ->where('study_academic_year_id',session('active_academic_year_id'))
                ->get(),
                'opted_module'=>ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
                 $query->join('student_program_module_assignment', 'program_module_assignments.id', '=', 'student_program_module_assignment.program_module_assignment_id')
                 ->where('semester_id',session('active_semester_id'))
                 ->where('student_program_module_assignment.student_id', '=', $student->id)
                 ->where('campus_program_id',$student->campus_program_id);
                 })->with(['module','programModuleAssignment'])
                 ->where('study_academic_year_id',session('active_academic_year_id'))
                 ->get(), 
                 'special_exam_requests'=> $Special_exams,
                 'student'=>$student,
                 'request'=>$request,
                 'suppExams'     => $suppExams,
                 'specialExams_count' => $specialExams_count
             ];
     

        }
    
        return view('dashboard.student.special-exams',$data)->withTitle('Exam Postponement');
    }

    /**
     * Store exam into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'type'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(SpecialExam::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('type',$request->get('type'))->count() != 0){
        	return redirect()->back()->with('error','Special exam already exists');
        }

        if(ModuleAssignment::whereHas('programModuleAssignment.campusProgram',function($query) {$query->where('campus_program_id',$student->campus_program_id);})
                                                          ->whereHas('programModuleAssignment',function($query)use($request){$query->->where('study_academic_year_id',$request->get('study_academic_year_id'));})
                                                          ->where('semester_id',$request->get('semester_id'));})
                                                          ->where('final_upload_status','UPLOADED')
                                                          ->where('id',$request->get('module_assignment_id'))->count() > 0){
            return redirect()->back()->with('error','You cannot postpone this module because your final results have already been uploaded');                                                                        
        }
        (new SpecialExamAction)->store($request);

        return Util::requestResponse($request,'Special exam created successfully');
    }

    /**
     * Store exam into database
     */
    public function storePostponement(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'type'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $student = Student::find($request->get('student_id'));

        $opted_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
            $query->join('student_program_module_assignment', 'program_module_assignments.id', '=', 'student_program_module_assignment.program_module_assignment_id')
            ->where('semester_id',session('active_semester_id'))
            ->where('student_program_module_assignment.student_id', '=', $student->id)
            ->where('campus_program_id',$student->campus_program_id);
            })->with(['module','programModuleAssignment'])
            ->where('study_academic_year_id',session('active_academic_year_id'))
            ->get();

        if (sizeof($opted_modules) == 0) {
            return redirect()->back()->with('error','You have not opted any optional modules');
        }

        
        
        // if ($request->get('mod_assign_'.$assign->id) ) {
        //     return redirect()->back()->with('error','You have');
        // }

        // if($r = SpecialExamRequest::where('student_id',$request->get('student_id'))->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('type',$request->get('type'))->first()){
        //     $req = $r;
        // }else{


            $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id);
            })->with(['module','programModuleAssignment'])->where('study_academic_year_id',session('active_academic_year_id'))->get();


            if($request->hasFile('postponement_letter')){
                $destination = SystemLocation::uploadsDirectory();
                $request->file('postponement_letter')->move($destination, $request->file('postponement_letter')->getClientOriginalName());
            }

            if($request->hasFile('supporting_document')){
                $destination = SystemLocation::uploadsDirectory();
                $request->file('supporting_document')->move($destination, $request->file('supporting_document')->getClientOriginalName());

            }

            foreach($module_assignments as $assign){
                if($request->get('mod_assign_'.$assign->id) == $assign->id){


                    $req = new SpecialExamRequest;
                    $req->semester_id = $assign->programModuleAssignment->semester_id;
                    $req->study_academic_year_id = session('active_academic_year_id');
                    $req->student_id = $request->get('student_id');
                    $req->type = $request->get('type');
                    $req->status = 'PENDING';
                    $req->postponement_letter = $request->file('postponement_letter')->getClientOriginalName();
                    if($request->hasFile('supporting_document')){
                    $req->supporting_document = $request->file('supporting_document')->getClientOriginalName();
                    }
                    $req->save();

                }
            }
            
        // }

        

        foreach($module_assignments as $assign){
            if($request->get('mod_assign_'.$assign->id) == $assign->id){
                if(SpecialExam::where('student_id',$request->get('student_id'))->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('type',$request->get('type'))->where('module_assignment_id',$assign->id)->count() == 0){
                        $exam = new SpecialExam;
                        $exam->student_id = $request->get('student_id');
                        $exam->study_academic_year_id = session('active_academic_year_id');
                        $exam->module_assignment_id = $request->get('mod_assign_'.$assign->id);
                        $exam->semester_id = $assign->programModuleAssignment->semester_id;
                        $exam->type = $request->get('type');
                        $exam->status = 'PENDING';
                        $exam->special_exam_request_id = $req->id;
                        $exam->save();
                }
            }
        }

        return Util::requestResponse($request,'Exam postponement created successfully');
    }


    /**
     * Download letter
     */
    public function downloadLetter(Request $request, $id)
    {
        try{
            $exam = SpecialExamRequest::findOrFail($id);
            if(file_exists(public_path().'/uploads/'.$exam->postponement_letter)){
               return response()->download(public_path().'/uploads/'.$exam->postponement_letter);
            }else{
                return redirect()->back()->with('error','Unable to get the resource specified in this request');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
    

    /**
     * Download letter
     */
    public function downloadSupportingDocument(Request $request, $id)
    {
        try{
            $exam = SpecialExamRequest::findOrFail($id);
            if(file_exists(public_path().'/uploads/'.$exam->supporting_document)){
               return response()->download(public_path().'/uploads/'.$exam->supporting_document);
            }else{
                return redirect()->back()->with('error','Unable to get the resource specified in this request');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Update specified exam
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'type'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new SpecialExamAction)->update($request);

        return Util::requestResponse($request,'Special exam updated successfully');
    }

    /**
     * Show recommendation for specified postponement
     */
    public function showRecommend(Request $request, $id)
    {
        $data = [
           'postponement'=>SpecialExamRequest::with(['student.campusProgram.program','exams.moduleAssignment.module'])->find($id)
        ];
        return view('dashboard.academic.recommend-special-exam',$data)->withTitle('Recommendation');
    }

    /**
     * Recommend the specified postponement
     */
    public function recommend(Request $request)
    {
            $validation = Validator::make($request->all(),[
              'recommendation'=>'required',
              'recommended'=>'required'
            ],[
              'recommended.required'=>'Recommendation option must be selected'
            ]);

            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }
            $exam = SpecialExamRequest::find($request->get('special_exam_request_id'));
            $exam->recommendation = $request->get('recommendation');
            $exam->recommended = $request->get('recommended');
            $exam->recommended_by_user_id = Auth::user()->id;
            $exam->save();

            return redirect()->to('academic/special-exams?study_academic_year_id='.session('active_academic_year_id'))->with('message','Special exam recommended successfully');
    }

    /**
     * Accept in bulk
     */
    public function acceptSpecialExams(Request $request)
    {
         $exams = SpecialExamRequest::where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
        //  $Special_exams = SpecialExam::where('study_academic_year_id',$request->get('study_academic_year_id'))->first();

        // return $Special_exams;

         foreach($exams as $exam){
            if(!$exam->recommended_by_user_id){
                return redirect()->back()->with('error','Special exam cannot be accepted because it has not been recommended');
                }

            if($request->get('exam_'.$exam->id) == $exam->id ){
                    $req = SpecialExamRequest::find($exam->id);
                    $special_exam = SpecialExam::where('special_exam_request_id', $exam->id)->first();
                    $req->status = $request->get('action') == 'Accept Selected'? 'POSTPONED' : 'DECLINED';
                    $special_exam->status = $request->get('action') == 'Accept Selected'? 'APPROVED' : 'DECLINED';
                    $special_exam->save();
                    $req->approved_by_user_id = Auth::user()->id;
                    $req->save();
                }

            // foreach($Special_exams as $se){
            //     if($request->get('exam_'.$exam->id) == intval($se->special_exam_request_id)){
            //         $req = SpecialExam::where('special_exam_request_id', $exam->id);
            //         $req->status = $request->get('action') == 'Accept Selected'? 'APPROVED' : 'DECLINED';
            //         $req->save();
            //     }
            // }
         }

         return redirect()->back()->with('message','Special exams accepted successfully');
    }


    /**
     * Approve the specified exam
     */
    public function accept($id)
    {
        try{
            $exam = SpecialExamRequest::findOrFail($id);
            $special_exam = SpecialExam::where('special_exam_request_id', $id)->first();
            if(!$exam->recommended_by_user_id){
                return redirect()->back()->with('error','Special exam cannot be accepted because it has not been recommended');
            }
            $exam->status = 'POSTPONED';
            $special_exam->status = 'APPROVED';
            $exam->approved_by_user_id = Auth::user()->id;
            $exam->save();
            $special_exam->save();

            return redirect()->back()->with('message','Special exam approve successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Disapprove the specified exam
     */
    public function decline($id)
    {
        try{
            $exam = SpecialExamRequest::findOrFail($id);
            if(!$exam->recommended_by_user_id){
                return redirect()->back()->with('error','Special exam cannot be declined because it has not been recommended');
            }
            $exam->status = 'DECLINED';
            $exam->approved_by_user_id = Auth::user()->id;
            $exam->save();

            return redirect()->back()->with('message','Special exam disapprove successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified exam
     */
    public function destroyRequest(Request $request, $id)
    {
        try{
            $exam = SpecialExamRequest::findOrFail($id);
            SpecialExam::where('special_exam_request_id',$id)->delete();
            $exam->delete();

            return redirect()->back()->with('message','Exams postponement deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified exam
     */
    public function destroy(Request $request, $id)
    {
        try{
            $exam = SpecialExam::findOrFail($id);
            $exam->delete();
            return redirect()->back()->with('message','Special exam deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
