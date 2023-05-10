<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Award;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Actions\ProgramAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth, DB;

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
                })->with(['departments','ntaLevel','award'])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->orderBy('code')->orderBy('nta_level_id',$request->get('nta_level'))->get();
            }else{
                $programs = Program::whereHas('departments',function($query) use($staff){
                    $query->where('id',$staff->department_id);
                })->with(['departments'=>function($query) use($staff){
                        $query->where('campus_id',$staff->campus_id);
                    },'ntaLevel','award','campusPrograms'=>function($query) use($staff){
                        $query->where('campus_id',$staff->campus_id);
                    },])->orderBy('code')->get();
            }
    
			$departments = Department::whereHas('campuses',function($query) use($staff){
                 $query->where('id',$staff->campus_id);
            })->where('id', $staff->department_id)->get();
			
        }elseif(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $departments = Department::whereHas('campuses')->with('campuses')->get();
            $programs = Program::whereHas('departments')->with(['departments','ntaLevel','award','campusPrograms'])->orderBy('code')->get();
        }else{
		    $departments = Department::whereHas('campuses',function($query) use($staff){
            $query->where('id',$staff->campus_id);})->get();

          if($request->has('query')){
            $programs = Program::whereHas('departments',function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			})->with(['departments.staffs.user','ntaLevel','award','campusPrograms'=>function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			},'departments'=>function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			}])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->orderBy('nta_level_id',$request->get('nta_level'))->get();
			
		  }else{
             $programs = Program::whereHas('departments',function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			})->with(['departments.staffs.user','ntaLevel','award','campusPrograms'=>function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			},'departments'=>function($query) use($staff){
				$query->where('campus_id',$staff->campus_id);
			}])->orderBy('code')->get();
			
          }
        }

    	$data = [
           'programs'=>$programs,
           'departments'=>$departments,
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
            'code'=>'required',
            'regulator_code'=>'unique:campus_program'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $check_program_department = Program::whereHas('departments', function($query) use($request)
                                            {$query->where('campus_id',$request->get('campus_id'));})->where('code', $request->get('code'))->first();

        if ($check_program_department) {
            return redirect()->back()->with('error','The programme has already been assigned in '.$check_program_department->departments[0]->name);
        } else {
            (new ProgramAction)->store($request);
        }
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

        // added by salim, check if program department exists to avoid duplicates in table program department

        $check_program_department = DB::table('program_department')
        ->where('program_id', $request->get('program_id'))
        ->where('department_id', $request->get('department_id'))
        ->where('campus_id', $request->get('campus_id'))
        ->first();

        if ($check_program_department) {
            return redirect()->back()->with('error','Program department already exists');
        } else {
            (new ProgramAction)->update($request);
        }

        


        return Util::requestResponse($request,'Program updated successfully');
    }

    /**
     * Get by code
     */
    public function getByCode(Request $request)
    {
        $program = Program::where('code',$request->get('code'))->first();
        return response()->json(['program'=>$program]);
    }

    /**
     * Remove the specified program
     */
    public function destroy($id)
    {
        try{
            $program = Program::findOrFail($id);
            
			if(ApplicantProgramSelection::whereHas('campusProgram',function($query) use ($program){
				  $query->where('program_id',$program->id);
			})->count() != 0){
				return redirect()->back()->with('error','The programme cannot be deleted because it has already been used');
			}
			if(ApplicationWindow::whereHas('campusPrograms',function($query) use ($program){
				  $query->where('program_id',$program->id);
			})->count() != 0){
				return redirect()->back()->with('error','The programme cannot be deleted because it has already been used');
			}
			$program->delete();
			CampusProgram::where('program_id',$program->id)->delete();
            return redirect()->back()->with('message','Program deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
}
