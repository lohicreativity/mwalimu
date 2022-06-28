<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Department;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\NactePayment;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\SpecialDate;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\AcademicStatus;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\TamisemiStudent;
use App\Domain\Application\Models\NextOfKin;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Application\Models\ExternalTransfer;
use App\Domain\Application\Models\AdmissionAttachment;
use App\Domain\Application\Models\ApplicantSubmissionLog;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Actions\ApplicantAction;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Settings\Models\Currency;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\NHIFService;
use App\Models\User;
use App\Models\Role;
use App\Utils\SystemLocation;
use App\Utils\Util;
use App\Jobs\SendAdmissionLetter;
use App\Mail\AdmissionLetterCreated;
use App\Mail\StudentAccountCreated;
use App\Mail\TamisemiApplicantCreated;
use NumberToWords\NumberToWords;
use App\Utils\DateMaker;
use App\Services\ACPACService;
use Validator, Hash, Config, Auth, Mail, PDF, DB;

class ApplicationController extends Controller
{
    /**
     * Disaplay form for application
     */
    public function index(Request $request)
    {
    	$data = [
           'awards'=>Award::all(),
           'intakes'=>Intake::all()
    	];
    	return view('dashboard.application.register',$data)->withTitle('Applicant Registration');
    }


    /**
     * Show applicants list
     */
    public function showApplicantsList(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $application_window = ApplicationWindow::find($request->get('application_window_id'));


        if($request->get('department_id') != null){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program.departments',function($query) use($request){
                 $query->where('id',$request->get('department_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }elseif($request->get('duration') == 'today'){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('created_at','<=',now()->subDays(1))->paginate(20);
        }elseif($request->get('gender') != null){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('gender',$request->get('gender'))->paginate(20);
        }elseif($request->get('nta_level_id') != null){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }elseif($request->get('campus_program_id') != null){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->whereHas('selections',function($query) use($request){
                 $query->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }else{
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->paginate(20);
        }

        if($request->get('status') == 'progress'){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('programs_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->paginate(20);
        }elseif($request->get('status') == 'completed'){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('programs_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->paginate(20);
        }elseif($request->get('status') == 'submitted'){
           $applicants = Applicant::with(['selections.campusProgram.program'])->where('programs_complete_status',1)->where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->paginate(20);
        }elseif($request->get('status') == 'total'){
            $applicants = Applicant::with(['selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->paginate(20);
        }

        $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'application_window'=>$application_window,
            'nta_levels'=>NTALevel::all(),
            'departments'=>Department::all(),
            'campus_programs'=>CampusProgram::with('program')->get(),
            'applicants'=>$applicants,
            'request'=>$request
        ];
        return view('dashboard.application.applicants-list',$data)->withTitle('Applicants');
    }

    /**
     * Selected applicants
     */
    public function selectedApplicants(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if($request->get('query')){
            $applicants = Applicant::whereHas('applicationWindow',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('gender')){
            $applicants = Applicant::whereHas('applicationWindow',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('campus_program_id')){
            $applicants = Applicant::whereHas('applicationWindow',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('nta_level_id')){
             $applicants = Applicant::whereHas('applicationWindow',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }else{
            $applicants = Applicant::whereHas('applicationWindow',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }

         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING');
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'confirmed_campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING');
            })->whereHas('selections.applicant',function($query){
                 $query->where('multiple_admissions',1);
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'submission_logs'=>ApplicantSubmissionLog::where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))->get(),
            'request'=>$request
         ];
         return view('dashboard.application.selected-applicants',$data)->withTitle('Selected Applicants');
    }


    /**
     * Admitted applicants
     */
    public function admittedApplicants(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if($request->get('query')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->where('confirmation_status','!=','CANCELLED')->where('confirmation_status','!=','TRANSFERED')->paginate(20);
         }elseif($request->get('gender')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->where('confirmation_status','!=','CANCELLED')->where('confirmation_status','!=','TRANSFERED')->paginate(20);
         }elseif($request->get('campus_program_id')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('confirmation_status','!=','CANCELLED')->where('confirmation_status','!=','TRANSFERED')->paginate(20);
         }elseif($request->get('nta_level_id')){
             $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('confirmation_status','!=','CANCELLED')->where('confirmation_status','!=','TRANSFERED')->paginate(20);
         }else{
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('confirmation_status','!=','CANCELLED')->where('confirmation_status','!=','TRANSFERED')->paginate(20);
         }
         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING');
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'request'=>$request
         ];
         return view('dashboard.admission.admitted-applicants',$data)->withTitle('Admitted Applicants');
    }

    /**
     * Download selected applicants list
     */
    public function downloadSelectedApplicants(Request $request)
    {

        if(!$request->get('program_level_id')){
            return redirect()->back()->with('error','Please select program level first');
        }
        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Selected-Applicants-'.$award->name.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
         
         ApplicationWindow::find($request->get('application_window_id'))->update(['enrollment_report_download_status'=>1]);

         if($request->get('gender')){
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('campus_program_id')){
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('nta_level_id')){
             $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'))->where('status','APPROVING');
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }else{
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->orWhere('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }

         // if($request->get('query')){
         //    $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
         //         $query->where('id',$request->get('application_window_id'));
         //    })->whereHas('selections',function($query) use($request){
         //         $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
         //    })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->get();
         // }elseif($request->get('gender')){
         //    $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
         //         $query->where('id',$request->get('application_window_id'));
         //    })->whereHas('selections',function($query) use($request){
         //         $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
         //    })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->get();
         // }elseif($request->get('campus_program_id')){
         //    $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
         //         $query->where('id',$request->get('application_window_id'));
         //    })->whereHas('selections',function($query) use($request){
         //         $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING')->where('campus_program_id',$request->get('campus_program_id'));
         //    })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->get();
         // }elseif($request->get('nta_level_id')){
         //     $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
         //         $query->where('id',$request->get('application_window_id'));
         //    })->whereHas('selections.campusProgram.program',function($query) use($request){
         //         $query->where('nta_level_id',$request->get('nta_level_id'));
         //    })->whereHas('selections',function($query) use($request){
         //         $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
         //    })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->get();
         // }else{
         //    $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
         //         $query->where('id',$request->get('application_window_id'));
         //    })->whereHas('selections',function($query) use($request){
         //         $query->where('status','APPROVING')->orWhere('status','SELECTED')->orWhere('status','PENDING');
         //    })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->get();
         // }

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['First Name','Middle Name','Surname','Gender','Programme','Status','O-Level Results','A-Level Results']);
                  foreach ($list as $applicant) { 

                      foreach ($applicant->selections as $select) {
                         if($select->status == 'APPROVING' || $select->status == 'SELECTED'){
                            $selection = $select;
                         }
                      }
                      $o_level_results = [];
                      foreach($applicant->nectaResultDetails as $detail){
                          if($detail->exam_id = 1){
                              foreach($detail->results as $result){
                                 $o_level_results[] = $result->subject_name.'-'.$result->grade;
                              }
                          }
                      }
                      $a_level_results = [];
                      foreach($applicant->nectaResultDetails as $detail){
                          if($detail->exam_id = 2){
                              foreach($detail->results as $result){
                                 $o_level_results[] = $result->subject_name.'-'.$result->grade;
                              }
                          }
                      }

                      fputcsv($file_handle, [$applicant->first_name,$applicant->middle_name,$applicant->surname,$applicant->gender,$selection->campusProgram->program->name, $selection->status,implode(',', $o_level_results),implode(',',$a_level_results)
                        ]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Submit selected applicants
     */
    public function submitSelectedApplicants(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));

        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->with(['nextOfKin.region','region','district','intake','selections.campusProgram.program','nectaResultDetails'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();

        if(ApplicantProgramSelection::whereHas('applicant',function($query) use($request,$staff){
             $query->where('campus_id',$staff->campus_id)->where('program_level_id',$request->get('program_level_id'));
        })->where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING')->count() == 0){
             return redirect()->back()->with('error','Applicants selection has not been run yet');
        }


        foreach($applicants as $applicant){

            if($request->get('applicant_'.$applicant->id) == $applicant->id){
                if(str_contains($award->name,'Bachelor')){
                 //$url='https://api.tcu.go.tz/applicants/submitProgramme';
                  
                  if(ApplicantSubmissionLog::where('applicant_id',$applicant->id)->where('program_level_id',$request->get('program_level_id'))->count() == 0){

                   $url='http://41.59.90.200/applicants/submitProgramme';
                   
                   $selected_programs = array();
                   $approving_selection = null;
                   foreach($applicant->selections as $selection){
                       $selected_programs[] = $selection->campusProgram->regulator_code;
                       if($selection->status == 'APPROVING'){
                           $approving_selection = $selection;
                       }
                   }

                   $f6indexno = null;
                   foreach ($applicant->nectaResultDetails as $detail) {
                       if($detail->exam_id == 2){
                          $f6indexno = $detail->index_number;
                       }
                   }

                   if($f6indexno){
                     if($approving_selection){
                        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno >
                        <f6indexno>'.$f6indexno.'</f6indexno>
                        <SelectedProgrammes>'.implode(',', $selected_programs).'</SelectedProgrammes>
                        <MobileNumber>'.str_replace('-', '', $applicant->phone).'</MobileNumber>
                        <OtherMobileNumber></OtherMobileNumber>
                        <EmailAddress>'.$applicant->email.'</EmailAddress>
                        <Category>A</Category>
                        <AdmissionStatus>provisional admission</AdmissionStatus>
                        <ProgrammeAdmitted>'.$approving_selection->campusProgram->regulator_code.'</ProgrammeAdmitted>
                        <Reason>eligible</Reason>
                        <Nationality >'.$applicant->nationality.'</Nationality>
                        <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                        <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                        <NationalIdNumber>'.$applicant->nin.'</NationalIdNumber>
                        <Otherf4indexno></Otherf4indexno>
                        <Otherf6indexno></Otherf6indexno>
                        </RequestParameters>
                        </Request>';
                     }else{
                        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno >
                        <f6indexno>'.$f6indexno.'</f6indexno>
                        <SelectedProgrammes>'.implode(',', $selected_programs).'</SelectedProgrammes>
                        <MobileNumber>'.str_replace('-', '', $applicant->phone).'</MobileNumber>
                        <OtherMobileNumber></OtherMobileNumber>
                        <EmailAddress>'.$applicant->email.'</EmailAddress>
                        <Category>A</Category>
                        <AdmissionStatus>not selected</AdmissionStatus>
                        <ProgrammeAdmitted>'.null.'</ProgrammeAdmitted>
                        <Reason>max capacity</Reason>
                        <Nationality >'.$applicant->nationality.'</Nationality>
                        <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                        <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                        <NationalIdNumber>'.$applicant->nin.'</NationalIdNumber>
                        <Otherf4indexno></Otherf4indexno>
                        <Otherf6indexno></Otherf6indexno>
                        </RequestParameters>
                        </Request>';
                     }
                    $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                    $json = json_encode($xml_response);
                    $array = json_decode($json,TRUE);

              

            // return dd($array);
                
                  if($array['Response']['ResponseParameters']['StatusCode'] == 200){
                    // $select = ApplicantProgramSelection::find($approving_selection->id);
                    // $select->status = 'SELECTED';
                    // $select->save();

                    $log = new ApplicantSubmissionLog;
                    $log->applicant_id = $applicant->id;
                    $log->program_level_id = $request->get('program_level_id');
                    $log->application_window_id = $request->get('application_window_id');
                    $log->submitted = 1;
                    $log->save();
                  }
                }
              }
            }elseif(str_contains($award->name,'Diploma') || str_contains($award->name,'Basic')){
                  
          
                  $payment = NactePayment::latest()->first();
                  // $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/payment/'.$payment->reference_no.'/'.config('NACTE_API_SECRET'));
                  // json_decode($result)['params'][0]['balance']/5000
                  if(300000/5000 >= count($applicants)){
                      return redirect()->back()->with('error','No sufficient NACTE payment balance');
                  }

                  if(ApplicantSubmissionLog::where('applicant_id',$applicant->id)->where('program_level_id',$request->get('program_level_id'))->count() == 0){

                  $f6indexno = null;
                   foreach ($applicant->nectaResultDetails as $detail) {
                       if($detail->exam_id == 2){
                          $f6indexno = $detail->index_number;
                       }
                   }

                  // $params = [
                  //      'firstname'=>$applicant->first_name,
                  //      'secondname'=>$applicant->middle_name,
                  //      'surname'=>$applicant->surname,
                  //      'DOB'=>DateMaker::toStandardDate($applicant->birth_date),
                  //      'gender'=>$applicant->gender == 'M'? 'Male' : 'Female',
                  //      'impairement'=>$applicant->disabilityStatus->name,
                  //      'form_four_indexnumber'=>$applicant->index_number,
                  //      'form_four_year'=>explode('/',$applicant->index_number)[2],
                  //      'form_six_indexnumber'=>$f6indexno? $f6indexno : '',
                  //      'form_six_year'=>$f6indexno? explode('/', $f6indexno) : '',
                  //      'NTA4_reg'=>'',
                  //      'NTA4_grad_year'=>'',
                  //      'NTA5_reg'=>'',
                  //      'NTA5_grad_year'=>'',
                  //      'email_address'=>$applicant->email,
                  //      'mobile_number'=>str_replace('-', '',$applicant->phone),
                  //      'address'=>$applicant->address,
                  //      'region'=>$applicant->region->name,
                  //      'district'=>$applicant->district->name,
                  //      'nationality'=>$applicant->nationality,
                  //      'next_kin_name'=>$applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->first_name,
                  //      'next_kin_phone'=>$applicant->nextOfKin->phone,
                  //      'next_kin_address'=>$applicant->nextOfKin->address,
                  //      'next_kin_region'=>$applicant->nextOfKin->region->name,
                  //      'next_kin_relation'=>$applicant->nextOfKin->relationship,
                  //      // 'programme_id'=>'13f781c5dbe25cf387f708eaf40a7a0eae6291e7',//$applicant->selections[0]->campusProgram->regulator_code,
                  //      // 'payment_reference_number'=>'APITEST1003',//$payment->reference_no,
                  //      // 'application_year'=>date('Y'),
                  //      // 'intake'=>$applicant->intake->name
                  //   ];

                  //   $data = [
                  //     'heading' => [
                  //     'authorization' => 'e52ab037dc82d24960d9b9c678b5a6147a1ba6ea',
                  //     'intake' => 'SEPT',
                  //     'programme_id' => '13f781c5dbe25cf387f708eaf40a7a0eae6291e7',
                  //     'application_year' => date('Y'),
                  //     'level' => '5',
                  //     'payment_reference_number' => 'APITEST1003',
                  //     ],
                  //     'students'=>[
                  //         ['particulars'=>$params]
                  //     ]
                  //  ];

                   //API URL
                  $url = 'https://www.nacte.go.tz/nacteapi/index.php/api/upload';


                  $ch = curl_init($url);

                  $data = array(
                      'heading' => array(
                          'authorization' => 'e52ab037dc82d24960d9b9c678b5a6147a1ba6ea',
                          'intake' => 'SEPTEMBER',
                          'programme_id' => '13f781c5dbe25cf387f708eaf40a7a0eae6291e7',
                          'application_year' => '2022',
                          'level' => '5',
                          'payment_reference_number' => 'APITEST1003',
                      ),
                      'students' => array(
                          ['particulars' => array(
                                  'firstname' => $applicant->first_name,
                                  'secondname' => $applicant->middle_name,
                                  'surname' => $applicant->surname,
                                  'DOB' => DateMaker::toStandardDate($applicant->birth_date),
                                  'gender' => $applicant->gender == 'M'? 'Male' : 'Female',
                                  'impairement' => $applicant->disabilityStatus->name,
                                  'form_four_indexnumber' => $applicant->index_number,
                                  'form_four_year' => explode('/',$applicant->index_number)[2],
                                  'form_six_indexnumber' => $f6indexno? $f6indexno : '',
                                  'form_six_year' => $f6indexno? explode('/', $f6indexno) : '',
                                  'NTA4_reg' => $applicant->nact_reg_no,
                                  'NTA4_grad_year' => '',
                                  'NTA5_reg' => '',
                                  'NTA5_grad_year' => '',
                                  'email_address' => $applicant->email,
                                  'mobile_number' => str_replace('-', '',$applicant->phone),
                                  'address' => $applicant->address,
                                  'region' => $applicant->region->name,
                                  'district' => $applicant->district->name,
                                  'nationality' => $applicant->nationality,
                                  'next_kin_name' => $applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->surname,
                                  'next_kin_address' => $applicant->nextOfKin->address,
                                  'next_kin_email_address' => '',
                                  'next_kin_phone' => $applicant->nextOfKin->phone,
                                  'next_kin_region' => $applicant->nextOfKin->region->name,
                                  'next_kin_relation' => $applicant->nextOfKin->relationship
                              
                              )
                          ],
                          
                      )
                  );

                      

                  $payload = json_encode(array($data));



                  //attach encoded JSON string to the POST fields
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                  //set the content type to application/json
                  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

                  //return response instead of outputting
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                  //execute the POST request
                  $result = curl_exec($ch);

                  //close cURL resource
                  curl_close($ch);

                  //echo message
                  return $result;

                    // $url = 'http://41.93.40.137/nacteapi/index.php/api/upload';

                    // $url = 'https://www.nacte.go.tz/nacteapi/index.php/api/upload';

                    // $data = json_encode([$data]);

                    // // return dd($data);

                    // $ch = curl_init();
                    // curl_setopt($ch, CURLOPT_URL, $url);
                    // // For xml, change the content-type.
                    // curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
                    // curl_setopt($ch, CURLOPT_POST, 1);
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
                    // // Send to remote and return data to caller.
                    // $result = curl_exec($ch);
                    // curl_close($ch);
                    // return dd($result);


                    // $log = new ApplicantSubmissionLog;
                    // $log->applicant_id = $applicant->id;
                    // $log->program_level_id = $request->get('program_level_id');
                    // $log->application_window_id = $request->get('application_window_id');
                    // $log->submitted = 1;
                    // $log->save();

                  }

              }

            }  

        }

        return redirect()->back()->with('message','Applicants submitted successfully');
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
     * Select program
     */
    public function selectProgram(Request $request)
    {   
        $count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count();

        $similar_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('campus_program_id',$request->get('campus_program_id'))->count();
        if($similar_count == 0){
             if($count >= 4){
                return redirect()->back()->with('error','You cannot select more than 4 programmes');
             }else{
                 $selection = new ApplicantProgramSelection;
                 $selection->applicant_id = $request->get('applicant_id');
                 $selection->campus_program_id = $request->campus_program_id;
                 $selection->application_window_id = $request->get('application_window_id');
                 $selection->order = $request->get('choice');
                 $selection->save();

                 $select_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count();
                 if($request->get('choice') == 1){
                    $applicant = Applicant::find($request->get('applicant_id'));
                    $applicant->programs_complete_status = 1;
                    if($applicant->entry_mode == 'DIRECT'){
                        $applicant->documents_complete_status = 1;
                    }
                    $applicant->save();
                 }

                 return redirect()->back()->with('message','Programme selected successfully');
             }
        }else{
           return redirect()->back()->with('error','Programme already selected');
        }
    }

    /**
     * Reset program selection 
     */
    public function resetProgramSelection($id)
    {
        try{
          $selection = ApplicantProgramSelection::findOrFail($id);
          if($selection->order == 1){
              Applicant::where('id',$selection->applicant_id)->update(['programs_complete_status'=>0,'submission_complete_status'=>0]);
          }
          $selection->delete();
          return redirect()->back()->with('message','Selection reset successfully');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'document'=>'required|mimes:pdf,png,jpeg,jpg'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new ApplicantAction)->uploadDocuments($request);

        return redirect()->back()->with('message','Document uploaded successfully');
    }

    /**
     * Delete uploaded document
     */
    public function deleteDocument(Request $request)
    {
        $applicant = Applicant::with('programLevel')->where('user_id',Auth::user()->id)->where('campus_id',session('applicant_campus_id'))->first();
        if($request->get('name') == 'birth_certificate'){
           unlink(public_path().'/uploads/'.$applicant->birth_certificate);
           $applicant->birth_certificate = null;
        }

        if($request->get('name') == 'o_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->o_level_certificate);
           $applicant->o_level_certificate = null;
        }

        if($request->get('name') == 'a_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->a_level_certificate);
           $applicant->a_level_certificate = null;
        }

        if($request->get('name') == 'diploma_certificate'){
           unlink(public_path().'/uploads/'.$applicant->diploma_certificate);
           $applicant->diploma_certificate = null;
        }

        if($request->get('name') == 'passport_picture'){
           unlink(public_path().'/uploads/'.$applicant->passport_picture);
           $applicant->passport_picture = null;
        }

        if($applicant->entry_mode == 'DIRECT'){
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }
        }else{
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->diploma_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }
        }

        $applicant->save();


        return redirect()->back()->with('message','File deleted successfully');
    }

    /**
     * Download application summary
     */
    public function downloadSummary(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with(['nextOfKin.country','nextOfKin.region','nextOfKin.district','nextOfKin.ward','country','region','district','ward','disabilityStatus','nectaResultDetails.results','nacteResultDetails.results','selections','applicationWindow','intake'])->where('campus_id',session('applicant_campus_id'))->first();
        $data = [
           'applicant'=>$applicant,
           'selections'=>ApplicantProgramSelection::with(['campusProgram.program'])->where('applicant_id',$applicant->id)->get()
        ];
        $pdf = PDF::loadView('dashboard.application.summary', $data);
        return $pdf->stream();
    }

    /**
     * Submit application
     */
    public function submitApplication(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'agreement_check'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

       $applicant = Applicant::with(['programLevel'])->find($request->get('applicant_id'));
       if($applicant->basic_info_complete_status == 0){
          return redirect()->back()->with('error','Basic information section not completed');
       }
       if($applicant->next_of_kin_complete_status == 0){
          return redirect()->back()->with('error','Next of kin section not completed');
       }
       if($applicant->payment_complete_status == 0){
          return redirect()->back()->with('error','Payment section not completed');
       }
       if($applicant->results_complete_status == 0){
          return redirect()->back()->with('error','Results section not completed');
       }
       if($applicant->avn_no_results == 1 || $applicant->teacher_certificate_status == 1){
          if($applicant->documents_complete_status == 0){
             return redirect()->back()->with('error','Documents section not completed');
          }
       }
       if($applicant->programs_complete_status == 0){
          return redirect()->back()->with('error','Programmes selection section not completed');
       }
       // if($applicant->documents_complete_status == 0){
       //    return redirect()->back()->with('error','Upload documents section not completed');
       // }
       $applicant->submission_complete_status = 1;
       $applicant->submitted_at = now();
       $applicant->save();
       return redirect()->back()->with('message','Application Submitted Successfully');
    }

    /**
     * Request control number 
     */
    public function getControlNumber(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'fee_amount_id'=>'required',
            'applicant_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $applicant = Applicant::with('country')->find($request->get('applicant_id'));
        $fee_amount = FeeAmount::with(['feeItem.feeType'])->find($request->get('fee_amount_id'));
        $usd_currency = Currency::where('code','USD')->first();

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        if(str_contains($applicant->nationality,'Tanzania')){
           $invoice->amount = round($fee_amount->amount_in_tzs);
           $invoice->currency = 'TZS';
        }else{
           $invoice->amount = round($fee_amount->amount_in_usd*$usd_currency->factor);
           $invoice->currency = 'TZS';//'USD';
        }
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $fee_amount->feeItem->fee_type_id;
        $invoice->save();


        $payable = Invoice::find($invoice->id)->payable;
        $fee_type = $fee_amount->feeItem->feeType;

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = Config::get('constants.SUBSPCODE');

        $email = $payable->email? $payable->email : 'application@mnma.ac.tz';

        return $this->requestControlNumber($request,
                                  $invoice->reference_no,
                                  $inst_id,
                                  $invoice->amount,
                                  $fee_type->description,
                                  $fee_type->gfs_code,
                                  $fee_type->payment_option,
                                  $payable->id,
                                  $payable->first_name.' '.$payable->surname,
                                  $payable->phone,
                                  $email,
                                  $generated_by,
                                  $approved_by,
                                  $fee_type->duration,
                                  $invoice->currency);
  }
    
    /**
     * Request control number
     */
    public function requestControlNumber(Request $request,$billno,$inst_id,$amount,$description,$gfs_code,$payment_option,$payerid,$payer_name,$payer_cell,$payer_email,$generated_by,$approved_by,$days,$currency){
      $data = array(
        'payment_ref'=>$billno,
        'sub_sp_code'=>$inst_id,
        'amount'=> $amount,
        'desc'=> $description,
        'gfs_code'=> $gfs_code,
        'payment_type'=> $payment_option,
        'payerid'=> $payerid,
        'payer_name'=> $payer_name,
        'payer_cell'=> $payer_cell,
        'payer_email'=> $payer_email,
        'days_expires_after'=> $days,
        'generated_by'=>$generated_by,
        'approved_by'=>$approved_by,
        'currency'=>$currency
      );

      //$txt=print_r($data, true);
      //$myfile = file_put_contents('/var/public_html/ifm/logs/req_bill.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
      $url = url('bills/post_bill');
      $result = Http::withHeaders([
                        'X-CSRF-TOKEN'=> csrf_token()
                ])->post($url,$data);

       // return $result;
    return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
            
    }


    /**
     * Store registration information
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'index_number'=>'required|unique:applicants',
            'entry_mode'=>'required',
            'password'=>'required|min:8'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
        
        DB::beginTransaction();
        if($usr = User::where('username',$request->get('index_number'))->where('password',Hash::make($request->get('password')))->first()){
            $user = $usr;
        }else{
            $user = new User;
            $user->username = $request->get('index_number');
            $user->password = Hash::make($request->get('password'));
            $user->save();
        }

        $role = Role::where('name','applicant')->first();
        $user->roles()->sync([$role->id]);

        $applicant = new Applicant;
        $applicant->first_name = $request->get('first_name');
        $applicant->middle_name = $request->get('middle_name');
        $applicant->surname = $request->get('surname');
        $applicant->user_id = $user->id;
        $applicant->campus_id = 0;
        $applicant->index_number = strtoupper($request->get('index_number'));
        $applicant->entry_mode = $request->get('entry_mode');
        $applicant->program_level_id = $request->get('program_level_id');
        // $applicant->intake_id = $request->get('intake_id');
        $applicant->save();
        DB::commit();
        
        return redirect()->to('application/login')->with('message','Applicant registered successfully');

    }

    /**
     * Display run selection page
     */
    public function showRunSelection(Request $request)
    { 
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'staff'=>$staff,
           'awards'=>Award::all(),
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'request'=>$request
        ];
        return view('dashboard.application.run-selection',$data)->withTitle('Run Selection');
    }

     /**
     * Display run selection by program page
     */
    public function showRunSelectionByProgram(Request $request)
    { 
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'staff'=>$staff,
           'programs'=>CampusProgram::whereHas('selections.applicant',function($query) use($request){
                $query->where('application_window_id',$request->get('application_window_id'));
           })->with('program')->where('campus_id',$staff->campus_id)->get(),
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'request'=>$request
        ];
        return view('dashboard.application.run-selection-by-program',$data)->withTitle('Run Selection By Programme');
    }

    /**
     * Run application selection
     */
    public function runSelection(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             return redirect()->back()->with('error','Application window not closed yet');
        }

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('end_date','<',now()->format('Y-m-d'))->where('status','INACTIVE')->first()){
             return redirect()->back()->with('error','Application window is not active');
        }
        // Phase I
        $campus_programs = CampusProgram::whereHas('applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('award_id'));
        })->with(['program','entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('campus_id',$staff->campus_id)->get();

        foreach($campus_programs as $program){
           $count[$program->id] = 0;
        }

        $award = Award::find($request->get('award_id'));

        $applicants = Applicant::whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();

        // Phase II
        $choices = array(1,2,3,4);
        $applicants = Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->get();

        for($i = 0; $i < count($applicants); $i++){
            for($j = $i + 1; $j < count($applicants); $j++){
               if($applicants[$i]->rank_points < $applicants[$j]->rank_points){
                 $temp = $applicants[$i];
                 $applicants[$i] = $applicants[$j];
                 $applicants[$j] = $temp;
               }
            }
        }
        
        $selected_program = [];
        foreach ($applicants as $applicant) {
          $selected_program[$applicant->id] = false;
        }
        
        foreach($choices as $choice){   
            foreach ($campus_programs as $program) {

                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                if($program->entryRequirements[0]->max_capacity == null){
                     return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                }

                if(isset($program->entryRequirements[0])){
                foreach($applicants as $applicant){
                  
                  foreach($applicant->selections as $selection){
                     if($selection->order == $choice && $selection->campus_program_id == $program->id){
                        if($count[$program->id] < $program->entryRequirements[0]->max_capacity && $selection->status == 'ELIGIBLE' && !$selected_program[$applicant->id]){
                           if(ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->count() == 0 && ($applicant->avn_no_results !== 1 || $applicant->teacher_diploma_certificate == null)){
                               $select = ApplicantProgramSelection::find($selection->id);
                               $select->status = 'APPROVING';
                               $select->status_changed_at = now();
                               $select->save();

                               Applicant::where('id',$applicant->id)->update(['status'=>'SELECTED']);

                               $selected_program[$applicant->id] = true;

                               $count[$program->id]++;
                           }
                        }
                     }
                  }
                }
              }
           }
        }

        return redirect()->back()->with('message','Selection run successfully');
    }

    /**
     * Run application selection
     */
    public function runSelectionByProgram(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        $prog = CampusProgram::with('program')->find($request->get('campus_program_id'));

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             return redirect()->back()->with('error','Application window not closed yet');
        }
        
        if(ApplicantProgramSelection::whereHas('applicant',function($query) use($request,$prog){
            $query->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$prog->program->award_id);
        })->where('status','APPROVING')->count() == 0){
            return redirect()->back()->with('error','You cannot run selection by programme before running by NTA level');
        }
        // Phase I
        $campus_programs = CampusProgram::whereHas('applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->with(['program','entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('id',$request->get('campus_program_id'))->get();

        foreach($campus_programs as $program){
           $count[$program->id] = 0;
        }

        $award = Award::find($request->get('award_id'));

        $applicants = Applicant::whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();

        // Phase II
        $choices = array(1,2,3,4);
        $applicants = Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->get();

        for($i = 0; $i < count($applicants); $i++){
            for($j = $i + 1; $j < count($applicants); $j++){
               if($applicants[$i]->rank_points < $applicants[$j]->rank_points){
                 $temp = $applicants[$i];
                 $applicants[$i] = $applicants[$j];
                 $applicants[$j] = $temp;
               }
            }
        }
        
        $selected_program = [];
        foreach ($applicants as $applicant) {
          $selected_program[$applicant->id] = false;
        }
        
        foreach($choices as $choice){   
            foreach ($campus_programs as $program) {

                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                if($program->entryRequirements[0]->max_capacity == null){
                     return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                }

                if(isset($program->entryRequirements[0])){
                foreach($applicants as $applicant){
                  
                  foreach($applicant->selections as $selection){
                     if($selection->order == $choice && $selection->campus_program_id == $program->id){
                        if($count[$program->id] < $program->entryRequirements[0]->max_capacity && $selection->status == 'ELIGIBLE' && !$selected_program[$applicant->id]){
                           if(ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->count() == 0 && ($applicant->avn_no_results !== 1 || $applicant->teacher_diploma_certificate == null)){
                               $select = ApplicantProgramSelection::find($selection->id);
                               $select->status = 'APPROVING';
                               $select->status_changed_at = now();
                               $select->save();

                               Applicant::where('id',$applicant->id)->update(['status'=>'SELECTED']);

                               $selected_program[$applicant->id] = true;

                               $count[$program->id]++;
                           }
                        }
                     }
                  }
                }
              }
           }
        }

        return redirect()->back()->with('message','Selection run successfully');
    }

    /**
     * Admit applicant
     */
    public function admitApplicant(Request $request, $applicant_id, $selection_id)
    {
        $data = [
           'applicant'=>Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results','disabilityStatus','campus'])->find($applicant_id),
           'selection'=>ApplicantProgramSelection::with(['campusProgram.program'])->find($selection_id)
        ];
        return view('dashboard.application.applicant-admission',$data)->withTitle('Applicant Admission');
    }

    /**
     * Register applicant
     */
    public function registerApplicant(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'academic_results_check'=>'required',
            'fee_payment_check'=>'required',
            // 'insurance_check'=>'required',
            'personal_info_check'=>'required',
            'medical_form_check'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
        
        DB::beginTransaction();
        $staff = User::find(Auth::user()->id)->staff;

        $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        $reg_date = SpecialDate::where('study_academic_year_id',$ac_year->id)->where('name','New Registration Period')->where('campus_id',$staff->campus_id)->first();
        if(!$reg_date){
            return redirect()->back()->with('error','Registration period has not been set');
        }
        $now = time();
        $reg_date_time = strtotime($reg_date->date);
        $datediff = $reg_date_time - $now;
        if(round($datediff / (60 * 60 * 24)) < 0 && round($datediff / (60 * 60 * 24)) < -7){
            return redirect()->back()->with('error','Applicant cannot register. Registration period is over');
        }

        

        $applicant = Applicant::with(['intake','campus','nextOfKin','country','region','district','ward'])->find($request->get('applicant_id'));
        $applicant->results_check = $request->get('results_check')? 1 : 0;
        $applicant->insurance_check = $request->get('insurance_check')? 1 : 0;
        $applicant->personal_info_check = $request->get('personal_info_check')? 1 : 0;
        $applicant->medical_form_check = $request->get('medical_form_check')? 1 : 0;
        $applicant->registered_by_user_id = Auth::user()->id;
        $applicant->save();

        $selection = ApplicantProgramSelection::with('campusProgram.program')->where('applicant_id',$request->get('applicant_id'))->where('status','SELECTED')->first();

        $studentship_status = StudentshipStatus::where('name','ACTIVE')->first();
        $academic_status = AcademicStatus::where('name','PASS')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $last_student = Student::where('campus_program_id',$selection->campusProgram->id)->max('registration_number');
        if($last_student){
           $code = sprintf('%04d',explode('/', $last_student)[2] + 1);
        }else{
           $code = sprintf('%04d',1);
        }
        $year = substr(date('Y'), 2);

        $prog_code = explode('.', $selection->campusProgram->program->code);
        if(str_contains($applicant->intake->name,'March')){
            if(!str_contains($applicant->campus->name,'Kivukoni')){
               $program_code = $prog_code[0].'Z3.'.$prog_code[1];
            }else{
               $program_code = $prog_code[0].'3.'.$prog_code[1];
            }  
        }else{
            if(!str_contains($applicant->campus->name,'Kivukoni')){
               $program_code = $prog_code[0].'Z.'.$prog_code[1];
            }else{
               $program_code = $prog_code[0].'.'.$prog_code[1];
            }  
        }

        if($stud = Student::where('applicant_id',$applicant->id)->first()){
            $student = $stud;
        }else{
            $student = new Student;
        }
        $student->applicant_id = $applicant->id;
        $student->first_name = $applicant->first_name;
        $student->middle_name = $applicant->middle_name;
        $student->surname = $applicant->surname;
        $student->user_id = $applicant->user_id;
        $student->gender = $applicant->gender;
        $student->phone = $applicant->phone;
        $student->email = $applicant->email;
        $student->birth_date = $applicant->birth_date;
        $student->nationality = $applicant->nationality;
        $student->registration_year = date('Y');
        $student->year_of_study = 1;
        $student->campus_program_id = $selection->campusProgram->id;
        $student->registration_number = 'MNMA/'.$program_code.'/'.$code.'/'.$year;
        $student->disability_status_id = $applicant->disability_status_id;
        $student->studentship_status_id = $studentship_status->id;
        $student->academic_status_id = $academic_status->id;
        $student->save();

        $user = User::find($applicant->user_id);
        $user->username = $student->registration_number;
        $user->email = $student->email;
        $password = strtoupper(Util::randString(8));
        $user->password = Hash::make($password);
        $user->must_update_password = 1;
        $user->save();

        $role = Role::where('name','student')->first();
        $user->roles()->sync([$role->id]);
        
        $loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$ac_year->id)->first();

        if($loan_allocation){
            if($loan_allocation->has_signed == 1){
                 if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
                    $registration = $reg;
                  }else{
                    $registration = new Registration;
                  }
                  $registration->study_academic_year_id = $ac_year->id;
                  $registration->semester_id = $semester->id;
                  $registration->student_id = $student->id;
                  $registration->year_of_study = 1;
                  $registration->registered_by_staff_id = $staff->id;
                  $registration->status = 'REGISTERED';
                  $registration->save();
                  }
              $loan_allocation->registration_number = $student->registration_number;
              $loan_allocation->student_id = $student->id;
              $loan_allocation->save();
        }else{
            if($applicant->insurance_check == 1){
                if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
                  $registration = $reg;
                }else{
                  $registration = new Registration;
                }
                $registration->study_academic_year_id = $ac_year->id;
                $registration->semester_id = $semester->id;
                $registration->student_id = $student->id;
                $registration->year_of_study = 1;
                $registration->registered_by_staff_id = $staff->id;
                $registration->status = 'REGISTERED';
                $registration->save();
            }
        }


        $days = round($datediff / (60 * 60 * 24)) - 7;

        if(round($datediff / (60 * 60 * 24)) < 0 && round($datediff / (60 * 60 * 24)) > -7){
            $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Late Registration%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->first();

            $student = Student::with(['applicant.country'])->find($student->id);

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for late registration');
         }       

         if(str_contains($student->applicant->nationality,'Tanzania')){
             $amount = $fee_amount->amount_in_tzs*$days*(-1);
             $currency = 'TZS';
         }else{
             $amount = $fee_amount->amount_in_usd*$days*(-1);
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-LR-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
        $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
        $invoice->save();

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_amount->feeItem->feeType->description,
                                    $fee_amount->feeItem->feeType->gfs_code,
                                    $fee_amount->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_amount->feeItem->feeType->duration,
                                    $invoice->currency);
        }
        
        if($ac_year->nhif_enabled == 1){
            if($applicant->insurance_status == 0){
                 $path = public_path().'/img/user-avatar.png';
                 $type = pathinfo($path, PATHINFO_EXTENSION);
                 $data = file_get_contents($path);
                 $base64 = base64_encode($data); //'data:image/' . $type . ';base64,' . base64_encode($data);
                 $data = [
                      'FormFourIndexNo'=>str_replace('/', '-', $applicant->index_number),
                      'FirstName'=> $applicant->first_name,
                      'MiddleName'=> $applicant->middle_name,
                      'Surname'=> $applicant->surname,
                      'AdmissionNo'=> $student->registration_number,
                      'CollageFaculty'=> $applicant->campus->name,
                      'MobileNo'=> '0'.substr($applicant->phone,3),
                      'ProgrammeOfStudy'=> $selection->campusProgram->program->name,
                      'CourseDuration'=> $selection->campusProgram->program->min_duration,
                      'MaritalStatus'=> "Single",
                      'DateJoiningEmployer'=> date('Y-m-d'),
                      'DateOfBirth'=> $applicant->birth_date,
                      'NationalID'=> $applicant->nin? $applicant->nin : '',
                      'Gender'=> $applicant->gender == 'M'? 'Male' : 'Female',
                      'PhotoImage'=>$base64
                  ];
                      
                  $url = 'http://196.13.105.15/OMRS/api/v1/Verification/StudentRegistration';
                  $token = NHIFService::requestToken();

                      //return $token;
                  $curl_handle = curl_init();

                     // return json_encode($data);
           

                  curl_setopt_array($curl_handle, array(
                  CURLOPT_URL => $url,
                  CURLOPT_HTTPHEADER => array('Content-Type: application/json',$token),
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 4200,
                  CURLOPT_FOLLOWLOCATION => false,
                  CURLOPT_SSL_VERIFYPEER => false,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => json_encode([$data])
                  ));

                  $response = curl_exec($curl_handle);
                  $response = json_decode($response);
                  $StatusCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                  $err = curl_error($curl_handle);

                  curl_close($curl_handle);

                  $data = [
                  'BatchNo'=>'8002217/'.$ac_year->academicYear->year.'/001',
                  'Description'=>'Batch submitted on '.date('m d, Y'),
                  'CardApplications'=>[ 
                     array(
                      'CorrelationID'=>$applicant->index_number,
                        'MobileNo'=>'0'.substr($applicant->phone, 3),
                        'AcademicYear'=>$ac_year->academicYear->year,
                        'YearOfStudy'=>1,
                        'CardNo'=>null,
                        'Category'=>1//$response->statusCode == 200? 1 : 2
                     )      
                   ]
                 ];
                
                $url = 'http://196.13.105.15/OMRS/api/v1/Verification/SubmitCardApplications';
                // $token = NHIFService::requestToken();

                //return $token;
                $curl_handle = curl_init();

                          //  return json_encode($data);
                 

                  curl_setopt_array($curl_handle, array(
                  CURLOPT_URL => $url,
                  CURLOPT_HTTPHEADER => array('Content-Type: application/json',$token),
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 4200,
                  CURLOPT_FOLLOWLOCATION => false,
                  CURLOPT_SSL_VERIFYPEER => false,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => json_encode($data)
                ));

                $response = curl_exec($curl_handle);
                $response = json_decode($response);
                $StatusCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                $err = curl_error($curl_handle);

                curl_close($curl_handle);
                }
            }

        $tuition_invoice = Invoice::whereHas('feeType',function($query){
               $query->where('name','LIKE','%Tuition%');
        })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

        $misc_invoice = Invoice::whereHas('feeType',function($query){
               $query->where('name','LIKE','%Miscellaneous%');
        })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

        $acpac = new ACPACService;
        $stud_name = $student->surname.', '.$student->first_name.' '.$student->middle_name;
        $stud_reg = substr($student->registration_number, 5);
        $stud_reg = str_replace('/', '', $stud_reg);
        $parts = explode('.', $stud_reg);
        if($parts[0] == 'BTC'){
            $stud_reg = 'BT'.$parts[1];
        }else{
            $stud_reg = $parts[0].$parts[1];
        }
        $next_of_kin = $applicant->nextOfKin->surname.', '.$applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->middle_name;
        $gparts = explode('.', $program_code);
        if($parts[0] == 'BTC'){
            $stud_group = 'BT'.$parts[1];
        }else{
            $stud_group = $parts[0].$parts[1];
        }
        // $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('B','CRDB','REC02','10','TF','MNMA002','TEST','INV002','100.0','B','10')");
        $acpac->query("INSERT INTO customers (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('".$stud_reg."','".$stud_group."','".$stud_name."','".$applicant->address."','".$applicant->district->name."','".$applicant->ward->name."','".$applicant->street."','".$applicant->region->name."','".$applicant->region->name."','".$applicant->address."','".$applicant->country->name."','".$next_of_kin."','".$applicant->phone."','".$applicant->nextOfKin->phone."','".$program_code."','STD','TSH','".$applicant->email."','".$applicant->nextOfKin->email."')");

        $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,LINENO,REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$tuition_invoice->reference_no."','".date('Ymd',strtotime($tuition_invoice->created_at))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','1','".$tuition_invoice->feeType->gl_code."','".$tuition_invoice->feeType->name."','".$tuition_invoice->feeType->description."','".$tuition_invoice->amount."','0','".date('Ymd',strtotime(now()))."')");

        $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,LINENO,REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->reference_no."','".date('Ymd',strtotime($misc_invoice->created_at))."','".$misc_invoice->feeType->description."','".$stud_reg."','".$stud_name."','1','".$misc_invoice->feeType->gl_code."','".$misc_invoice->feeType->name."','".$misc_invoice->feeType->description."','".$misc_invoice->amount."','0','".date('Ymd',strtotime(now()))."')");

        if($tuition_invoice->gatewayPayment->psp_name == 'National Microfinance Bank'){
            $bank_code = 619;
            $bank_name = 'NMB';
        }else{
            $bank_code = 615;
            $bank_name = 'CRDB';
        }

        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($tuition_invoice->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($tuition_invoice->gatewayPayment->datetime))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$tuition_invoice->reference_no."','".$tuition_invoice->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

        if($misc_invoice->gatewayPayment->psp_name == 'National Microfinance Bank'){
            $bank_code = 619;
            $bank_name = 'NMB';
        }else{
            $bank_code = 615;
            $bank_name = 'CRDB';
        }

        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($misc_invoice->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($misc_invoice->gatewayPayment->datetime))."','".$misc_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$misc_invoice->reference_no."','".$misc_invoice->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

        $acpac->close();

        try{
           //Mail::to($user)->send(new StudentAccountCreated($student, $selection->campusProgram->program->name,$ac_year->academicYear->year, $password));
        }catch(\Exception $e){}
        DB::commit();
        if($days < 0){
          return redirect()->to('application/applicants-registration')->with('error','Student successfully registered with registration number '.$student->registration_number.', but has a penalty of '.$amount.' '.$currency);
        }else{
           return redirect()->to('application/applicants-registration')->with('message','Student registered successfully with registration number '.$student->registration_number);
        }
    }

    /**
     * Selected applicant
     */
    public function applicantsRegistration(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
         $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->whereYear('end_date',explode('/',$ac_year->academicYear->year)[0])->first();

         if(!$application_window){
             return redirect()->back()->with('error','No corresponding application window');
         }
         if($request->get('query')){
            $applicants = Applicant::whereDoesntHave('student')->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('campus_id',$staff->campus_id)->where(function($query) use($request){
                   $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%');
                 })->where('application_window_id',$application_window->id)->where(function($query){
                     $query->where('confirmation_status','!==','CANCELLED')->orWhere('confirmation_status','!==','TRANSFERED')->orWhereNull('confirmation_status');
                   })->where(function($query){
                     $query->where('admission_confirmation_status','!==','NOT CONFIRMED')->orWhereNull('admission_confirmation_status');
                   })->where('status','ADMITTED')->get();
              if(count($applicants) == 0){
                  return redirect()->back()->with('error','No applicant with searched name or already registered');
              }
         }elseif($request->get('index_number')){
            $applicants = Applicant::whereDoesntHave('student')->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['intake','selections.campusProgram.program'])->where('index_number','LIKE','%'.$request->get('index_number').'%')->where('application_window_id',$application_window->id)->where(function($query){
                     $query->where('confirmation_status','!==','CANCELLED')->orWhere('confirmation_status','!==','TRANSFERED')->orWhereNull('confirmation_status');
                   })->where(function($query){
                     $query->where('admission_confirmation_status','!==','NOT CONFIRMED')->orWhereNull('admission_confirmation_status');
                   })->where('status','ADMITTED')->get();
            if(count($applicants) == 0){
                  return redirect()->back()->with('error','No applicant with searched index number or already registered');
              }
         }else{
            $applicants = [];
         }
         $data = [
            'staff'=>$staff,
            'applicants'=>$applicants,
            'request'=>$request
         ];
         return view('dashboard.application.applicants-registration',$data)->withTitle('Applicants Registration');
    }

