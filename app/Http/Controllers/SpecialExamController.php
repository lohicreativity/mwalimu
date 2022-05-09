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
use App\Domain\Academic\Actions\SpecialExamAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Validator, Auth;

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
           })->with(['student','semester','studyAcademicYear.academicYear','exams.moduleAssignment.module'])->paginate(20) : SpecialExamRequest::fwith(['student','semester','studyAcademicYear.academicYear','exams.moduleAssignment.module'])->paginate(20),
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
        $data =  [
           'second_semester_publish_status'=>$second_semester_publish_status,
           'module_assignments'=>ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
               $query->where('semester_id',session('active_semester_id'))->where('campus_program_id',$student->campus_program_id);
           })->with(['module','programModuleAssignment'])->where('study_academic_year_id',session('active_academic_year_id'))->get(),
           'special_exam_requests'=>SpecialExamRequest::with(['exams.moduleAssignment.programModuleAssignment','exams.moduleAssignment.module'])->where('student_id',$student->id)->paginate(20),
           'student'=>$student,
           'request'=>$request
        ];
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

        if($r = SpecialExamRequest::where('student_id',$request->get('student_id'))->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('type',$request->get('type'))->first()){
            $req = $r;
        }else{
            $req = new SpecialExamRequest;
            $req->semester_id = session('active_semester_id');
            $req->study_academic_year_id = session('active_academic_year_id');
            $req->student_id = $request->get('student_id');
            $req->type = $request->get('type');
            $req->status = 'PENDING';
            if($request->hasFile('postponement_letter')){
              $destination = SystemLocation::uploadsDirectory();
              $request->file('postponement_letter')->move($destination, $request->file('postponement_letter')->getClientOriginalName());
                  $req->postponement_letter = $request->file('postponement_letter')->getClientOriginalName();
              
            }
            if($request->hasFile('supporting_document')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('supporting_document')->move($destination, $request->file('supporting_document')->getClientOriginalName());
                  $req->supporting_document = $request->file('supporting_document')->getClientOriginalName();
            }
            $req->save();
        }

        $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
               $query->where('semester_id',session('active_semester_id'))->where('campus_program_id',$student->campus_program_id);
           })->with(['module','programModuleAssignment'])->where('study_academic_year_id',session('active_academic_year_id'))->get();

        

        foreach($module_assignments as $assign){
            if($request->get('mod_assign_'.$assign->id) == $assign->id){
                if(SpecialExam::where('student_id',$request->get('student_id'))->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('type',$request->get('type'))->where('module_assignment_id',$assign->id)->count() == 0){
                        $exam = new SpecialExam;
                        $exam->student_id = $request->get('student_id');
                        $exam->study_academic_year_id = session('active_academic_year_id');
                        $exam->module_assignment_id = $request->get('mod_assign_'.$assign->id);
                        $exam->semester_id = session('active_semester_id');
                        $exam->type = $request->get('type');
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

         foreach($exams as $exam){
            if($request->get('exam_'.$exam->id) == $exam->id){
                $req = SpecialExamRequest::find($post->id);
                $req->status = $request->get('action') == 'Accept Selected'? 'POSTPONED' : 'DECLINED';
                $req->approved_by_user_id = Auth::user()->id;
                $req->save();
            }
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
            $exam->status = 'APPROVED';
            $exam->approved_by_user_id = Auth::user()->id;
            $exam->save();

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
