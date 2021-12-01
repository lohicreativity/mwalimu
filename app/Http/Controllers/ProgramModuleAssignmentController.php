<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Actions\ProgramModuleAssignmentAction;
use App\Utils\Util;
use Validator;

class ProgramModuleAssignmentController extends Controller
{
    /**
     * Display program module assignments
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campuses'=>Campus::with(['campusPrograms.program','campusPrograms.programModuleAssignments.module'])->get()
    	];
    	return view('dashboard.academic.program-module-assignments',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Assign program modules
     */
    public function assignModules(Request $request, $ac_year_id,$campus_prog_id)
    {
      $assignments = ProgramModuleAssignment::with(['module','semester'])->where('study_academic_year_id',$ac_year_id)->where('campus_program_id',$campus_prog_id)->get();
      $moduleIds = [];
      foreach ($assignments as $key => $assign) {
        $moduleIds[] = $assign->module->id;
      }
    	$campus_program = CampusProgram::with('program')->find($campus_prog_id);
    	if(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL8'){
           $modules = Module::whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 8')->OrWhere('name','LIKE','NTA level 7');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 8')->OrWhere('name','LIKE','NTA level 7');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL7'){
           $modules = Module::whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 7');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 7');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL6'){
           $modules = Module::whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 6')->OrWhere('name','LIKE','NTA level 5');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 6')->OrWhere('name','LIKE','NTA level 5');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL5'){
           $modules = Module::whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 5');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 5');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL4'){
           $modules = Module::whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 4');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 4');
                      })->get();
    	}else{
    		$modules = Module::whereNotIn('id',$moduleIds)->get();
        $inclusive_modules = Module::all();
    	}
    	$data = [
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($ac_year_id),
            'campus_program'=>$campus_program,
            'modules'=>$modules,
            'inclusive_modules'=>$inclusive_modules,
            'semesters'=>Semester::all(),
            'assignments'=>$assignments
    	];
    	return view('dashboard.academic.assign-program-modules',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Store program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'year_of_study'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ProgramModuleAssignment::where('module_id',$request->get('module_id'))->where('semester_id',$request->get('semester_id'))->where('year_of_study',$request->get('year_of_study'))->where('campus_program_id',$request->get('campus_program_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
             return redirect()->back()->withInput()->with('error','Module already added in this study academic year');
        }


        (new ProgramModuleAssignmentAction)->store($request);

        return Util::requestResponse($request,'Program module assignment created successfully');
    }

    /**
     * Update specified program
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'year_of_study'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return response()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ProgramModuleAssignmentAction)->update($request);

        return Util::requestResponse($request,'Program module assignment updated successfully');
    }

    /**
     * Remove the specified program module assignment
     */
    public function destroy($id)
    {
        try{
            $program = ProgramModuleAssignment::findOrFail($id);
            $program->delete();
            return redirect()->back()->with('message','Program module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }

}
