<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a list of departments
     */
    public function index()
    {
    	$data = [
           'departments'=>Department::paginate(20)
    	];
    	return view('dashboard.academic.programs',$data)->withTitle('Departments');
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make(Input::all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new DepartmentAction)->store($request);

        if($request->ajax()){
           return response()->json(array('success_messages'=>array('Department created successfully')));
        }else{
           session()->flash('success_messages',array('Department created successfully'));
           return Redirect::back();
        }
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make(Input::all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        $department = Department::find($request->get('department_id'));
        $department->name = $request->get('name');
        $department->save();

        if($request->ajax()){
           return response()->json(array('success_messages'=>array('Department updated successfully')));
        }else{
           session()->flash('success_messages',array('Department updated successfully'));
           return Redirect::back();
        }
    }

    /**
     * Remove the specified department
     */
    public function destroy($id)
    {
        try{
            $department = Department::findOrFail($id);
            $department->delete();
            session()->flash('success_messages',array('Department deleted successfully'));
            return Redirect::back();
        }catch(Exception $e){
            session()->flash('error_messages',array('Unable to get the resource specified in this request'));
            return redirect()->back();
        }
    }
}
