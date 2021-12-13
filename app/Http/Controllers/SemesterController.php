<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Actions\SemesterAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class SemesterController extends Controller
{
    /**
     * Display a list of semesters
     */
    public function index()
    {
    	$data = [
           'semesters'=>Semester::paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.semesters',$data)->withTitle('Semesters');
    }

    /**
     * Store semester into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:semesters',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new SemesterAction)->store($request);

        return Util::requestResponse($request,'Semester created successfully');
    }

    /**
     * Update specified semester
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new SemesterAction)->update($request);

        return Util::requestResponse($request,'Semester updated successfully');
    }

        /**
     * Activate semester
     */
    public function activate($id)
    {
        try{
            $semester = Semester::findOrFail($id);
            $semester->status = 'ACTIVE';
            $semester->save();

            Semester::where('id','!=',$semester->id)->update(['status'=>'INACTIVE']);

            return redirect()->back()->with('message','Semester activated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Deactivate semester
     */
    public function deactivate($id)
    {
        try{
            $semester = Semester::findOrFail($id);
            $semester->status = 'INACTIVE';
            $semester->save();

            return redirect()->back()->with('message','Semester deactivated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified semester
     */
    public function destroy($id)
    {
        try{
            $semester = Semester::findOrFail($id);
            $semester->delete();
            return redirect()->back()->with('message','Semester deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
