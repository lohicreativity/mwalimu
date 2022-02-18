<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Actions\ApplicationWindowAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ApplicationWindowController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index(Request $request)
    {
    	$data = [
           'windows'=>ApplicationWindow::paginate(20),
           'intakes'=>Intake::all(),
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
            'capacity'=>'required',
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


        (new ApplicationWindowAction)->store($request);

        return Util::requestResponse($request,'Application window created successfully');
    }

    /**
     * Update specified award
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'capacity'=>'required',
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


        (new ApplicationWindowAction)->update($request);

        return Util::requestResponse($request,'Application window updated successfully');
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
