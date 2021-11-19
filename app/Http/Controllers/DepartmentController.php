<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;
use App\Domain\Settings\Models\UnitCategory;
use App\Domain\Settings\Models\UnitCategory;
use App\Domain\Academic\Actions\DepartmentAction;
use App\Utils\Util;
use Validator;

class DepartmentController extends Controller
{
    /**
     * Display a list of departments
     */
    public function index()
    {
    	$data = [
           'departments'=>Department::with('unitCategory')->paginate(20),
           'unit_categories'=>UnitCategory::all(),
           'campuses'=>Campus::all()
    	];
    	return view('dashboard.academic.departments',$data)->withTitle('Departments');
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
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

        return Util::requestResponse($request,'Department created successfully');
    }

    /**
     * Update specified department
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new DepartmentAction)->update($request);

        return Util::requestResponse($request,'Department updated successfully');
    }

    /**
     * Remove the specified department
     */
    public function destroy(Request $request, $id)
    {
        try{
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->back()->with('message','Department deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
