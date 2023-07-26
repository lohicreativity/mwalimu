<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicationBatch;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Campus;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Application\Actions\ApplicationBatchAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\DateMaker;
use Validator, Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Academic\Models\Award;
use App\Domain\Application\Models\Applicant;

class ApplicationBatchController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $windows = null;
        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $windows = ApplicationWindow::where('status','ACTIVE')->get();

        }else{
            $windows = ApplicationWindow::where('campus_id', session('staff_campus_id'))->where('status','ACTIVE')->latest()->get();
        }

        $batches = [];
        foreach($windows as $window){

            $batches[] = ApplicationBatch::where('application_window_id',$window->id)->get();
        }
        //return $batches;
    	$data = [
           'windows'=>$windows,
           'intakes'=>Intake::all(),
           'campuses'=>Campus::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request,
           'awards'=>Award::all(),
           'batches'=>$batches
    	];
    	return view('dashboard.application.application-batches',$data)->withTitle('Application Batches');
    }

    /**
     * Store award into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'program_level_id'=>'required',
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
        $window = ApplicationWindow::where('campus_id',session('staff_campus_id'))->latest()->first();
        if($window){
            $current_batch = ApplicationBatch::where('application_window_id',$window->id)->where('program_level_id', $request->get('program_level_id'))->latest()->first();
        
            if($current_batch){
                if(Applicant::where('batch_id', $current_batch->id)){
                    return redirect()->back()->with('error','Cannot be created because a new application batch has already been created');
                }
            }
        }else{
            return redirect()->back()->with('error','Application window for this campus not already created');
        }

        (new ApplicationBatchAction)->store($request);

        return Util::requestResponse($request,'Application batch created successfully');
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
     * Remove the specified award
     */
    public function destroy(Request $request, $id)
    {
        try{
            $batch = ApplicationBatch::findOrFail($id);
            $applicant = Applicant::where('batch_id',$batch->id)->count();
            if($applicant > 0){
                return redirect()->back()->with('error','Batch cannot be deleted. Applicant already assigned.');
            }
            $batch->delete();
            return redirect()->back()->with('message','Application batch deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
