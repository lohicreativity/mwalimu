<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\Semester;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Actions\SpecialExamAction;
use App\Utils\Util;
use Validator;

class SpecialExamController extends Controller
{
     /**
     * Display a list of exams
     */
    public function index(Request $request, $mod_assign_id)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'exams'=>SpecialExam::with(['student','semester','studyAcademicYear.academicYear','moduleAssignment.module'])->paginate(20),
           'module_assignment'=>ModuleAssignment::with(['module','programModuleAssignment'])->findOrFail($mod_assign_id),
           'student'=>$request->has('registration_number')? Student::where('registration_number',$request->get('registration_number'))->first() : null,
           'semesters'=>Semester::all()
    	];
    	return view('dashboard.academic.special-exams',$data)->withTitle('Special Exams');
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
     * Approve the specified exam
     */
    public function approve($id)
    {
        try{
            $exam = SpecialExam::findOrFail($id);
            $exam->status = 'APPROVED';
            $exam->save();

            return redirect()->back()->with('message','Special exam approve successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Disapprove the specified exam
     */
    public function disapprove($id)
    {
        try{
            $exam = SpecialExam::findOrFail($id);
            $exam->status = 'DISAPPROVED';
            $exam->save();

            return redirect()->back()->with('message','Special exam disapprove successfully');
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
