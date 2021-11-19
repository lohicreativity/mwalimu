<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Actions\CampusProgramAction;
use App\Utils\Util;
use Validator;

class CampusProgramController extends Controller
{
     /**
     * Display a list of programs
     */
    public function index($id)
    {
    	try{
	    	$data = [
	           'campus_programs'=>CampusProgram::with('program')->where('campus_id',$id)->paginate(20),
	           'campus'=>Campus::findOrFail($id),
	           'programs'=>Program::all()
	    	];
	    	return view('dashboard.academic.campus-programs',$data)->withTitle('Campus Programs');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Store campus program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'regulator_code'=>'required|unique:campus_program',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CampusProgramAction)->store($request);

        return Util::requestResponse($request,'Campus program created successfully');
    }

    /**
     * Update specified campus program
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'regulator_code'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CampusProgramAction)->update($request);

        return Util::requestResponse($request,'Campus program updated successfully');
    }

    /**
     * Remove the specified program
     */
    public function destroy(Request $request, $id)
    {
        try{
            $program = program::findOrFail($id);
            $program->delete();
            return redirect()->back()->with('message','Campus program deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
