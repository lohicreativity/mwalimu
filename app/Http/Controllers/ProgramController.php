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
    public function index(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
      if(Auth::user()->hasRole('hod')){
        if($request->has('query')){
            $programs = Program::whereHas('departments',function($query) use($staff){
             $query->where('id',$staff->department_id);
          })->with(['departments','ntaLevel','award'])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->orderBy('code')->orderBy('nta_level_id',$request->get('nta_level'))->paginate(20);
        }else{
          $programs = Program::whereHas('departments',function($query) use($staff){
             $query->where('id',$staff->department_id);
          })->with(['departments','ntaLevel','award'])->orderBy('code')->paginate(20);
        }
      }else{
          if($request->has('query')){
            $programs = Program::with(['departments','ntaLevel','award'])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->orderBy('nta_level_id',$request->get('nta_level'))->paginate(20);
          }else{
             $programs = Program::with(['department','ntaLevel','award'])->orderBy('code')->paginate(20);
          }
      }
    	$data = [
           'programs'=>$programs,
           'departments'=>Department::whereHas('campuses',function($query) use($staff){
                 $query->where('id',$staff->campus_id);
            })->get(),
           'nta_levels'=>NTALevel::where('name','!=','NTA Level 7')->get(),
           'awards'=>Award::all(),
           'request'=>$request,
           'staff'=>$staff
    	];
    	return view('dashboard.academic.programs',$data)->withTitle('Programmes');
    }

    /**
     * Store program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'code'=>'required'
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