        /**
     * Selected applicants
     */
    public function applicantsAdmission(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('selections',function($query) use($request){
             $query->where('status','SELECTED');
        })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);

        $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','ACTIVE')->first();

        if(!$application_window){
            return redirect()->back()->with('error','No active application window for your campus');
        }
         
         $data = [
            'staff'=>$staff,
            'application_window'=>$application_window,
            'awards'=>Award::all(),
            'request'=>$request
         ];
         return view('dashboard.application.applicants-admission',$data)->withTitle('Applicants Admission');
    }

    /**
     * Show upload admission attachment
     */
    public function uploadAttachments(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         $data = [
            'staff'=>$staff,
            'attachments'=>AdmissionAttachment::paginate(20),
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'request'=>$request
         ];
         return view('dashboard.application.upload-attachments',$data)->withTitle('Upload Attachments');
    }

    /**
     * Show admission attachments
     */
    public function admissionPackage(Request $request)
    {    
         $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
         $data = [
            'attachments'=>AdmissionAttachment::paginate(20),
            'applicant'=>$applicant,
            'request'=>$request
         ];
         return view('dashboard.application.admission-package',$data)->withTitle('Admission Package');
    }

    /**
     * Download admission letter
     */
    public function downloadAdmissionLetter(Request $request)
    {   
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
        try{
           return response()->download(public_path().'/uploads/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Document could not be found');
        }
    }

    /**
     * Upload admission attachments
     */
    public function uploadAttachmentFile(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name'=>'required',
            'attachment'=>'required|mimes:pdf'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if($request->hasFile('attachment')){
            $destination = SystemLocation::uploadsDirectory();
            $request->file('attachment')->move($destination, $request->file('attachment')->getClientOriginalName());


            $attachment = new AdmissionAttachment;
            $attachment->name = $request->get('name');
            $attachment->file_name = $request->file('attachment')->getClientOriginalName();
            $attachment->save();
        }

        return redirect()->back()->with('message','Attachment uploaded successfully');
    }

    /**
     * Download admission attachment
     */
    public function downloadAttachment(Request $request)
    {
        $attachment = AdmissionAttachment::find($request->get('id'));
        return response()->download(public_path().'/uploads/'.$attachment->file_name);
    }

    /**
     * Delete admission attachment
     */
    public function deleteAttachment(Request $request)
    {
        $attachment = AdmissionAttachment::find($request->get('id'));
        if(file_exists(public_path().'/uploads/'.$attachment->file_name)){
           unlink(public_path().'/uploads/'.$attachment->file_name);
        }
        $attachment->delete();
        return redirect()->back()->with('message','Attachment deleted successfully');
    }

    /**
     * Send admission letter to applicants
     */
    public function sendAdmissionLetter(Request $request)
    {
        dispatch(new SendAdmissionLetter($request->all()));

        return redirect()->back()->with('message','Admission package sent successfully');
    }


    /**
     * Show dashboard
     */
    public function showDashboard(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if(!Auth::user()->hasRole('administrator')){
           $application_window = ApplicationWindow::where('status','ACTIVE')->where('campus_id',$staff->campus_id)->first();
         }else{
           $application_window = ApplicationWindow::find($request->get('application_window_id'));
         }
         if(!$application_window){
             return redirect()->back()->with('error','No active application window has been set');
         }
         $data = [
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'campuses'=>Campus::all(),
            'progress_applications'=>Applicant::where('results_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count(),
            'completed_applications'=>Applicant::where('results_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count(),
            'submitted_applications'=>Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count(),
            'total_applications'=>Applicant::where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count(),
            'today_progress_applications'=>Applicant::where('results_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'today_completed_applications'=>Applicant::where('results_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'today_submitted_applications'=>Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->whereDate('submitted_at','=',now()->format('Y-m-d'))->count(),
            'today_total_applications'=>Applicant::where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'staff'=>$staff,
            'request'=>$request
         ];
         return view('dashboard.application.application-dashboard',$data)->withTitle('Application Dashboard');
    }

    /**
     * Search for applicant
     */
    public function searchForApplicant(Request $request)
    {   
        $applicant = Applicant::where('index_number',$request->get('index_number'))->first();
        if($request->get('index_number') && !$applicant){
            return redirect()->back()->with('error','Student does not exists');
        }
        $data = [
             'applicant'=>$applicant
        ];
        return view('dashboard.application.search-applicant',$data)->withTitle('Search For Applicant');
    }

    /**
     * Reset applicant's password
     */
    public function resetApplicantPassword(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'password'=>'required',
            'password_confirmation'=>'required|same:password'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $user = User::find($request->get('user_id'));
        $user->password = Hash::make($request->get('password'));
        $user->save();

        return redirect()->back()->with('message','Password reset successfully');
    }

    /**
     * Reset applicant's password
     */
    public function resetApplicantPasswordDefault(Request $request)
    {
        $applicant = Applicant::find($request->get('applicant_id'));
        // if(!ApplicationWindow::where('campus_id',$applicant->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
        //        return redirect()->back()->with('error','You cannot reset applicant\'s password because application window is already closed');
        // }
        $user = User::find($request->get('user_id'));
        $user->password = Hash::make('password');
        $user->save();

        return redirect()->to('application/application-dashboard')->with('message','Password reset successfully');
    }

    /**
     * Show insurance statuses
     */
    public function showInsuranceStatus(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'awards'=>Award::all(),
           'applicants'=>Applicant::with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->paginate(50),
           'request'=>$request
        ];
        return view('dashboard.application.insurance-statuses',$data)->withTitle('Applicant Insurance Status');
    }

    /**
     * Download insurance status
     */
    public function downloadInsuranceStatus(Request $request)
    {
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Insurance-Status.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

        $list = Applicant::has('insurances')->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get();

        $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['Index Number','First Name','Middle Name','Surname','Insurance Name','Card Number','Expiry Date']);
                  foreach ($list as $row) { 
                      fputcsv($file_handle, [$row->index_number,$row->first_name,$row->middle_name,$row->surname,$row->insurances[0]->insurance_name,$row->insurances[0]->membership_number,$row->insurances[0]->expire_date]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Show hostel statuses
     */
    public function showHostelStatus(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'awards'=>Award::all(),
           'applicants'=>Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->paginate(50),
           'request'=>$request
        ];
        return view('dashboard.application.hostel-statuses',$data)->withTitle('Applicant Insurance Status');
    }

    /**
     * Download hostel status
     */
    public function downloadHostelStatus(Request $request)
    {
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Hostel-Status.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

        $list = Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get();

        $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['Index Number','First Name','Middle Name','Surname','Status']);
                  foreach ($list as $row) { 
                      fputcsv($file_handle, [$row->index_number,$row->first_name,$row->middle_name,$row->surname,$row->hostel_status == 1? 'Yes' : 'No']);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Submit Applicants
     */
    public function submitApplicants(Request $request)
    {
        $data = [
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'program_level'=>Award::find($request->get('program_level_id')),
            'selected_applicants'=>Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get(),
            'request'=>$request
        ];
        return view('dashboard.application.submit-selected-applicants',$data)->withTitle('Submit Selected Applicants');
    }

    /**
     * Retrieve applicants from TCU
     */
    public function getApplicantsFromTCU(Request $request)
    {
        if(ApplicantSubmissionLog::where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))->count() == 0){
             return redirect()->back()->with('error','Applicants were not sent to TCU');
        }
        $url = 'http://41.59.90.200/applicants/getStatus';
        $campus_program = CampusProgram::find($request->get('campus_program_id'));
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <ProgrammeCode>'.$campus_program->regulator_code.'</ProgrammeCode>
                        </RequestParameters>
                        </Request>
                        ';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        foreach($array['Response']['ResponseParameters']['Applicant'] as $data){
            $applicant = Applicant::where('index_number',$data['f4indexno'])->first();
            if($applicant){
               $applicant->multiple_admissions = $data['AdmissionStatusCode'] == 225? 1 : 0;
               $applicant->save();

               $selection = ApplicantProgramSelection::where('applicant_id',$applicant)->where('status','APPROVING')->update(['status'=>'SELECTED']);
            }
        }

        ApplicantProgramSelection::whereHas('applicant',function($query) use($request){
             $query->where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'));
        })->where('campus_program_id',$request->get('campus_program_id'))->where('status','APPROVING')->update(['status'=>'PENDING']);

        return redirect()->back()->with('message','Applicants retrieved successfully from TCU');
    }


    /**
     * Retrieve confirmed applicants from TCU
     */
    public function getConfirmedFromTCU(Request $request)
    {
        if(ApplicantSubmissionLog::where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))->count() == 0){
             return redirect()->back()->with('error','Applicants were not sent to TCU');
        }
        if(ApplicantProgramSelection::where('application_window_id',$request->get('application_window_id'))->where('status','SELECTED')->count() == 0){
             return redirect()->back()->with('error','No applicants not retrieved from TCU');
        }
        $url = 'http://41.59.90.200/applicants/getConfirmed';
        $campus_program = CampusProgram::find($request->get('campus_program_id'));
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <ProgrammeCode>'.$campus_program->regulator_code.'</ProgrammeCode>
                        </RequestParameters>
                        </Request>
                        ';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        foreach($array['Response']['ResponseParameters']['Applicant'] as $data){
            $applicant = Applicant::where('index_number',$data['f4indexno'])->first();
            if($applicant){
               $applicant->admission_confirmation_status = $data['ConfirmationStatusCode'] == 233? 'CONFIRMED' : 'NOT CONFIRMED';
               $applicant->save();
            }
        }

        return redirect()->back()->with('message','Confirmed applicants retrieved successfully from TCU');
    }

    /**
     * Show admission confirmation
     */
    public function showConfirmAdmission(Request $request)
    {
        $data = [
           'applicant'=>User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first(),
           'campus'=>Campus::find(session('applicant_campus_id'))
        ];
        return view('dashboard.admission.admission-confirmation',$data)->withTitle('Admission Confirmation');
    }

    /**
     * Confirm admission
     */
    public function confirmAdmission(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'confirmation_code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
        $applicant = Applicant::find($request->get('applicant_id'));
        
        $url = 'http://41.59.90.200/admission/confirm';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ConfirmationCode>'.$request->get('confirmation_code').'</ConfirmationCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $applicant->confirmation_status = 'CONFIRMED';
            $applicant->save();

            return redirect()->back()->with('message','Admission confirmed successfully');
        }else{
            return redirect()->back()->with('error','Unable to confirm admission');
        }
    }

    /**
     * Confirm admission
     */
    public function unconfirmAdmission(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'confirmation_code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
        $applicant = Applicant::find($request->get('applicant_id'));
        
        $url = 'http://41.59.90.200/admission/unconfirm';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ConfirmationCode>'.$request->get('confirmation_code').'</ConfirmationCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $applicant->confirmation_status = 'UNCONFIRMED';
            $applicant->save();
            
            return redirect()->back()->with('message','Admission unconfirmed successfully');
        }else{
            return redirect()->back()->with('error','Unable to unconfirm admission');
        }
    }

    /**
     * Request confirmation code
     */
    public function requestConfirmationCode(Request $request)
    {
        $applicant = Applicant::find($request->get('applicant_id'));
        
        $url = 'http://41.59.90.200/admission/requestConfirmationCode';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <MobileNumber>0'.substr($applicant->phone, 3).'</MobileNumber>
                        <EmailAddress>'.$applicant->email.'</ EmailAddress >
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 223){

            return redirect()->back()->with('message','Confirmation code requested successfully');
        }else{
            return redirect()->back()->with('error','Unable to request confirmation code. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

    /**
     * Request confirmation code
     */
    public function cancelAdmission(Request $request)
    {
        $applicant = Applicant::find($request->get('applicant_id'));
        
        $url = 'http://41.59.90.200/admission/reject';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 224){
            $applicant->confirmation_status = 'CANCELLED';
            $applicant->save();
            return redirect()->back()->with('message','Admission rejected successfully');
        }else{
            return redirect()->back()->with('error','Unable to reject admission. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

    /**
     * Restore cancelled admission
     */
    public function restoreCancelledAdmission(Request $request)
    {
        $applicant = Applicant::with(['selections.campusProgram'])->find($request->get('applicant_id'));

        $admitted_program = null;
        foreach($applicant->selections as $selection){
            if($selection->status == 'SELECTED'){
                $admitted_program = $selection->campusProgram->regulator_code;
            }
        }
        
        $url = 'http://41.59.90.200/admission/restoreCancelledAdmission';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ProgrammeCode>'.$admitted_program.'</ ProgrammeCode >
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $applicant->confirmation_status = 'RESTORED';
            $applicant->save();
            return redirect()->back()->with('message','Admission restored successfully');
        }else{
            return redirect()->back()->with('error','Unable to restore admission. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

    /**
     * Show internal transfer
     */
    public function showInternalTransfer(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $student = Student::whereHas('applicant',function($query) use($staff){
                     $query->where('campus_id',$staff->campus_id);
        })->with(['applicant.selections'=>function($query){
              $query->where('status','SELECTED');
        },'selections.campusProgram.program'])->where('registration_number',$request->get('registration_number'))->first();
        if(!$student && $request->get('registration_number')){
            return redirect()->back()->with('error','Student does not belong to this campus');
        }
        $data = [
            'student'=>$student,
            'campus_programs'=>$student? CampusProgram::whereHas('program',function($query) use($student){
                 $query->where('award_id',$student->applicant->program_level_id)->where('campus_id',$student->applicant->campus_id);
            })->with('program')->get() : [],
            'transfers'=>InternalTransfer::whereHas('student.applicant',function($query) use($staff){
                  $query->where('campus_id',$staff->campus_id);
            })->with(['student.applicant','previousProgram.program','currentProgram.program','user.staff'])->paginate(20),
            'staff'=>$staff
        ];
        return view('dashboard.registration.submit-internal-transfer',$data)->withTitle('Internal Transfer');
    }

    /**
     * Show internal transfer
     */
    public function showExternalTransfer(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $student = Student::whereHas('applicant',function($query) use($staff){
                     $query->where('campus_id',$staff->campus_id);
        })->with(['applicant.selections'=>function($query){
              $query->where('status','SELECTED');
        },'selections.campusProgram.program'])->where('registration_number',$request->get('registration_number'))->first();
        if(!$student && $request->get('registration_number')){
            return redirect()->back()->with('error','Student does not belong to this campus');
        }
        // if($applicant){
        //     if($applicant->multiple_admissions == 1 && $applicant->confirmation_status != 'CONFIRMED'){
        //          return redirect()->back()->with('error','The applicant has multiple admissions and has not yet confirmed');
        //     }
        //     if($applicant->multiple_admissions == 0 && $applicant->confirmation_status == 'CANCELLED'){
        //          return redirect()->back()->with('error','The applicant has cancelled the admission');
        //     }
        //     if($applicant->multiple_admissions == 0 && $applicant->confirmation_status == 'TRANSFERED'){
        //          return redirect()->back()->with('error','The applicant has already been transfered');
        //     }
        //     $admission_status = null;
        //     foreach($applicant->selections as $selection){
        //         if($selection->status == 'SELECTED'){
        //             $admission_status = true;
        //         }
        //     }
        //     if(!$admission_status){
        //         return redirect()->back()->with('error','Applicant has not been admitted');
        //     }
        // }
        $data = [
            'student'=>$student,
            'transfers'=>ExternalTransfer::whereHas('student.applicant',function($query) use($staff){
                  $query->where('campus_id',$staff->campus_id);
            })->with(['student.applicant','previousProgram.program','user.staff'])->paginate(20),
            'staff'=>$staff
        ];
        return view('dashboard.registration.submit-external-transfer',$data)->withTitle('External Transfer');
    }

    /**
     * Submit internal transfer
     */
    public function submitInternalTransfer(Request $request)
    {
        $student = Student::with(['applicant.selections.campusProgram','nectaResultDetails.results','applicant.nacteResultDetails.results','applicant.programLevel'])->find($request->get('student_id'));

        $award = $student->applicant->programLevel;
        $applicant = $student->applicant;

        $transfer_program = CampusProgram::with(['entryRequirements'=>function($query) use($applicant){
             $query->where('application_window_id',$applicant->application_window_id);
        },'program'])->find($request->get('campus_program_id'));

        $transfer_program_code = $transfer_program->regulator_code;

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        
           $index_number = $applicant->index_number;
           $exam_year = explode('/', $index_number)[2];
          
           foreach($applicant->nectaResultDetails as $detail) {
              if($detail->exam_id == 2){
                  $index_number = $detail->index_number;
                  $exam_year = explode('/', $index_number)[2];
              }
           }

           if($exam_year < 2014 || $exam_year > 2015){
             $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
           }else{
             $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'S'=>0.5,'F'=>0];
           }
           $subject_count = 0;
           $program = $transfer_program;
           foreach($applicant->selections as $selection){
                
                if($program->id == $selection->campus_program_id){

                  if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                  }

                  if($program->entryRequirements[0]->max_capacity == null){
                    return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                  }

                   // Certificate
                   if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {
                          
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                    $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }
                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                           
                         }else{
                            return redirect()->back()->with('error','Applicant does not qualify for transfer');
                         }
                       }
                   }

                   // Diploma
                   if(str_contains($award->name,'Diploma')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_subsidiary_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;


                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                     $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                    }
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){

                         }else{
                            return redirect()->back()->with('error','Applicant does not qualify for transfer');
                         }

                         
                       }
                       $has_btc = false;
                       foreach($applicant->nacteResultDetails as $detailKey=>$detail){
                          if(str_contains($detail->programme,'BASIC TECHNICIAN CERTIFICATE')){
                              $has_btc = true;
                          }
                       }

                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){

                       }else{
                          return redirect()->back()->with('error','Applicant does not qualify for transfer');
                       }
                   }
                   
                   // Bachelor
                   if(str_contains($award->name,'Bachelor')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_principle_pass_points = 0;
                       $a_level_subsidiary_pass_count = 0;
                       $diploma_pass_count = 0;
                       
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                 $applicant->rank_points += $o_level_grades[$result->grade];
                                 $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                      $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                    }
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                         }else{

                         }
                       }

                       foreach ($applicant->nacteResultDetails as $detailKey=>$detail) {
                         foreach ($detail->results as $key => $result) {
                              if($diploma_grades[$result->grade] >= $diploma_grades[$program->entryRequirements[0]->equivalent_average_grade]){
                                 $diploma_pass_count += 1;
                              }
                           }
                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($diploma_pass_count >= $program->entryRequirements[0]->equivalent_pass_subjects || $detail->diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)){
                           
                         }else{
                            return redirect()->back()->with('error','Applicant does not qualify for transfer');
                         }
                       }
                   }
                }
            }

        $admitted_program_code = null;
        foreach($applicant->selections as $selection){
            if($selection->status == 'SELECTED'){
                $admitted_program = $selection->campusProgram;
                $admitted_program_code = $selection->campusProgram->regulator_code;
            }
        }

        $f6indexno = null;
        foreach($applicant->nectaResultDetails as $detail){
            if($detail->exam_id == 2){
               $f6indexno = $detail->index_number;
               break;
            }
        }

        
        
        $url = 'http://41.59.90.200/admission/submitInternalTransfers';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                         <f4indexno>'.$student->applicant->index_number.'</f4indexno>
                         <f6indexno>'.$f6indexno.'</f6indexno>
                         <CurrentProgrammeCode>'.$transfer_program_code.'</CurrentProgrammeCode>
                         <PreviousProgrammeCode>'.$admitted_program_code.'</PreviousProgrammeCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        

        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $transfer = new InternalTransfer;
            $transfer->student_id = $student->id;
            $transfer->previous_campus_program_id = $admitted_program->id;
            $transfer->current_campus_program_id = $transfer_program->id;
            $transfer->transfered_by_user_id = Auth::user()->id;
            $transfer->save();

            ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','SELECTED')->update(['status'=>'ELIGIBLE']);

            $select = new ApplicantProgramSelection;
            $select->student_id = $student->id;
            $select->campus_program_id = $transfer_program->id;
            $select->application_window_id = $applicant->application_window_id;
            $select->order = 5;
            $select->status = 'SELECTED';
            $select->save();
            return redirect()->to('registration/internal-transfer')->with('message','Transfer completed successfully');
        }else{
            return redirect()->back()->with('error','Unable to complete transfer. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

    /**
     * Submit external transfer
     */
    public function submitExternalTransfer(Request $request)
    {
        $student = Student::with(['applicant.selections.campusProgram','applicant.nectaResultDetails'])->find($request->get('student_id'));

        $applicant = $student->applicant;

        $admitted_program_code = null;
        foreach($student->applicant->selections as $selection){
            if($selection->status == 'SELECTED'){
                $admitted_program = $selection->campusProgram;
                $admitted_program_code = $selection->campusProgram->regulator_code;
            }
        }

        $f6indexno = null;
        foreach($student->applicant->nectaResultDetails as $detail){
            if($detail->exam_id == 2){
               $f6indexno = $detail->index_number;
               break;
            }
        }
        
        $url = 'http://41.59.90.200/admission/submitInternalTransfers';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.config('constants.TCU_USERNAME').'</Username>
                        <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                         <f4indexno>'.$student->applicant->index_number.'</f4indexno>
                         <f6indexno>'.$f6indexno.'</f6indexno>
                         <CurrentProgrammeCode>'.$request->get('program_code').'</CurrentProgrammeCode>
                         <PreviousProgrammeCode>'.$admitted_program_code.'</PreviousProgrammeCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        


        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $transfer = new ExternalTransfer;
            $transfer->student_id = $student->id;
            $transfer->previous_campus_program_id = $admitted_program->id;
            $transfer->current_program = $request->get('program_code');
            $transfer->transfered_by_user_id = Auth::user()->id;
            $transfer->save();

            $applicant->confirmation_status = 'TRANSFERED';
            $applicant->save();
            return redirect()->to('registration/external-transfer')->with('message','Transfer completed successfully');
        }else{
            return redirect()->back()->with('error','Unable to complete transfer. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

    /**
     * Get verified students from NACTE
     */
    public function getVerifiedApplicantsNACTE(Request $request)
    {
         $campus_programs = CampusProgram::whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('program_level_id'));
         })->get();
         $intake = ApplicationWindow::find($request->get('application_window_id'))->intake;
         foreach($campus_programs as $program){
            $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/verificationresults/'.$program->regulator_code.'-'.date('Y').'-'.$intake->name.'/'.config('constants.NACTE_API_KEY'));

            if($result['code'] == 200){
                foreach ($result['params'] as $res) {
                    $applicant = Applicant::where('index_number',$res['username'])->first();
                    $applicant->multiple_admissions = $res['multiple_selection'] == 'no multiple'? 0 : 1;
                    $applicant->save();

                    ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->update(['status'=>'SELECTED']);
                }
            }
         }

         return redirect()->back()->with('message','Verified applicants retrieved successfully from NACTE');
    }

    /**
     * Show Tamisemi applicants
     */
    public function tamisemiApplicants(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'campus_programs'=>CampusProgram::whereHas('program',function($query){
                 $query->where('name','LIKE','%Basic%');
            })->where('campus_id',$staff->campus_id)->get(),
            'request'=>$request
        ];
        return view('dashboard.application.tamisemi-applicants',$data)->withTitle('TAMISEMI Applicants');
    }

    /**
     * Download TAMISEMI applicants
     */
    public function downloadTamisemiApplicants(Request $request)
    {
        $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        $applyr = 2020;
        $application_window = ApplicationWindow::with('intake')->find($request->get('application_window_id'));
        $campus_program = CampusProgram::with(['program','entryRequirements'])->find($request->get('campus_program_id'));
        $program = $campus_program;

        if(count($program->entryRequirements) == 0){
            return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
        }

        if($program->entryRequirements[0]->max_capacity == null){
            return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
        }
        $has_must_subjects = false;
        if(unserialize($campus_program->entryRequirements[0]->must_subjects) != null){
           $has_must_subjects = true;
        }
        $appacyr = $ac_year->academicYear->year;
        $intake = $application_window->intake->name;
        $nactecode = $campus_program->regulator_code;
        $token = config('constants.NACTE_API_KEY');
        $url="https://www.nacte.go.tz/nacteapi/index.php/api/tamisemiconfirmedlist/".$nactecode."-".$applyr."-".$intake."/".$token;

        $arrContextOptions=array(
            "ssl"=>array(
              "verify_peer"=> false,
              "verify_peer_name"=> false,
            ),
          );
          
          $jsondata = file_get_contents($url,false, stream_context_create($arrContextOptions)); 

          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
          curl_setopt($curl, CURLOPT_POST, true);
          //curl_setopt($curl, CURLOPT_POSTFIELDS, $jsondata);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
          $jsondata= curl_exec($curl);
            curl_close($curl);
             $returnedObject = json_decode($jsondata);

             if(!isset($returnedObject->params)){
                return redirect()->back()->with('error','No students to retrieve from TAMISEMI for selected programme');
             }

             // return $returnedObject;
          //echo $returnedObject->params[0]->student_verification_id."-dsdsdsdsds-<br />";
          // check for parse errors json_last_error() == JSON_ERROR_NONE
          if (isset($returnedObject->params)) {
            if(count($returnedObject->params)>0){
              for($i=0;$i<count($returnedObject->params);$i++){
                // $parts=explode("/",$returnedObject->params[$i]->username);
                // //create format from returned form four index format 
                // $form4index=$parts[0]."-".$parts[1];
                // $year=$parts[2];
                // if (strpos($returnedObject->params[$i]->username, ',') !== false) {
                //   $form4index=$parts[0]."-".$parts[1]."-".$parts[2];
                //   $year=$parts[3];
                // }
                $form4index = $returnedObject->params[$i]->username;

                if($stud = TamisemiStudent::where('f4indexno',$form4index)->first()){
                   $student = $stud;
                }else{
                   $student = new TamisemiStudent;
                }
                $student->f4indexno = $form4index;
                $student->year = $applyr;
                $student->fullname = str_replace("'","\'",$returnedObject->params[$i]->fullname);
                $student->year = $returnedObject->params[$i]->application_year;
                $student->programme_id = $nactecode;
                $student->programme_name = $returnedObject->params[$i]->programe_name;
                $student->campus = $returnedObject->params[$i]->institution_name;
                $student->gender = $returnedObject->params[$i]->sex;
                $student->date_of_birth = DateMaker::toDBDate($returnedObject->params[$i]->date_of_birth);
                $student->phone_number = $returnedObject->params[$i]->phone_number;
                $student->email = str_replace("'","\'",$returnedObject->params[$i]->email);
                $student->address = str_replace("'","\'",$returnedObject->params[$i]->address);
                $student->district = str_replace("'","\'",$returnedObject->params[$i]->district);
                $student->region = str_replace("'","\'",$returnedObject->params[$i]->region);
                $student->next_of_kin_fullname = str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_fullname);
                $student->next_of_kin_phone_number = $returnedObject->params[$i]->Next_of_kin_phone_number;
                $student->next_of_kin_email = str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_email);
                $student->next_of_kin_address = str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_address);
                $student->next_of_kin_region = str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_region);
                $student->relationship = $returnedObject->params[$i]->relationship;
                $student->appacyr = $appacyr;
                $student->intake = $intake;
                $student->receiveDate = now();
                $student->save();
                
                $program_level = Award::where('name','LIKE','%Basic%')->first();
                // $next_of_kin = new NextOfKin;
                // $next_of_kin->first_name = explode(' ', $student->next_of_kin_fullname)[0];
                // $next_of_kin->middle_name = count(explode(' ', $student->next_of_kin_fullname)) == 3? explode(' ',$student->next_of_kin_fullname)[1] : null;
                // $next_of_kin->surname = count(explode(' ', $student->next_of_kin_fullname)) == 3? explode(' ', $student->next_of_kin_fullname)[2] : explode(' ',$student->next_of_kin_fullname)[1];
                // $next_of_kin->address = $student->next_of_kin_address;
                // $next_of_kin->phone = $student->next_of_kin_phone;
                // $next_of_kin->email = $student->Next_of_kin_email;
                // $next_of_kin->relationship = $student->relationship;
                // $next_of_kin->save();

                // $region = Region::where('name',$student->region)->first();
                // $district = District::where('name',$student->district)->first();
                // $ward = Ward::where('district_id',$district->id)->first();
                $surname = count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1];

                if($us = User::where('username',$form4index)->first()){
                    $user = $us;
                }else{
                    $user = new User;
                }
                $user->username = $form4index;
                $user->email = 'dennis.lupiana@gmail.com';//$student->email;
                $user->password = Hash::make($surname);
                $user->save();

                $role = Role::where('name','applicant')->first();
                $user->roles()->sync([$role->id]);

                if($app = Applicant::where('index_number',$form4index)->where('campus_id',$campus_program->campus_id)->where('application_window_id',$application_window->id)->where('is_tamisemi',1)->first()){
                   $applicant = $app;
                }else{
                   $applicant = new Applicant;
                }
                $applicant->first_name = explode(' ', $student->fullname)[0];
                $applicant->middle_name = count(explode(' ', $student->fullname)) == 3? explode(' ',$student->fullname)[1] : null;
                $applicant->surname = count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1];
                $applicant->phone = '225'.substr($student->phone_number,1);
                $applicant->email = $student->email;
                $applicant->address = $student->address;
                $applicant->gender = substr($student->gender, 0,1);
                $applicant->campus_id = $campus_program->campus_id;
                $applicant->program_level_id = $program_level->id;
                // $applicant->next_of_kin_id = $next_of_kin->id;
                $applicant->application_window_id = $application_window->id;
                $applicant->payment_complete_status = 1;
                $applicant->intake_id = $application_window->intake->id;
                $applicant->index_number = $form4index;
                $applicant->admission_year = $applyr;
                $applicant->entry_mode = 'DIRECT';
                $applicant->nationality = 'Tanzanian';
                $applicant->birth_date = $student->date_of_birth;
                $applicant->country_id = 1;
                $applicant->user_id = $user->id;
                $applicant->is_tamisemi = 1;
                $applicant->save();

                if($select = ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('application_window_id',$application_window->id)->first()){
                    $selection = $select;
                }else{
                    $selection = new ApplicantProgramSelection;
                }
                $selection->campus_program_id = $campus_program->id;
                $selection->applicant_id = $applicant->id;
                $selection->application_window_id = $application_window->id;
                $selection->order = 1;
                $selection->status = 'SELECTED';
                $selection->save();

                try{
                    Mail::to($user)->queue(new TamisemiApplicantCreated($student,$applicant,$campus_program->program->name));
                }catch(\Exception $e){}
                                
            }
          }
        }//end
        
        if($has_must_subjects){
        $applicants = Applicant::where('application_window_id',$application_window->id)->where('is_tamisemi',1)->get();
        foreach($applicants as $applicant){
            $parts=explode("/",$applicant->index_number);
            //create format from returned form four index format 
            $index_no = $parts[0]."-".$parts[1];
            $exam_year = $parts[2];
            $exam_id = 1;
            $response = Http::get('https://api.necta.go.tz/api/public/auth/'.config('constants.NECTA_API_KEY'));
            $token = json_decode($response)->token;
            $response = Http::get('https://api.necta.go.tz/api/public/results/'.$index_no.'/'.$exam_id.'/'.$exam_year.'/'.$token);
            if(!isset(json_decode($response)->results)){
                return redirect()->back()->with('error','Invalid Index number or year');
            }
            if($det = NectaResultDetail::where('index_number',$index_no)->where('exam_id',$exam_id)->where('applicant_id',$applicant->id)->first()){
                $detail = $det;
            }else{
                $detail = new NectaResultDetail;
                $detail->center_name = json_decode($response)->particulars->center_name;
                $detail->center_number = json_decode($response)->particulars->center_number;
                $detail->first_name = json_decode($response)->particulars->first_name;
                $detail->middle_name = json_decode($response)->particulars->middle_name;
                $detail->last_name = json_decode($response)->particulars->last_name;
                $detail->sex = json_decode($response)->particulars->sex;
                $detail->index_number = str_replace('-','/',$index_no); //json_decode($response)->particulars->index_number;
                $detail->division = json_decode($response)->results->division->division;
                $detail->points = json_decode($response)->results->division->points;
                $detail->exam_id = $exam_id;
                $detail->applicant_id = $applicant->id;
                $detail->save();
            }
            foreach(json_decode($response)->results->subjects as $subject){
                if($rs = NectaResult::where('subject_code',$subject->subject_code)->where('necta_result_detail_id',$detail->id)->first()){
                    $res = $rs;
                }else{
                    $res = new NectaResult;
                }
                $res->subject_name = $subject->subject_name;
                $res->subject_code = $subject->subject_code;
                $res->grade = $subject->grade;
                $res->applicant_id = $applicant->id;
                $res->necta_result_detail_id = $detail->id;
                $res->save();
            }
        }



        $applicants = Applicant::with(['selections','nectaResultDetails.results','programLevel'])->where('application_window_id',$application_window->id)->where('is_tamisemi',1)->get();
        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $selected_program = array();
        
        foreach($applicants as $applicant){
           $award = $applicant->programLevel;
           $index_number = $applicant->index_number;
           $exam_year = explode('/', $index_number)[2];
          
           foreach($applicant->nectaResultDetails as $detail) {
              if($detail->exam_id == 2){
                  $index_number = $detail->index_number;
                  $exam_year = explode('/', $index_number)[2];
              }
           }

           if($exam_year < 2014 || $exam_year > 2015){
             $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
           }else{
             $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'S'=>0.5,'F'=>0];
           }
           $selected_program[$applicant->id] = false;
           $subject_count = 0;
           foreach($applicant->selections as $selection){
                 
                  $program = $campus_program;

                  if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                  }

                  if($program->entryRequirements[0]->max_capacity == null){
                    return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                  }

                   // Certificate
                   if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {
                          
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                    $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }
                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'SELECTED';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }
                   }

                   // Diploma
                   if(str_contains($award->name,'Diploma')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_subsidiary_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;


                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                     $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                    }
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'SELECTED';
                           $select->status_changed_at = now();
                           $select->save();
                         }

                         
                       }
                       $has_btc = false;
                       foreach($applicant->nacteResultDetails as $detailKey=>$detail){
                          if(str_contains($detail->programme,'BASIC TECHNICIAN CERTIFICATE')){
                              $has_btc = true;
                          }
                       }

                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'SELECTED';
                           $select->status_changed_at = now();
                           $select->save();
                       }
                   }
                   
                   // Bachelor
                   if(str_contains($award->name,'Bachelor')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_principle_pass_points = 0;
                       $a_level_subsidiary_pass_count = 0;
                       $diploma_pass_count = 0;
                       
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                 $applicant->rank_points += $o_level_grades[$result->grade];
                                 $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                      $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                    }
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'SELECTED';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }
                   }
                }
            }
        }

        return redirect()->back()->with('message','TAMISEMI applicants retrieved successfully');
    }


    /**
     * Fetch results from NECTA
     */
    public function getNectaResults(Request $request)
    {
        return view('dashboard.application.admin-request-results')->withTitle('Fetch Results');
    }

    /**
     * Update teacher's certificate status
     */
    public function updateTeacherCertificateStatus(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'teacher_certificate_status'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $applicant = Applicant::find($request->get('applicant_id'));
        $applicant->teacher_certificate_status = $request->get('teacher_certificate_status');
        $applicant->save();

        return redirect()->back()->with('message','Teacher certificate status updated successfully');
    }
}
