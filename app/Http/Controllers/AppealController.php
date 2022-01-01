<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Appeal;
use App\Models\User;
use Auth;

class AppealController extends Controller
{

	/**
	 * Display a list of appeals
	 */
	public function index(Request $request)
	{
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'appeals'=>Appeal::whereHas('moduleAssignment',function($query) use($request){
            	 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->with(['student','moduleAssignment.studyAcademicYear.academicYear','moduleAssignment.module'])->latest()->paginate(20)
        ];
        return view('dashboard.academic.appeals',$data)->withTitle('Appeals');
	}

    /**
     * Get control number 
     */
    public function getControlNumber(Request $request)
    {
    	// $headers = array('Accept' => 'application/json');
     //    $options = array('auth' => array('user', 'pass'));
     //    $request = WpOrg\Requests\Requests::get('https://api.github.com/gists', $headers, $options);
    	$student = User::find(Auth::user()->id)->student;
    	$results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();

    	$years = [];
    	$years_of_studies = [];
    	$academic_years = [];
    	foreach($results as $key=>$result){
    		if(!array_key_exists($result->moduleAssignment->programModuleAssignment->year_of_study, $years)){
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study] = [];  
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
    		}
            if(!in_array($result->moduleAssignment->studyAcademicYear->id, $years[$result->moduleAssignment->programModuleAssignment->year_of_study])){

            	$years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
            }
    	}

    	foreach($years as $key=>$year){
    		foreach ($year as $yr) {
    			$years_of_studies[$key][] = StudyAcademicYear::with('academicYear')->find($yr);
    		}
    	}

    	$data = [
    	   'years_of_studies'=>$years_of_studies,
           'student'=>$student
    	];
    	return view('dashboard.student.appeal-examination-results',$data)->withTitle('Examination Results');
    }

    /**
     * Display student academic year results
     */
    public function showAcademicYearResults(Request $request, $ac_yr_id, $yr_of_study)
    {
    	 $student = User::find(Auth::user()->id)->student;
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student){
         	   $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	   $query->where('id',$student->id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();

          $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->get();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'publications'=>$publications,
         	'optional_programs'=>$optional_programs,
         	'year_of_study'=>$yr_of_study,
            'student'=>$student
         ];
         return view('dashboard.student.appeal-examination-results-report',$data)->withTitle('Examination Results');
    }


    /**
     * Store appeals
     */
    public function store(Request $request)
    {
    	 $student = User::find(Auth::user()->id)->student;
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($request, $student){
         	   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($request){
         	 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'));
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();


         foreach($results as $result){
         	 if($request->get('result_'.$result->id)){
         	 	 $appeal = new Appeal;
         	 	 $appeal->examination_result_id = $result->id;
         	 	 $appeal->module_assignment_id = $result->module_assignment_id;
         	 	 $appeal->student_id = $result->student_id;
         	 	 $appeal->save();
         	 }
         }

        return redirect()->back()->with('message','Results appeals submitted successfully');
    }
}
