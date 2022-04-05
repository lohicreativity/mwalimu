<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Campus;
use App\Domain\Application\Actions\ApplicationWindowAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\DateMaker;
use Validator, Auth;

class ApplicationWindowController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index(Request $request)
    {
    	$data = [
           'windows'=>ApplicationWindow::with(['campus','intake'])->paginate(20),
           'intakes'=>Intake::all(),
           'campuses'=>Campus::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.application.application-windows',$data)->withTitle('Application Windows');
    }

    /**
     * Store award into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'begin_date'=>'required',
            'end_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ApplicationWindow::where('intake_id',$request->get('intake_id'))->where('campus_id',$request->get('campus_id'))->where('begin_date','<=',DateMaker::toDBDate($request->get('begin_date')))->whereYear('end_date','>=',DateMaker::toDBDate($request->get('begin_date')))->count() != 0){
            return redirect()->back()->with('error','You cannot create more than one application window in the same campus and intake');
        }

        if(strtotime($request->get('begin_date')) > strtotime($request->get('end_date'))){
            return redirect()->back()->with('error','End date cannot be less than begin date');
        }elseif(strtotime($request->get('begin_date')) < strtotime(now()->format('Y-m-d'))){
            return redirect()->back()->with('error','Begin date cannot be less than today date');
        }


        (new ApplicationWindowAction)->store($request);

        return Util::requestResponse($request,'Application window created successfully');
    }

    /**
     * Update specified award
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'begin_date'=>'required',
            'end_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime($request->get('begin_date')) > strtotime($request->get('end_date'))){
            return redirect()->back()->with('error','End date cannot be less than begin date');
        }elseif(strtotime($request->get('begin_date')) < strtotime(now()->format('Y-m-d'))){
            return redirect()->back()->with('error','Begin date cannot be less than today\'s date');
        }


        (new ApplicationWindowAction)->update($request);

        return Util::requestResponse($request,'Application window updated successfully');
    }

    /**
     * Display asssigned programs
     */
    public function showPrograms(Request $request)
    {
        $campusPrograms = CampusProgram::where('campus_id',$request->get('campus_id'))->get();
        $campusProgramIds = [];
        foreach($campusPrograms as $prog){
            $campusProgramIds[] = $prog->id;
        }
        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$request->get('campus_id'))->get(),
           'campuses'=>Campus::all(),
           'campusPrograms'=>CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get(),
           'campus'=>$request->has('campus_id')? Campus::find($request->get('campus_id')) : null,
           'staff'=>User::find(Auth::user()->id)->staff
        ];
        return view('dashboard.application.assign-application-window-campus-programs',$data)->withTitle('Application Window Campus Programs');
    }

    /**
     * Update asssigned programs
     */
    public function updatePrograms(Request $request)
    {
        $programs = CampusProgram::all();
        $window = ApplicationWindow::find($request->get('application_window_id'));
        $programIds = [];
        foreach ($programs as $program) {
            if($request->has('window_'.$window->id.'_program_'.$program->id)){
                $programIds[] = $request->get('window_'.$window->id.'_program_'.$program->id);
            }
        }

        if(count($programIds) == 0){
            return redirect()->back()->with('error','Please select programs to assign');
        }else{
            $window->campusPrograms()->sync($programIds);

            return redirect()->back()->with('message','Campus programs assigned successfully');
        }
    }

        /**
     * Activate window
     */
    public function activate($id)
    {
        try{
            $window = ApplicationWindow::findOrFail($id);
            $window->status = 'ACTIVE';
            $window->save();

            ApplicationWindow::where('campus_id',$window->campus_id)->where('intake_id',$window->intake_id)->where('id','!=',$id)->update(['status','INACTIVE']);

            return redirect()->back()->with('message','Application window activated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Deactivate window
     */
    public function deactivate($id)
    {
        try{
            $window = ApplicationWindow::findOrFail($id);
            $window->status = 'INACTIVE';
            $window->save();

            return redirect()->back()->with('message','Application window deactivated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified award
     */
    public function destroy(Request $request, $id)
    {
        try{
            $window = ApplicationWindow::findOrFail($id);
            $window->delete();
            return redirect()->back()->with('message','Application window deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
