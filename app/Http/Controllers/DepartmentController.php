<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;
use App\Domain\Settings\Models\UnitCategory;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Actions\DepartmentAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class DepartmentController extends Controller
{
    /**
     * Display a list of departments
     */
    public function index()
    {
      $staff = User::find(Auth::user()->id)->staff;

    	$data = [
           'unit_categories'=>UnitCategory::all(),
           'all_departments'=>Department::all(),
           'campuses'=>Campus::all(),
           'staff'=> $staff,
           'departments' => Department::whereHas('campuses',function($query) use($staff){
               $query->where('campuses.id', $staff->id);
            })
            // ->with('unitCategory','campuses')
            ->get()
         //   'departments'=>Department::with('unitCategory','campuses')
         //   ->paginate(20)
    	];

      return $data['departments'];

    	// return view('dashboard.academic.departments',$data)->withTitle('Departments');
    }

    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:departments',
            'abbreviation'=>'required|unique:departments',
            'description'=>'required',
            'campuses'=>'required',
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
            'abbreviation'=>'required',
            'description'=>'required',
            'campuses'=>'required',
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
            if(Module::where('department_id',$department->id)->count() != 0 || Program::where('department_id',$department->id)->count() != 0){
               return redirect()->back()->with('message','Department cannot be deleted because it has modules or programs');
            }else{
               $department->delete();
               return redirect()->back()->with('message','Department deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
