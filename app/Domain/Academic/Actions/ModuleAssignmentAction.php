<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Academic\Repositories\Interfaces\ModuleAssignmentInterface;
use App\Mail\StaffModuleAssigned;
use App\Models\User;
use App\Utils\Util;
use Auth, Mail;

class ModuleAssignmentAction implements ModuleAssignmentInterface{
	
	public function store(Request $request){
                $program = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));
                $staff = User::find(Auth::user()->id)->staff;
                $assigned_staff = Staff::find($request->get('staff_id'));
                $module = Module::with('departments')->find($request->get('module_id'));
                if($program->policy_assigned == 0){
                   return redirect()->back()->with('error','Examination policy not set for this programme');
                }
		        $assignment = new ModuleAssignment;
                $assignment->staff_id = $request->get('staff_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->program_module_assignment_id = $request->get('program_module_assignment_id');
                $assignment->assigned_by_user_id = Auth::user()->id;

                if($staff->department_id == $assigned_staff->department_id && $staff->campus_id == $assigned_staff->campus_id){
                    $assignment->confirmed = 1;
                }
                
                $assignment->save();
                $assign = ModuleAssignment::with(['staff','module','studyAcademicYear','programModuleAssignment.semester'])->where('confirmed',1)->find($assignment->id);
                if($assign){
                $user = User::find($assigned_staff->user_id);
                    try{
                       Mail::to($user)->queue(new StaffModuleAssigned($assign));
                    }catch(\Exception $e){}
                }
                return redirect()->back()->with('message','Module assigned to staff successfully');
	}

	public function update(Request $request){
	            $assignment = ModuleAssignment::find($request->get('module_assignment_id'));
                $assignment->staff_id = $request->get('staff_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->program_module_assignment_id = $request->get('program_module_assignment_id');
                $assignment->assigned_by_user_id = Auth::user()->id;
                $assignment->save();
	}


    public function acceptConfirmation(Request $request,$id)
    {
        $assignment = ModuleAssignment::find($id);
        $assignment->confirmed = 1;
        $assignment->save();

        $assign = ModuleAssignment::with(['staff','module','studyAcademicYear','programModuleAssignment.semester'])->find($assignment->id);
        $assigned_staff = Staff::find($assign->staff_id);
        $user = User::find($assigned_staff->user_id);
        try{
           Mail::to($user)->queue(new StaffModuleAssigned($assign));
        }catch(\Exception $e){}
    }

    public function rejectConfirmation(Request $request,$id)
    {
        $assignment = ModuleAssignment::find($id);
        $assignment->confirmed = 0;
        $assignment->save();
    }
}