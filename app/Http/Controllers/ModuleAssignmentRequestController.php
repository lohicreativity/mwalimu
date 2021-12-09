<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignmentRequest;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Academic\Actions\ModuleAssignmentRequestAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth, File;

class ModuleAssignmentRequestController extends Controller
{
    /**
     * Display a list of module assignments requests
     */
    public function index()
    {
    	$staff = User::find(Auth::user()->id)->staff;
    	$data = [
           'requests'=>ModuleAssignmentRequest::with(['department','module','programModuleAssignment.moduleAssignments.staff','campusProgram.program','studyAcademicYear.academicYear'])->where('department_id',$staff->department_id)->latest()->paginate(20),
           'staffs'=>Staff::where('department_id',$staff->department_id)->get(),
           'staff'=>$staff
    	];
    	return view('dashboard.academic.module-assignment-requests',$data)->withTitle('Module Assignments Requests');
    }

    /**
     * Store assignment into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'department_id'=>'required',
            'module_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ModuleAssignmentRequestAction)->store($request);

        return Util::requestResponse($request,'Module assignment request created successfully');
    }

    /**
     * Remove the specified assignment
     */
    public function destroy($id)
    {
        try{
            $assignment = ModuleAssignmentRequest::findOrFail($id);
            $assignment->delete();
            return redirect()->back()->with('message','Module assignment request deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
}
