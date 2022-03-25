<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\NectaResult;
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
    	$data = [
           'application_windows'=>ApplicationWindow::all(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'campus_programs'=>CampusProgram::with('program')->get(),
           'entry_requirements'=>EntryRequirement::with(['campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->paginate(20),
           'subjects'=>NectaResult::distinct()->get(['subject_name']),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.application.entry-requirements',$data)->withTitle('Entry Requirements');
    }

    /**
     * Show capacity
     */
    public function showCapacity(Request $request)
    {
        $data = [
           'application_windows'=>ApplicationWindow::all(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'entry_requirements'=>EntryRequirement::with(['campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->get()
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


        (new EntryRequirementAction)->store($request);

        return Util::requestResponse($request,'Entry requirement created successfully');
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
