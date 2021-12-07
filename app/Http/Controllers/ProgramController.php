<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Actions\ProgramAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ProgramController extends Controller
{
    /**
     * Display a list of programs
     */
    public function index()
    {
    	$data = [
           'programs'=>Program::with(['department','ntaLevel','award'])->latest()->paginate(20),
           'departments'=>Department::all(),
           'nta_levels'=>NTALevel::all(),
           'awards'=>Award::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.programs',$data)->withTitle('programs');
    }

    /**
     * Store program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:programs',
            'code'=>'required|unique:programs'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ProgramAction)->store($request);

        return Util::requestResponse($request,'Program created successfully');
    }

    /**
     * Update specified program
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return response()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ProgramAction)->update($request);

        return Util::requestResponse($request,'Program updated successfully');
    }

    /**
     * Remove the specified program
     */
    public function destroy($id)
    {
        try{
            $program = Program::findOrFail($id);
            $program->delete();
            return redirect()->back()->with('message','Program deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
}
