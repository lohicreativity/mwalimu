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
use Illuminate\Support\Facades\Http;
use App\Utils\Util;
use App\Exports\GraduantsExport;
use App\Exports\GraduantsCertExport;

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

    /**
     * Download list
     */
    public function downloadList(Request $request)
    {
          return (new GraduantsExport($request->get('study_academic_year_id')))->download('graduants.xlsx');
    }

    /**
     * Download list
     */
    public function downloadCertList(Request $request)
    {
          return (new GraduantsCertExport($request->get('study_academic_year_id')))->download('graduants-certificates.xlsx');
    }

    /**
     * Enrollment report
     */
    public function enrollmentReport(Request $request)
    {

        if($request->get('query')){
           $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%')->paginate(50);
        }else{
           $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->paginate(50);
        }

        if($request->get('campus_program_id')){
            $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->where('campus_program_id',$request->get('campus_program_id'))->paginate(50);
        }
        $data = [
           'nta_levels'=>NTALevel::all(),
           'students'=>$students,
           'campus_programs'=>CampusProgram::with('program')->get(),
           'request'=>$request
        ];
        return view('dashboard.academic.enrollment-report',$data)->withTitle('Enrollment Report');
    }

    /**
     * Submit enrolled students
     */
    public function submitEnrolledStudents(Request $request)
    {
        $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award','annualRemarks'])->where('year_of_study',$request->get('year_of_study'))->get();


        foreach($students as $student){
            foreach($student->campusProgram->program->departments as $dpt){
              if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                  $department = $dpt;
              }
           }
           $is_year_repeat = 'NO';
           foreach($student->annualRemarks as $remark){
                 if($remark->year_of_study == $student->year_of_study){
                    if($remark->remark == 'CARRY' || $remark->remark == 'RETAKE'){
                       $is_year_repeat = 'YES';
                    }
                 }
           }

            $url='https://api.tcu.go.tz/applicants/submitEnrolledStudents';

               $xml_request = '<?xml version=”1.0” encoding=” UTF-8”?>
                <Request>
                <UsernameToken>
                <Username>'.config('constants.TCU_USERNAME').'</Username>
                <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                </UsernameToken>
                <RequestParameters>
                <Fname>'.$student->first_name.'</Fname>
                <Mname>'.$student->middle_name.'</Mname>
                <Surname>'.$student->surname.'</Surname>
                <F4indexno>'.$student->applicant->index_number.'</F4indexno>
                <Gender>'.$student->gender.'</Gender>
                <Nationality>'.$student->applicant->nationality.'</Nationality>
                <DateOfBirth>'.date('Y',strtotime($student->applicant->birth_date)).'</DateOfBirth>
                <ProgrammeCategory>'.$student->campusProgram->program->award->name.'</ProgrammeCategory>
                <Specialization>'.$department->name.'</Specialization>
                <AdmissionYear>'.$student->applicant->admission_year.'</AdmissionYear>
                <ProgrammeCode>'.$student->campusProgram->regulator_code.'</ProgrammeCode>
                <RegistrationNumber>'.$student->registration_number.'</RegistrationNumber>
                <ProgrammeName>'.$student->campusProgram->program->name.'</ProgrammeName>
                <YearOfStudy>'.$student->year_of_study.'</YearOfStudy >
                <StudyMode>'.$student->study_mode.'</StudyMode >
                <IsYearRepeat>'.$is_year_repeat.'</IsYearRepeat >
                <EntryMode>'.$student->applicant->entry_mode.'</EntryMode >
                <Sponsorship>Private</Sponsorship >
                <PhysicalChallenges>'.$student->applicant->disabilityStatus->name.'</PhysicalChallenges>
                </RequestParameters>
                </Request>';
          $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
          $json = json_encode($xml_response);
          $array = json_decode($json,TRUE);

          return dd($array);

        }

        return redirect()->back()->with('message','Enrolled students submitted successfully');
    }

    /**
     * Send XML over POST
     */
    public function sendXmlOverPost($url,$xml_request)
    {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml"));
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return $result;
    }

    /**
     * Download enrolled students
     */
    public function downloadEnrolledStudents(Request $request)
    {
         $nta_level = NTALevel::find($request->get('nta_level_id'));
         $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Enrollment-Report-Year-'.$request->get('year_of_study').'-'.$nta_level->name.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

              $list = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
              })->with(['applicant.disabilityStatus','campusProgram.program.award','annualRemarks'])->where('year_of_study',$request->get('year_of_study'))->get();

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['First Name','Middle Name','Surname','Gender','Nationality','Date of Birth','Award Category','Field Specialization','Year of Study','Study Mode','Is Year Repeat','Entry Qualification','Sponsorship','Admission Year','Physical Challenges','F4 Index No','Award Name','Registration Number','Institution Code','Programme Code']);
                  foreach ($list as $student) { 
                       foreach($student->campusProgram->program->departments as $dpt){
                          if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                              $department = $dpt;
                          }
                       }
                       $is_year_repeat = 'NO';
                       foreach($student->annualRemarks as $remark){
                             if($remark->year_of_study == $student->year_of_study){
                                if($remark->remark == 'CARRY' || $remark->remark == 'RETAKE'){
                                   $is_year_repeat = 'YES';
                                }
                             }
                       }

                      fputcsv($file_handle, [$student->first_name,$student->middle_name,$student->surname,$student->gender,
                        $student->applicant->nationality,
                        date('Y',strtotime($student->applicant->birth_date)),
                        $student->campusProgram->program->award->name,
                        $department->name,
                        $student->year_of_study,
                        $student->study_mode,
                        $is_year_repeat,
                        $student->applicant->entry_mode,
                        'Private',
                        $student->applicant->admission_year,
                        $student->applicant->disabilityStatus->name,
                        $student->applicant->index_number,
                        $student->campusProgram->program->name,
                        $student->registration_number,
                        substr($student->campusProgram->regulator_code,0,2),
                        $student->campusProgram->regulator_code,
                        ]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }
}
