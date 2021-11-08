<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Actions\AcademicYearAction;


class AcademicYearController extends Controller
{
     /**
     * Display a list of departments
     */
    public function index()
    {
    	$data = [
           'academic_years'=>AcademicYear::paginate(20)
    	];
    	return view('dashboard.academic.academic-years',$data)->withTitle('Academic Years');
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make(Input::all(),[
            'year'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        $academic_year = new Academic;
        $academic_year->year = $request->get('year');
        $academic_year->save();

        if($request->ajax()){
           return response()->json(array('success_messages'=>array('Academic year created successfully')));
        }else{
           session()->flash('success_messages',array('Academic year created successfully'));
           return redirect()->back();
        }
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make(Input::all(),[
            'year'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        $academic_year = AcademicYear::find($request->get('department_id'));
        $academic_year->name = $request->get('name');
        $academic_year->save();

        if($request->ajax()){
           return response()->json(array('success_messages'=>array('Academic year updated successfully')));
        }else{
           session()->flash('success_messages',array('Academic year updated successfully'));
           return Redirect::back();
        }
    }

    /**
     * Remove the specified department
     */
    public function destroy($id)
    {
        try{
            $academic_year = AcademicYear::findOrFail($id);
            $academic_year->delete();
            session()->flash('success_messages',array('Academic year deleted successfully'));
            return Redirect::back();
        }catch(Exception $e){
            session()->flash('error_messages',array('Unable to get the resource specified in this request'));
            return redirect()->back();
        }
    }
}
