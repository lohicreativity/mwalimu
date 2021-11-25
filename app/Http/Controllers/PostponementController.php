<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Actions\PostponementAction;
use App\Utils\Util;
use Validator;


class PostponementController extends Controller
{
     /**
     * Display a list of postponements
     */
    public function index(Request $request)
    {
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'student'=>$request->has('registration_number')? Student::where('registration_number',$request->get('registration_number'))->first() : null,
           'postponements'=>Postponement::with(['student','StudyAcademicYear.academicYear','semester'])->paginate(20),
           'semesters'=>Semester::all()
    	];
    	return view('dashboard.academic.postponements',$data)->withTitle('Postponements');
    }

    /**
     * Store postponement into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'student_id'=>'required',
            'category'=>'required',
            'semester_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PostponementAction)->store($request);

        return Util::requestResponse($request,'Postponement created successfully');
    }

    /**
     * Update specified postponement
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'student_id'=>'required',
            'category'=>'required',
            'semester_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PostponementAction)->update($request);

        return Util::requestResponse($request,'Postponement updated successfully');
    }

    /**
     * Remove the specified postponement
     */
    public function destroy(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            $postponement->delete();
            return redirect()->back()->with('message','Postponement deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
