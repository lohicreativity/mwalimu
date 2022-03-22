<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Graduant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Utils\Util;

class GraduantController extends Controller
{
    /**
     * Run graduants
     */
    public function runGraduants(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campus'=>Campus::find($request->get('campus_id')),
           'campuses'=>Campus::all(),
           'nta_levels'=>NTALevel::get(),
           'request'=>$request
    	];
    	return view('dashboard.academic.run-graduants',$data)->withTitle('Run Graduants');
    }


    /**
     * Sort Graduants
     */
    public function sortGraduants(Request $request)
    {
      if(ResultPublication::where('study_academic_year_id',session('active_academic_year_id'))->where('type','SUPP')->count() == 0){
          return redirect()->back()->with('error','Supplementary results not published');
      }
      if(Appeal::whereHas('moduleAssignment',function($query){
           $query->where('study_academic_year_id',session('active_academic_year_id'));
      })->where('is_attended',0)->where('is_paid',1)->count() != 0){
         return redirect()->back()->with('error','Appeals not attended completely');
      }

      $nta_level = NTALevel::with(['programs'])->find($request->get('nta_level_id'));

      foreach($nta_level->programs as $program){
          	$campus_program = CampusProgram::with('program')->find($request->get('campus_program_id'));
          	$students = Student::with(['annualRemarks','overallRemark'])->whereHas('campusProgram',function($query) use ($program, $request){
                 $query->where('program_id',$program->id)->where('campus_id',$request->get('campus_id'));
            })->where('year_of_study',$program->min_duration)->get();
          	$excluded_list = [];
          	$status = StudentshipStatus::where('name','GRADUANT')->first();
          	foreach($students as $student){
          		if($student->overallRemark){
      	    		if($grad = Graduant::where('student_id',$student->id)->first()){
      	               $graduant = $grad;
      	    		}else{
      	               $graduant = new Graduant;
      	    		}
      	    		$graduant->student_id = $student->id;
      	    		$graduant->overall_remark_id = $student->overallRemark->id;
      	    		$graduant->study_academic_year_id = $request->get('study_academic_year_id');
      	    		$graduant->status = 'GRADUATING';
                $count = 0;
      	    		foreach($student->annualRemarks as $remark){
      	    			if($remark->remark != 'PASS'){
      	    			   $graduant->status = 'EXCLUDED';
      	                   $excluded_list[] = $student;
      	                   break;
      	    			}else{
                     $count++;
                  }
                  if($count >= $program->min_duration){
                     $graduant->status = 'GRADUATING';
                     if($cls = Clearance::where('student_id')->first()){
                        $clearance = $cls;
                     }else{
                        $clearance = new Clearance;
                     }
                     $clearance->student_id = $student->id;
                     $clearance->study_academic_year_id = $request->get('study_academic_year_id');
                     $clearance->save();
                  }else{
                     $graduant->status = 'EXCLUDED';
                     if($remark->remark == 'POSTPONED'){
                       $graduant->reason = 'Postponed Results';
                     }else{
                       $graduant->reason = 'Incomplete Results';
                     }
                  }
      	    		}
      	    		$graduant->save();
          	  }
    	    $student = Student::find($student->id);
    	    $student->studentship_status_id = $status->id;
    	    $student->save();
        }
    	}

    	return redirect()->back()->with('message','Graduants sorted successfully');

    }


    /**
     * Show graduants list
     */
    public function showGraduants(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'graduants'=>Graduant::with(['student.campusProgram.program.ntaLevel','student.campusProgram.campus','student.overallRemark'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','GRADUATING')->paginate(50),
           'request'=>$request
    	];
    	return view('dashboard.academic.graduants-list',$data)->withTitle('Graduants List');
    }

    /**
     * Show non graduants list
     */
    public function showExcludedGraduants(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'non_graduants'=>Graduant::with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','EXCLUDED')->paginate(50),
           'request'=>$request
    	];
    	return view('dashboard.academic.non-graduants-list',$data)->withTitle('Non Graduants List');
    }
}
