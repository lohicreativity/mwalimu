<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Campus;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Application\Actions\ApplicationWindowAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\DateMaker;
use Validator, Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\ApplicationBatch;

class ApplicationWindowController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $windows = ApplicationWindow::with(['campus','intake'])
            //    ->where('YEAR(end_date)', '=', now()->format('Y'))
                ->whereBetween(DB::raw('YEAR(end_date)'), [now()->format('Y') - 1, now()->format('Y')])
                ->latest('end_date')
               ->get();
        }else{
            $windows = ApplicationWindow::with(['campus','intake'])->where('campus_id',$staff->campus_id)
            //    ->where('YEAR(end_date)', '=', now()->format('Y'))
                ->whereBetween(DB::raw('YEAR(end_date)'), [now()->format('Y') - 1, now()->format('Y')])
                ->latest('end_date')
               ->get();
        }
        
    	$data = [
           'windows'=>$windows,
           'intakes'=>Intake::all(),
           'campuses'=>Campus::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request,
           'window_applicants' =>  Applicant::select(DB::raw('DISTINCT(application_window_id)', 'application_window_id'))->pluck('application_window_id'),
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

        if(date('Y-m-d', strtotime($request->get('begin_date'))) > date('Y-m-d', strtotime($request->get('end_date')))){
            return redirect()->back()->with('error','End date cannot be less than begin date');
        }elseif(date('Y-m-d', strtotime($request->get('begin_date'))) < date('Y-m-d', strtotime(now()->format('Y-m-d')))){
            return redirect()->back()->with('error',"Begin date cannot be less than today's date");
        }

        if(!empty($request->get('bcs_end_date')) || !empty($request->get('msc_end_date'))){
            if(date('Y-m-d', strtotime($request->get('begin_date'))) > date('Y-m-d', strtotime($request->get('bcs_end_date'))) || date('Y-m-d', strtotime($request->get('begin_date'))) > date('Y-m-d', strtotime($request->get('msc_end_date')))){
                return redirect()->back()->with('error','End date cannot be less than begin date');
            }elseif(date('Y-m-d', strtotime($request->get('begin_date'))) < date('Y-m-d', strtotime($request->get('bcs_end_date'))) || date('Y-m-d', strtotime($request->get('begin_date'))) < date('Y-m-d', strtotime($request->get('msc_end_date')))){
                return redirect()->back()->with('error',"Begin date cannot be less than today's date");
            }
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

        if($request->get('campus_id') != session('staff_campus_id')){
                return redirect()->back()->with('error','You cannot update this application window because it does not belong to your campus');
        }

        if(date('Y-m-d', strtotime($request->get('begin_date'))) > date('Y-m-d', strtotime($request->get('end_date'))) || date('Y-m-d',strtotime($request->get('begin_date'))) > date('Y-m-d',strtotime($request->get('bsc_end_date')))
            || date('Y-m-d',strtotime($request->get('begin_date'))) > date('Y-m-d',strtotime($request->get('msc_end_date')))){
            return redirect()->back()->with('error','End date cannot be less than begin date');
        }

        

        // elseif(strtotime($request->get('begin_date')) < strtotime(now()->format('Y-m-d'))){
        //     return redirect()->back()->with('error','Begin date cannot be less than today\'s date');
        // }


        (new ApplicationWindowAction)->update($request);

        return Util::requestResponse($request,'Application window updated successfully');
    }

    /**
     * Display asssigned programs
     */
    public function showPrograms(Request $request)
    {
        if($request->get('query')){
           $window = ApplicationWindow::with(['intake','campusPrograms.program'=>function($query) use($request){
                  $query->where('name','LIKE','%'.$request->get('query').'%');
           }])->find($request->get('application_window_id'));
        }else{
           $window = ApplicationWindow::with(['intake','campusPrograms'])->find($request->get('application_window_id'));
        }
        if(!$window){
            return redirect()->back()->with('error','No application window specified');
        }
        $staff = User::find(Auth::user()->id)->staff;
        if(Auth::user()->hasRole('admission-officer')){
            if($window->campus_id != $staff->campus_id){
                return redirect()->back()->with('error','You cannot access offered programmes because you do not belong to this campus');
            }
        }

        $data = [
           'application_windows'=>ApplicationWindow::with(['intake','campus'])->latest()->get(),
           'window'=>$window,
           'campuses'=>Campus::all(),
           'campusPrograms'=>$window? CampusProgram::with(['program'=>function($query){
                $query->orderBy('name','ASC');
           }])->where('campus_id',$window->campus_id)->get() : null,
           'campus'=>$window? Campus::find($window->campus_id) : null,
           'staff'=>$staff,
           'request'=>$request
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
            $window = ApplicationWindow::with('campusPrograms')->findOrFail($id);

            $campus_programs_count = CampusProgram::whereHas('entryRequirements',function($query) use($window){
                $query->where('application_window_id',$window->id);
            })->count();

            if($campus_programs_count < count($window->campusPrograms)){
                return redirect()->back()->with('error','You cannot activate the window because some offered programmes are missing entry requirements');
            }

            if($window->campus_id != session('staff_campus_id')){
                return redirect()->back()->with('error','You cannot activate the window because it does not belong to your campus');
            }

            if(count($window->campusPrograms) == 0){
                return redirect()->back()->with('error','You cannot activate the window because no offered programmes have been set');
            }
            $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($window){
                   $query->where('year','LIKE','%'.Carbon::parse($window->begin_date)->format('Y').'/%');
            })->first();

            $amount = FeeAmount::whereHas('feeItem.feeType',function($query){
                  $query->where('name','LIKE','%Application Fee%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();

            if(!$amount){
                return redirect()->back()->with('error','You cannot activate the window because application fee has not been set');
            }

            if(ApplicationBatch::where('application_window_id',$window->id)->count() == 0){
                 return redirect()->back()->with('error','You cannot activate the window because no batch has been defined');               
            }


            $window->status = 'ACTIVE';
            $window->save();

            ApplicationWindow::where('campus_id',$window->campus_id)->where('id','!=',$id)->update(['status'=>'INACTIVE']);

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
            if($window->campus_id != session('staff_campus_id')){
                return redirect()->back()->with('error','You cannot deactivate this application window because it does not belong to your campus');
            }
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
            $window = ApplicationWindow::with('campusPrograms')->findOrFail($id);
            if($window->campus_id != session('staff_campus_id')){
                return redirect()->back()->with('error','You cannot delete this application window because it does not belong to your campus');
            }
            if(count($window->campusPrograms) != 0){
                return redirect()->back()->with('error','You cannot delete this application window because it has already been used');
            }
            $window->delete();
            return redirect()->back()->with('message','Application window deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
