<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignmentRequest;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\StudyAcademicYear;
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
    public function index(Request $request)
    {
    	$staff = User::find(Auth::user()->id)->staff()->with(['department'])->first();
    	$data = [
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'requests'=>ModuleAssignmentRequest::whereHas('programModuleAssignment.module.departments',function($query) use ($staff){
                    $query->where('id',$staff->department_id);
               })->with(['programModuleAssignment.moduleAssignments.staff','campusProgram.program','studyAcademicYear.academicYear','user.staff.campus'])->latest()->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->paginate(20),
           'staffs'=>Staff::with(['campus','designation'])->where('campus_id',$staff->campus_id)->get(),
           'request'=>$request,
           'staff'=>$staff
    	];
		
		return $requests;
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

        if(ModuleAssignmentRequest::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$request->get('campus_program_id'))->where('program_module_assignment_id',$request->get('program_module_assignment_id'))->where('department_id',$request->get('department_id'))->count() != 0){
            return redirect()->back()->with('error','Facilitator already requested');
        }

        $program_module_assignment = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));
        if($program_module_assignment->category == 'OPTIONAL'){
            if(ElectivePolicy::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$request->get('campus_program_id'))->where('semester_id',$program_module_assignment->semester_id)->where('year_of_study',$program_module_assignment->year_of_study)->count() == 0){
                return redirect()->back()->with('error','No elective policy defined for this academic year');
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
