<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Application\Actions\EntryRequirementAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class EntryRequirementController extends Controller
{
    /**
     * Display a list of levels
     */
    public function index(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
      $approving_status = ApplicantProgramSelection::where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING')->count();
    	$data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                     $query->where('application_window_id',$request->get('application_window_id'));
              })->with('program')->where('campus_id',$staff->campus_id)->get(),
           'cert_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
                    $query->where('name','LIKE','%4%');
           })->whereHas('applicationWindows',function($query) use($request){
                     $query->where('id',$request->get('application_window_id'));
              })->with('program')->where('campus_id',$staff->campus_id)->get(),
           'diploma_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
                    $query->where('name','LIKE','%6%');
           })->whereHas('applicationWindows',function($query) use($request){
                     $query->where('id',$request->get('application_window_id'));
              })->with('program')->where('campus_id',$staff->campus_id)->get(),
           'degree_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
                    $query->where('name','LIKE','%7%')->orWhere('name','LIKE','%8%');
           })->whereHas('applicationWindows',function($query) use($request){
                     $query->where('id',$request->get('application_window_id'));
              })->with('program')->where('campus_id',$staff->campus_id)->get(),
           'entry_requirements'=>$request->get('query')? EntryRequirement::whereHas('campusProgram.program',function($query) use($request){
                    $query->where('name',$request->get('query'));
              })->with(['campusProgram.program.award'])->where('application_window_id',$request->get('application_window_id'))->latest()->paginate(20) : EntryRequirement::with(['campusProgram.program.award'])->where('application_window_id',$request->get('application_window_id'))->latest()->paginate(20),
           'subjects'=>NectaResult::distinct()->get(['subject_name']),
           'equivalent_subjects'=>NacteResult::distinct()->get('subject'),
           'staff'=>$staff,
           'selection_run'=>$approving_status == 0? false : true,
           'request'=>$request
    	];
    	return view('dashboard.application.entry-requirements',$data)->withTitle('Entry Requirements');
    }

    /**
     * Show capacity
     */
    public function showCapacity(Request $request)
    {   
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'entry_requirements'=>EntryRequirement::with(['campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->get(),
           'request'=>$request
        ];
        return view('dashboard.application.entry-requirements-capacity',$data)->withTitle('Entry Capacity');
    }

    /**
     * Update Capacity
     */
    public function updateCapacity(Request $request)
    {
         $entry_requirements = EntryRequirement::with(['campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->get();

         foreach($entry_requirements as $req){
             if($request->get('requirement_'.$req->id)){
                 $requirement = EntryRequirement::find($req->id);
                 $requirement->max_capacity = $request->get('requirement_'.$req->id);
                 $requirement->save();
             }
         }
         return redirect()->back()->with('message','Maximum capacities updated successfully');
    }

    /**
     * Store entry requirement into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'pass_subjects'=>'required',
            'pass_grade'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        return (new EntryRequirementAction)->store($request);

        return Util::requestResponse($request,'Entry requirement created successfully');
    }

    /**
     * Store as previous
     */
    public function storeAsPrevious(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $prev_window = ApplicationWindow::where('campus_id',$staff->campus_id)->latest()->offset(1)->first();
        if(!$prev_window){
            return redirect()->back()->with('error','No previous application window');
        }
        $reqs = EntryRequirement::where('application_window_id',$prev_window->id)->get();
        $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','ACTIVE')->first();
        foreach($reqs as $req){
            $requirement = new EntryRequirement;
            $requirement->campus_program_id = $req->campus_program_id;
            $requirement->application_window_id = $application_window->id;
            $requirement->equivalent_gpa = $req->equivalent_gpa;
            $requirement->equivalent_pass_subjects = $req->equivalent_pass_subjects;
            $requirement->equivalent_average_grade = $req->equivalent_average_grade;
            $requirement->open_equivalent_gpa = $req->open_equivalent_gpa;
            $requirement->open_equivalent_pass_subjects = $req->open_equivalent_pass_subjects;
            $requirement->open_equivalent_average_grade = $req->open_equivalent_average_grade;
            $requirement->principle_pass_points = $req->principle_pass_points;
            $requirement->min_principle_pass_points = $req->min_principle_pass_points;
            $requirement->principle_pass_subjects = $req->principle_pass_subjects;
            $requirement->subsidiary_pass_subjects = $req->subsidiary_pass_subjects;
            $requirement->pass_subjects = $req->pass_subjects;
            $requirement->pass_grade = $req->pass_grade;
            $requirement->award_level = $req->award_level;
            $requirement->nta_level = $req->nta_level;
            $requirement->exclude_subjects = $req->exclude_subjects;
            $requirement->must_subjects = $req->must_subjects;
            $requirement->other_must_subjects = $req->other_must_subjects;
            $requirement->other_advance_must_subjects = $req->other_advance_must_subjects;
            $requirement->advance_exclude_subjects = $req->advance_exclude_subjects;
            $requirement->advance_must_subjects = $req->advance_must_subjects;
            $requirement->subsidiary_subjects = $req->subsidiary_subjects;
            $requirement->principle_subjects = $req->principle_subjects;
            $requirement->max_capacity = $req->max_capacity;
            $requirement->group_id = $req->group_id;
            $requirement->save();
        }

        return redirect()->back()->with('message','Entry requirements created successfully');
    }

    /**
     * Update specified entry requirement
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'pass_subjects'=>'required',
            'pass_grade'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new EntryRequirementAction)->update($request);

        return Util::requestResponse($request,'Entry requirement updated successfully');
    }

    /**
     * Remove the specified entry requirement
     */
    public function destroy(Request $request, $id)
    {
        try{
               $requirement = EntryRequirement::findOrFail($id);
               $requirement->delete();
               return redirect()->back()->with('message','Entry requirement deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
