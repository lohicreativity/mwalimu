<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Actions\ProgramModuleAssignmentAction;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ResultPublication;
use App\Models\User;
use App\Utils\Util;
use Validator, DB, Auth;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Models\Award;

class ProgramModuleAssignmentController extends Controller
{
    /**
     * Display program module assignments
     */
    public function index(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campuses'=>Campus::all(),
           'campus'=>Campus::with(['campusPrograms.program.departments','campusPrograms.programModuleAssignments.module'])->find($request->get('campus_id')),
           'staff'=>$staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.program-module-assignments',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Show optional students
     */
    public function showOptionalStudents(Request $request, $id)
    {
        $data = [
           'students'=>ProgramModuleAssignment::find($id)->students,
           'assignment'=>ProgramModuleAssignment::with('module')->find($id),
           'staff'=>User::find(Auth::user()->id)->staff
        ];
        return view('dashboard.academic.optional-students',$data)->withTitle('Optional Students');
    }

    
    /**
     * Download optional students
     */
    public function downloadOptedStudents(Request $request)
    {
          
           $list = ProgramModuleAssignment::find($request->get('assignment_id'))->students;
           $assignment = ProgramModuleAssignment::with('module')->find($request->get('assignment_id'));

            $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=OPTED-STUDENTS-.'.date('Y-m-d').'-'.$assignment->module->code.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

              $callback = function() use ($list) 
          {
              $file_handle = fopen('php://output', 'w');
              fputcsv($file_handle,['NAME','REGISTRATION NUMBER','SEX']);
              foreach ($list as $student) { 

                  fputcsv($file_handle, [$student->first_name.' '.$student->middle_name.' '.$student->surname,$student->registration_number,$student->gender
                    ]);
              }
              fclose($file_handle);
          };

          return response()->stream($callback, 200, $headers);
    }

    /**
     * Allocate options
     */
    public function allocateOptions(Request $request)
    {
        $data = [
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first(),
            'semester'=>Semester::where('status','ACTIVE')->first(),
			'awards'=>Award::all(),
			'request'=>$request
        ];
		
        return view('dashboard.academic.allocate-options',$data)->withTitle('Allocate Options');
    }

    /**
     * Allocate student options
     */
    public function allocateStudentOptions(Request $request)
    {
        $user = User::find(Auth::user()->id)->staff()->with('department')->first();
		$department = Department::with('programs')->find($user->department_id);
        $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();		
		//kwa sababu kuna deadline kwa kila award, inabidi kuallocate kufanyike kwa award pia 
		$deadline = ElectiveModuleLimit::where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->where('award_id',$request->get('program_level_id'))
									   ->where('campus_id',$user->campus_id)->first();
        $now = strtotime(date('Y-m-d'));
        $deadline = strtotime($deadline->deadline);
		if($now <= $deadline){
			return redirect()->back()->with('message','Options cannot be allocated because selection deadline is not due');
		}else{
			return 'imeisha';
		}
		
		$prog = [];
        foreach($department->programs as $program){
            for($yr = 1; $yr <= $program->min_duration; $yr++){
              $campus_program = CampusProgram::where('program_id',$program->id)->first();
              if($campus_program){
              $elective_policy = ElectivePolicy::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$campus_program->id)->where('year_of_study',$yr)->where('semester_id',$request->get('semester_id'))->first();

              $optional_modules = ProgramModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$campus_program->id)->where('year_of_study',$yr)->where('semester_id',$request->get('semester_id'))->where('category','OPTIONAL')->get();
              
              $opt_mod_ids = [];
              foreach($optional_modules as $mod){
                  $opt_mod_ids[] = $mod->id;
              }

              $non_opt_students = Student::whereHas('studentshipStatus',function($query){
                  $query->where('name','ACTIVE');
              })->whereHas('registrations',function($query) use($request,$yr){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$yr)->where('semester_id',$request->get('semester_id'));
              })->whereDoesntHave('options',function($query) use($opt_mod_ids){
                          $query->whereIn('id',$opt_mod_ids);
                      })->where('year_of_study',$yr)->where('campus_program_id',$campus_program->id)->get();

              $opt_students = Student::whereHas('studentshipStatus',function($query){
                  $query->where('name','ACTIVE');
              })->whereHas('registrations',function($query) use($request,$yr){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$yr)->where('semester_id',$request->get('semester_id'));
              })->whereHas('options',function($query) use($opt_mod_ids){
                          $query->whereIn('id',$opt_mod_ids);
                      })->where('year_of_study',$yr)->where('campus_program_id',$campus_program->id)->get();

              $opt_mod_stud = [];
              foreach($optional_modules as $mod){
                  $opt_mod_stud[$mod->id]['count'] = ProgramModuleAssignment::find($mod->id)->students()->count();
                  $opt_mod_stud[$mod->id]['perc'] = $opt_mod_stud[$mod->id]['count']/(count($opt_students)+count($non_opt_students))*100;

              }


              $skip = count($optional_modules) != 0? intdiv(count($non_opt_students),count($optional_modules)) : 0;
              $remainder = count($optional_modules) != 0? count($non_opt_students)%count($optional_modules) : 0;
              $studCount = 0;
                 
              foreach($optional_modules as $key=>$module){
                  if(($opt_mod_stud[$module->id]['count'] != 0 && $opt_mod_stud[$module->id]['perc'] >= 50) || count($opt_students) == 0){
                      foreach($non_opt_students as $stKey=>$student){
                         
                         if(Student::find($student->id)->options()->whereIn('id',$opt_mod_ids)->count() < $elective_policy->number_of_options){
                                $program_mod_assign = ProgramModuleAssignment::find($module->id);
                                $program_mod_assign->students()->attach([$student->id]);
                         }
                      }
                  }else{
                      $count = 0;
                      foreach($non_opt_students as $stKey=>$student){
                         if($key == (count($optional_modules)-1)){
                            $skip = $skip + $remainder;
                         }
                         
                         if(Student::find($student->id)->options()->whereIn('id',$opt_mod_ids)->count() < $elective_policy->number_of_options){
                            if($count <= $skip){
                                $program_mod_assign = ProgramModuleAssignment::find($module->id);
                                $program_mod_assign->students()->attach([$student->id]);
                                $count += 1;
                            }
                         }
                      }
                  }
                }
              } 
            }
          }
        
        
        return redirect()->back()->with('message','Options allocated successfully');
        

    }

    /**
     * Assign program modules
     */
    public function assignModules(Request $request, $ac_year_id,$campus_prog_id)
    {
      $assignments = ProgramModuleAssignment::with(['module','semester'])->where('study_academic_year_id',$ac_year_id)->where('campus_program_id',$campus_prog_id)->get();
      $staff = User::find(Auth::user()->id)->staff;
      $moduleIds = [];
      foreach ($assignments as $key => $assign) {
        $moduleIds[] = $assign->module->id;
      }
    	$campus_program = CampusProgram::with('program')->find($campus_prog_id);
    	if(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL8'){
           $modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 8')->OrWhere('name','LIKE','NTA level 7');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 8')->OrWhere('name','LIKE','NTA level 7');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL7'){
           $modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 7');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 7');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL6'){
           $modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 6')->OrWhere('name','LIKE','NTA level 5');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 6')->OrWhere('name','LIKE','NTA level 5');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL5'){
           $modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 5');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 5');
                      })->get();
    	}elseif(Util::stripSpacesUpper($campus_program->program->ntaLevel->name) == 'NTALEVEL4'){
           $modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
           	              $query->where('name','LIKE','NTA level 4');
                      })->whereNotIn('id',$moduleIds)->get();
           $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereHas('ntaLevel',function($query){
                          $query->where('name','LIKE','NTA level 4');
                      })->get();
    	}else{
    		$modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->whereNotIn('id',$moduleIds)->get();
        $inclusive_modules = Module::whereHas('departments',function($query) use($staff){
                            $query->where('campus_id',$staff->campus_id);
                      })->get();
    	}
    	$data = [
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($ac_year_id),
            'campus_program'=>$campus_program,
            'modules'=>$modules,
            'inclusive_modules'=>$inclusive_modules,
            'semesters'=>Semester::all(),
            'assignments'=>$assignments,
            'staff'=>$staff
    	];
    	return view('dashboard.academic.assign-program-modules',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Assign previous modules
     */
    public function assignPreviousModules(Request $request, $ac_year_id, $campus_prog_id)
    {
         DB::beginTransaction();
         $academic_year = StudyAcademicYear::latest()->take(1)->skip(1)->first();
         if(!$academic_year){
             DB::rollback();
             return redirect()->back()->with('error','No previous study academic year');
         }
         $assignments = ProgramModuleAssignment::whereHas('studyAcademicYear',function($query) use ($academic_year){
                  $query->where('id',$academic_year->id);
         })->get();

         if(count($assignments) == 0){
             DB::rollback();
             return redirect()->back()->with('error','No previous study programme module assignments');
         }
         
         foreach ($assignments as $key => $assignment){
            $assign = new ProgramModuleAssignment;
            $assign->semester_id = $assignment->semester_id;
            $assign->campus_program_id = $campus_prog_id;
            $assign->study_academic_year_id = $ac_year_id;
            $assign->module_id = $assignment->module_id;
            $assign->year_of_study = $assignment->year_of_study;
            $assign->category = $assignment->category;
            $assign->type = $assignment->type;
            $assign->course_work_min_mark = $assignment->course_work_min_mark;
            $assign->course_work_percentage_pass = $assignment->course_work_percentage_pass;
            $assign->course_work_pass_score = $assignment->course_work_pass_score;
            $assign->final_min_mark = $assignment->final_min_mark;
            $assign->final_percentage_pass = $assignment->final_percentage_pass;
            $assign->final_pass_score = $assignment->final_pass_score;
            $assign->module_pass_mark = $assignment->module_pass_mark;
            $assign->save();
         }
         DB::commit();

         return redirect()->back()->with('message','Programme module assignment completed successfully');
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
		
		$module = Module::find($request->get('module_id'));
		
		if($module->course_work_based == 1 && $request->get('course_work_min_mark') == 0){
			return redirect()->back()->with('error','Coursework minimum mark cannot be zero');
		}


        return (new ProgramModuleAssignmentAction)->store($request);

        //return Util::requestResponse($request,'Program module assignment created successfully');
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

        $program_mod_assign = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));
        if($program_mod_assign->category == 'OPTIONAL'){
            if(ProgramModuleAssignment::find($request->get('program_module_assignment_id'))->students()->count() != 0 && $program_mod_assign->category != $request->get('category')){
               return redirect()->back()->with('error','This optional module already has students');
            }
        }

        if(ResultPublication::where('semester_id',$request->get('semester_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','PUBLISHED')->count() != 0){
             return redirect()->back()->with('error','Unable to edit programme module. Results already published');
        }
        
        // if(ModuleAssignment::whereNotNull('final_upload_status')->where('program_module_assignment_id',$request->get('program_module_assignment_id'))->count() != 0){
        //      return redirect()->back()->with('error','Final marks already uploaded');
        // }

        (new ProgramModuleAssignmentAction)->update($request);

        return Util::requestResponse($request,'Program module assignment updated successfully');
    }

    /**
     * Remove the specified program module assignment
     */
    public function destroy($id)
    {
        try{
            $program = ProgramModuleAssignment::with(['moduleAssignments'=>function($query){
                 $query->where('course_work_process_status','PROCESSED');
            }])->findOrFail($id);
            if(count($program->moduleAssignments) != 0){
                return redirect()->back()->with('error','Cannot delete module with coursework');
            }
            $program->delete();
            return redirect()->back()->with('message','Program module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

}
