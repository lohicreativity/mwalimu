<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Academic\Actions\ModuleAssignmentAction;
use App\Utils\Util;
use Validator, Auth;

class ModuleAssignmentController extends Controller
{
	/**
	 * Display a list of staffs to assign modules
	 */
	public function index()
	{
		$data = [
           'assignments'=>ModuleAssignment::with(['staff','studyAcademicYear.academicYear','module'])->latest()->paginate(20),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'modules'=>Module::all(),
           'staffs'=>Staff::all()
		];
		return view('dashboard.academic.assign-staff-modules',$data)->withTitle('Staff Module Assignment');
	}

  /**
     * Disaplay staff assigned modules 
     */
    public function showStaffAssignedModules(Request $request)
    {
        $staff = Staff::with(['assignedModules.studyAcademicYear'=>function($query){
                   $query->where('status','ACTIVE');
            }])->where('user_id',Auth::user()->id)->first();
        $data = [
           'staff'=>$staff,
           'assignments'=>$staff? ModuleAssignment::with(['studyAcademicYear.academicYear','module'])->where('staff_id',$staff->id)->latest()->paginate(20) : [],
        ];
        return view('dashboard.academic.staff-assigned-modules',$data)->withTitle('Staff Assigned Modules');
    }

    /**
     * Show assessment plans to assigned staff
     */
    public function showAssessmentPlans(Request $request,$id)
    {
        try{
            $data = [
               'module_assignment'=>ModuleAssignment::with('module')->findOrFail($id),
               'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$id)->get()
            ];
            return view('dashboard.academic.module-assessment-plans',$data)->withTitle('Module Assessment Plans');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Store module assignment into database
     */
    public function store(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'staff_id'=>'required',
            'module_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('staff_id',$request->get('staff_id'))->where('module_id',$request->get('module_id'))->count() != 0){

             if($request->ajax()){
                return response()->json(array('error_messages'=>'Module already assined for this academic year'));
             }else{
                return redirect()->back()->with('error','Module already assined for this academic year');
             }
        }


        (new ModuleAssignmentAction)->store($request);

        return Util::requestResponse($request,'Module assignment created successfully');
    }

    /**
     * Upload module assignment results
     */
    public function showResultsUpload(Request $request,$id)
    {
         $data = [
            'module_assignment'=>ModuleAssignment::with('assessmentPlans','module')->findOrFail($id),
         ];
         return view('dashboard.academic.upload-module-assignment-results',$data)->withTitle('Upload Module Assignment Results');
    }

    /**
     * Upload module assignment results
     */
    public function uploadResults(Request $request)
    {

    }

    /**
     * Remove the specified assignment
     */
    public function destroy(Request $request, $id)
    {
        try{
            $assignment = ModuleAssignment::findOrFail($id);
            $assignment->delete();
            return redirect()->back()->with('message','Module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
