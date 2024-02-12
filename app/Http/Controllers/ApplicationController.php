<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Stream;
use App\Domain\Academic\Models\Group;
use App\Domain\Academic\Models\Department;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\NactePayment;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\SpecialDate;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\AcademicStatus;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\InsuranceRegistration;
use App\Domain\Application\Models\TamisemiStudent;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Application\Models\ExternalTransfer;
use App\Domain\Application\Models\AdmissionAttachment;
use App\Domain\Application\Models\ApplicantSubmissionLog;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Actions\ApplicantAction;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Settings\Models\Currency;
use App\Domain\Application\Models\HealthInsurance;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\NHIFService;
use App\Models\User;
use App\Models\Role;
use App\Utils\SystemLocation;
use App\Utils\Util;
use App\Jobs\SendAdmissionLetterJob;
use App\Mail\AdmissionLetterCreated;
use App\Mail\StudentAccountCreated;
use NumberToWords\NumberToWords;
use App\Utils\DateMaker;
use App\Services\ACPACService;
use Carbon\Carbon;
use Validator, Hash, Config, Auth, Mail, PDF, DB;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Application\Models\ApplicationBatch;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\ApplicantFeedBackCorrection;
use App\Domain\Application\Models\AdmissionReferenceNumber;
use App\Domain\Application\Models\OutResult;
use App\Domain\Application\Models\OutResultDetail;

class ApplicationController extends Controller
{
    /**
     * Disaplay form for application
     */
    public function index(Request $request)
    {
    	$data = [
           'awards'=>Award::all(),
           'intakes'=>Intake::all(),
           'certificate_window'=> ApplicationWindow::where('status','ACTIVE')->whereColumn('begin_date','!=','end_date')->first()? true : false,
           'bsc_window'=> ApplicationWindow::where('status','ACTIVE')->whereColumn('begin_date','!=','bsc_end_date')->first()? true : false,
           'msc_window'=> ApplicationWindow::where('status','ACTIVE')->whereColumn('begin_date','!=','msc_end_date')->first()? true : false
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
        $applicants = null;
        if($request->get('department_id') != null){
            $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))
						                    ->whereHas('selections.campusProgram.program.departments',function($query) use($request){$query->where('id',$request->get('department_id'));})
						                    ->paginate(500);

        }elseif($request->get('duration') == 'today'){
            $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('created_at','<=',now()->subDays(1))
                                            ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

        }elseif($request->get('gender') != null){
           $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('gender',$request->get('gender'))
                                            ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

        }elseif($request->get('nta_level_id') != null){
            $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))
                                            ->whereHas('selections.campusProgram.program',function($query) use($request){$query->where('nta_level_id',$request->get('nta_level_id'));})->paginate(500);

        }elseif($request->get('campus_program_id') != null){
            $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])
                                            ->whereHas('selections',function($query) use($request){$query->where('campus_program_id',$request->get('campus_program_id'));})
                                            ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

        }else{
            $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                            'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                            ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                            'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))->paginate(500);

        }

        if($request->get('status') == 'progress'){
            if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
               $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('programs_complete_status',0)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

            }elseif(Auth::user()->hasRole('hod')){
               $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('programs_complete_status',0)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)
                                                ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})->paginate(500);
            }else{
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id','selections.campusProgram.program:id,code',
                                                'programLevel:id,name,code'])->where('programs_complete_status',0)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)->paginate(500);

            }

        }elseif($request->get('status') == 'completed'){
            if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('programs_complete_status',1)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

            }elseif(Auth::user()->hasRole('hod')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('programs_complete_status',1)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)
                                                ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})->paginate(500);
            }else{
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id','selections.campusProgram.program:id,code',
                                                'programLevel:id,name,code'])->where('programs_complete_status',1)->where('submission_complete_status',0)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)->paginate(500);
            }

        }elseif($request->get('status') == 'submitted'){
            if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('submission_complete_status',1)
                                                ->where('application_window_id',$request->get('application_window_id'))->paginate(500);

            }elseif(Auth::user()->hasRole('hod')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('submission_complete_status',1)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)
                                                ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})->paginate(500);


            }else{
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('submission_complete_status',1)
                                                ->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)->paginate(500);

            }

        }elseif($request->get('status') == 'total'){
            if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id','selections.campusProgram.program:id,code',
                                                'programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))->paginate(500);

            }elseif(Auth::user()->hasRole('hod')){
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))
                                                ->where('campus_id',$staff->campus_id)
                                                ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})->paginate(500);

            }else{
                $applicants = Applicant::select('id','first_name','surname','index_number','gender','phone','batch_id','submission_complete_status','programs_complete_status',
                                                'basic_info_complete_status','next_of_kin_complete_status','payment_complete_status','results_complete_status','program_level_id')
                                                ->with(['selections:id,order,campus_program_id,applicant_id','selections.campusProgram:id,program_id',
                                                'selections.campusProgram.program:id,code','programLevel:id,name,code'])->where('application_window_id',$request->get('application_window_id'))
                                                ->where('campus_id',$staff->campus_id)->paginate(500);

            }
        }


        // $a_level = NectaResultDetail::where('applicant_id', $applicant->id)->where('exam_id', 2)->where('verified', 1)->first();

        // $avn = $request->get('index_number') ? NacteResultDetail::where('applicant_id', $applicant->id)->where('verified', 1)->first() : null;

        // $out = $request->get('index_number') ? OutResultDetail::where('applicant_id', $applicant->id)->where('verified', 1)->first() : null;

        $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'application_window'=>$application_window,
            'nta_levels'=>NTALevel::all(),
            'departments'=>Department::all(),
            'campus_programs'=>CampusProgram::with('program')->get(),
            'applicants'=>$applicants,
            'request'=>$request,
            'batches'=>ApplicationBatch::all()
        ];
        return view('dashboard.application.applicants-list',$data)->withTitle('Applicants');
    }

	/**
	 * Reset selections
	 */
	 public function resetSelections(Request $request)
	 {
		 $staff = User::find(Auth::user()->id)->staff;

         // removed selected status where clause
         // update `applicant_program_selections`
         // set `status` = ELIGIBLE, `applicant_program_selections`.`updated_at` = 2022-09-23 11:04:55
         // where exists (select * from `applicants`
         //    where `applicant_program_selections`.`applicant_id` = `applicants`.`id` and (`campus_id` = 1 and `status` != ADMITTED or `status` != SUBMITTED)) and `application_window_id` = 23 and `program_level_id` is null

         // ->where('name', '=', 'John')
         //   ->where(function ($query) {
         //       $query->where('status','!=','ADMITTED')
         //             ->orWhere('status','!=','SUBMITTED');
         //   })
/*
		 ApplicantProgramSelection::whereHas('applicant',function($query) use($staff){
			 $query->where('campus_id',$staff->campus_id)
             ->where('application_window_id',$request->get('application_window_id'))
             ->where('program_level_id',$request->get('program_level_id'))
             ->where(function ($query) {
               $query->where('status','!=','ADMITTED')
                     ->orWhere('status','!=','SUBMITTED');

		      })->update(['status'=>'ELIGIBLE']);

         });*/
/*whereHas('author', function ($query) use ($keyword)
                     {
                          $query->where('surname', 'LIKE', '%'.$keyword.'%')
                               ->orWhere('name', 'LIKE', '%'.$keyword.'%');
                     })

                     Book::whereHas('author', function ($query) use ($keyword) {
        $query->where(function ($q) use ($keyword) {
            $q->where('surname', 'LIKE', '%'.$keyword.'%')
                ->orWhere('name', 'LIKE', '%'.$keyword.'%');
        });
    })

    function($query) use($staff, $request){
             $query->where(function ($q) use($staff, $request) {
               $q->where('campus_id',$staff->campus_id)
                 ->where('application_window_id',$request->get('application_window_id'))
                 ->where('program_level_id',$request->get('program_level_id'))
                 ->where(function ($s){
                     $s->where('status','!=','ADMITTED')
                       ->orWherikole('status','!=','SUBMITTED');
                 });

              });

          ApplicantProgramSelection::whereHas('applicant',function($query) use($staff, $request){
         return ApplicantProgramSelection::whereHas('applicant',function($query) use($staff, $request){




*/

        $batch_id = $batch_no = 0;
        if(!empty($request->get('program_level_id'))){
            $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))
                                        ->where('program_level_id',$request->get('program_level_id'))->latest()->first();
            if($batch->batch_no > 1){
                if(Applicant::whereHas('selections',function($query) use($request, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                    ->where('application_window_id',$request->get('application_window_id'))
                    ->where('batch_id',$batch->id);})
                    ->where('application_window_id', $request->get('application_window_id'))
                    ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                            $batch_id = $batch->id;
                            $batch_no = $batch->batch_no;

                        }else{

                    $previous_batch = null;
                    if($batch->batch_no > 1){
                        $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))
                                                            ->where('batch_no', $batch->batch_no - 1)->first();
                        $batch_id = $previous_batch->id;
                        $batch_no = $previous_batch->batch_no;
                    }
                }
            }else{
                $batch_id = $batch->id;
                $batch_no = $batch->batch_no;
            }

        }

        if(Auth::user()->hasRole('admission-officer')) {
            ApplicantProgramSelection::whereHas('applicant',function($query) use($staff, $request, $batch_id){$query->where('campus_id',$staff->campus_id)
                                     ->where('application_window_id',$request->get('application_window_id'))
                                     ->where('program_level_id',$request->get('program_level_id'))->where('batch_id',$batch_id)
                                     ->whereNotIn('status', ['ADMITTED', 'SUBMITTED', 'NULL']);})->update(['status'=>'ELIGIBLE']);

            Applicant::where('application_window_id',$request->get('application_window_id'))->where('campus_id',$staff->campus_id)->where('batch_id',$batch_id)
                     ->where('program_level_id',$request->get('program_level_id'))->whereNotIn('status', ['ADMITTED', 'SUBMITTED', 'NULL'])->update(['status'=>null]);

        }else{
            return redirect()->back()->with('error','Sorry, this task can only be done by a respective Admission Officer.');
        }


		 return redirect()->back()->with('message','Selections reset successfully');
	 }

    /**
     * Admitted studdents who have cancelled Admission
     */
     public function cancelledApplicants(Request $request)
     {
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;

        $batch_id = 0;

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->latest()->first();

        if(!empty($request->get('program_level_id'))){
            if($batch->batch_no > 1){
                    if(Applicant::whereHas('selections',function($query) use($request, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                        ->where('application_window_id',$request->get('application_window_id'))
                        ->where('batch_id',$batch->id);})
                        ->where('application_window_id', $request->get('application_window_id'))
                        ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                                $batch_id = $batch->id;

                            }else{
                    $previous_batch = null;

                    $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where('batch_no', $batch->batch_no - 1)->first();
                    $batch_id = $previous_batch->id;
                }
            }else{
                $batch_id = $batch->id;
            }
        }

        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {

           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->where(function($query){$query->where('status', 'ADMITTED')->where('confirmation_status', 'CANCELLED');})
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,verified'])->paginate(500);

        }elseif(Auth::user()->hasRole('hod')){

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})
                                    ->where('programs_complete_status',1)
                                    ->where(function($query){$query->where('status', 'ADMITTED')->where('confirmation_status', 'CANCELLED');})
                                    ->where('campus_id', $staff->campus_id)
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,verified'])->paginate(500);
        }else{

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->where('programs_complete_status',1)
                                    ->where('campus_id', $staff->campus_id)
                                    ->where(function($query){$query->where('status', 'ADMITTED')->where('confirmation_status', 'CANCELLED');})
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,verified'])->paginate(500);

        }

        $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->whereIn('status',['APPROVING','PENDING']);
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'confirmed_campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request,$batch_id){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','SELECTED')->where('batch_id',$batch_id);
            })->whereHas('selections.applicant',function($query){
                 $query->where('multiple_admissions',1);
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'applicants'=>$applicants,
            'request'=>$request,
            //'selection_status'=> $selection_status > 0? false : true,
            'batches'=> ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get(),
         ];
         return view('dashboard.application.cancelled-admitted-applicants',$data)->withTitle('Cancelled Applicants');


     }

    /**
     * Selected applicants
     */
    public function selectedApplicants(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;

        $batch_id = 0;

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->latest()->first();

        if(!empty($request->get('program_level_id'))){
            if($batch->batch_no > 1){
                    if(Applicant::whereHas('selections',function($query) use($request, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                        ->where('application_window_id',$request->get('application_window_id'))
                        ->where('batch_id',$batch->id);})
                        ->where('application_window_id', $request->get('application_window_id'))
                        ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                                $batch_id = $batch->id;

                            }else{
                    $previous_batch = null;

                    $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where('batch_no', $batch->batch_no - 1)->first();
                    $batch_id = $previous_batch->id;
                }
            }else{
                $batch_id = $batch->id;
            }
        }

        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {

           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->where(function($query){$query->whereNull('status')->orWhereIn('status',['SELECTED','SUBMITTED','NOT SELECTED']);})
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,verified'])->orderBy('batch_id', 'DESC')->paginate(500);

        }elseif(Auth::user()->hasRole('hod')){

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->whereHas('selections.campusProgram.program.departments',function($query) use($staff){$query->where('department_id',$staff->department_id);})
                                    ->where('programs_complete_status',1)
                                    ->where('status','!=','ADMITTED')
                                    ->where('campus_id', $staff->campus_id)
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,verified'])->orderBy('batch_id', 'DESC')->paginate(500);

        }else{
            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','entry_mode','status','multiple_admissions',
                                            'confirmation_status','admission_confirmation_status','program_level_id')->doesntHave('student')
                                    ->whereHas('selections',function($query){$query->whereIn('status',['APPROVING','SELECTED','ELIGIBLE']);})
                                    ->where('programs_complete_status',1)
                                    ->where('campus_id', $staff->campus_id)
                                    ->where('status','!=','ADMITTED')
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code',
                                            'nectaResultDetails:id,applicant_id,index_number,exam_id,verified','nacteResultDetails:id,applicant_id,avn,registration_number,verified'])
                                    ->orderBy('batch_id', 'DESC')->paginate(500);
        }

        // Ready to be sent to regulators i.e. NACTVET and TCU
        $selected_applicants = $selected_applicant_new = [];
        if($request->get('program_level_id') == 1 || $request->get('program_level_id') == 2){
                $selected_applicants = Applicant::select('id','first_name','middle_name','surname','gender','batch_id','index_number','status')->doesntHave('student')->whereDoesntHave('selections',function($query){$query->whereIn('status',['SELECTED','PENDING']);})->whereHas('selections',function($query){$query->where('status','APPROVING');})
                                        ->where('status','SELECTED')
                                        ->where('application_window_id',$request->get('application_window_id'))
                                        ->where('program_level_id',$request->get('program_level_id'))
                                        ->where('programs_complete_status',1)
                                        ->get();

         }elseif($request->get('program_level_id') == 4){
         $selected_applicants = Applicant::select('id','first_name','middle_name','surname','gender','batch_id','index_number','status')->doesntHave('student')
                                            ->whereDoesntHave('selections',function($query){$query->whereIn('status',['SELECTED','PENDING']);})
                                            ->whereIn('status',['SELECTED','NOT SELECTED'])
                                            ->where('application_window_id',$request->get('application_window_id'))
                                            ->where('program_level_id',$request->get('program_level_id'))
                                            ->where('programs_complete_status',1)
                                            ->get();
         foreach($selected_applicants as $selected_applicant){

            if(ApplicantSubmissionLog::where('applicant_id',$selected_applicant->id)->where('application_window_id',$request->get('application_window_id'))
            ->where('batch_id',$selected_applicant->batch_id)->where('program_level_id',4)->count() == 0){
                $selected_applicant_new[] = $selected_applicant;

            }
         }
         }


        $submission_status = Applicant::doesntHave('student')
                ->where('application_window_id',$request->get('application_window_id'))
                ->where('program_level_id',$request->get('program_level_id'))
                ->where('status','SUBMITTED')->count();

         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>$selected_applicant_new,
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->whereIn('status',['APPROVING','PENDING']);
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'confirmed_campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request,$batch_id){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','SELECTED')->where('batch_id',$batch_id);
            })->whereHas('selections.applicant',function($query){
                 $query->where('multiple_admissions',1);
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'applicants'=>$applicants,
            'submission_logs'=>ApplicantSubmissionLog::where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))->where('batch_id', $batch_id)->get(),
            'request'=>$request,
            //'selection_status'=> $selection_status > 0? false : true,
            'batches'=> ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get(),
            'confirmation_status'=>Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))
                                            ->where('multiple_admissions',1)->whereIn('confirmation_status',['CONFIRMED','NOT CONFIRMED'])->count()>0? true : false,
            'submission_status' => $submission_status
         ];
         return view('dashboard.application.selected-applicants',$data)->withTitle('Selected Applicants');
    }


    /**
     * Admitted applicants
     */
    public function admittedApplicants(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);
        $staff = User::find(Auth::user()->id)->staff;
        $campus_id = $staff->campus_id;
		$applicants = null;

         if (Auth::user()->hasRole('administrator')|| Auth::user()->hasRole('arc')) {

/*             $applicants = Applicant::doesntHave('student')->whereHas('selections',function($query) use($request){
                $query->where('status','SELECTED');
           })->with(['disabilityStatus','ward','region','country','nextOfKin','intake','selections.campusProgram.program','nectaResultDetails','nacteResultDetails'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
               $query->where('confirmation_status','!=','CANCELLED')->orWhere('confirmation_status','!=','TRANSFERED')->orWhereNull('confirmation_status');
           })->where('status','ADMITTED')->get(); */

           $applicants = Applicant::select('id','first_name','middle_name','surname','gender','birth_date','nationality','email','phone','address','disability_status_id','batch_id','index_number','intake_id',
                                            'status','entry_mode','ward_id','district_id','region_id','country_id','next_of_kin_id')
                                    ->with([
                                        'disabilityStatus:id,name',
                                        'ward:id,name',
                                        'region:id,name',
                                        'country:id,name',
                                        'nextOfKin:id,first_name,middle_name,surname,gender,address,phone,nationality,relationship,ward_id,district_id,region_id,country_id',
                                        'intake:id,name',
                                        'selections'=>function($query){$query->select('id','applicant_id','campus_program_id','status')->where('status','SELECTED');},
                                        'selections.campusProgram:id,code',
                                        'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number')->where('exam_id',2)->where('verified',1);},
                                        'nacteResultDetails'=>function($query){$query->select('id','applicant_id','avn')->where('verified',1);}
                                    ])
                                    ->doesntHave('student')
                                    ->whereHas('selections',function($query) use($request) {$query->where('status','SELECTED')->where('application_window_id',$request->get('application_window_id'));})
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    //->where(function($query){$query->where('confirmation_status','!=','CANCELLED')->orWhere('confirmation_status','!=','TRANSFERED')->orWhereNull('confirmation_status');})
                                    ->where('status','ADMITTED')->get();

                                    // dd($applicants);

         }elseif (Auth::user()->hasRole('admission-officer')) {

/*             $applicants = Applicant::doesntHave('student')->whereHas('selections',function($query) use($request){
                $query->where('status','SELECTED');
           })->with(['disabilityStatus','ward','region','country','nextOfKin','intake','selections.campusProgram.program','nectaResultDetails','nacteResultDetails'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
               $query->where('confirmation_status','!=','CANCELLED')->orWhere('confirmation_status','!=','TRANSFERED')->orWhereNull('confirmation_status');
           })->where('campus_id', $campus_id)->where('status','ADMITTED')->get();
 */
           $applicants = Applicant::select('id','first_name','middle_name','surname','gender','birth_date','nationality','email','phone','address','disability_status_id','batch_id','index_number','intake_id',
                                            'status','entry_mode','ward_id','district_id','region_id','country_id','next_of_kin_id')
                                    ->with([
                                        'disabilityStatus:id,name',
                                        'ward:id,name',
                                        'region:id,name',
                                        'country:id,name',
                                        'nextOfKin:id,first_name,middle_name,surname,gender,address,phone,nationality,relationship,ward_id,district_id,region_id,country_id',
                                        'intake:id,name',
                                        'selections'=>function($query){$query->select('id','applicant_id','campus_program_id','status')->where('status','SELECTED');},
                                        'selections.campusProgram:id,code',
                                        'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number')->where('exam_id',2)->where('verified',1);},
                                        'nacteResultDetails'=>function($query){$query->select('id','applicant_id','avn')->where('verified',1);}
                                    ])
                                    ->doesntHave('student')
                                    ->whereHas('selections',function($query) use($request) {$query->where('status','SELECTED')->where('application_window_id',$request->get('application_window_id'));})
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    //->where(function($query){$query->where('confirmation_status','!=','CANCELLED')->orWhere('confirmation_status','!=','TRANSFERED')->orWhereNull('confirmation_status');})
                                    ->where('campus_id', $campus_id)
                                    ->where('status','ADMITTED')->get();


         }elseif (Auth::user()->hasRole('hod')) {

            $applicants = Applicant::select('id','first_name','middle_name','surname','gender','birth_date','nationality','email','phone','address','disability_status_id','batch_id','index_number','intake_id',
                                            'status','entry_mode','ward_id','district_id','region_id','country_id','next_of_kin_id')
                                    ->with([
                                        'disabilityStatus:id,name',
                                        'ward:id,name',
                                        'region:id,name',
                                        'country:id,name',
                                        'nextOfKin:id,first_name,middle_name,surname,gender,address,phone,nationality,relationship,ward_id,district_id,region_id,country_id',
                                        'intake:id,name',
                                        'selections'=>function($query){$query->select('id','applicant_id','campus_program_id','status')->where('status','SELECTED');},
                                        'selections.campusProgram:id,code',
                                        'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number')->where('exam_id',2)->where('verified',1);},
                                        'nacteResultDetails'=>function($query){$query->select('id','applicant_id','avn')->where('verified',1);}
                                    ])
                                    ->doesntHave('student')
                                    ->whereHas('selections',function($query) {$query->where('status','SELECTED');})
                                    ->whereHas('selections.campusProgram.program.departments',function($query) use($staff) {$query->where('department_id',$staff->department_id);})
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->where('program_level_id',$request->get('program_level_id'))
                                    //->where(function($query){$query->where('confirmation_status','!=','CANCELLED')->orWhere('confirmation_status','!=','TRANSFERED')->orWhereNull('confirmation_status');})
                                    ->where('campus_id', $campus_id)
                                    ->where('status','ADMITTED')->get();

         }

         if(count($applicants) == 0 && !empty($request->get('program_level_id'))){
            return redirect()->back()->with('error','No admitted applicants for this application window');
         }

         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'))->where('status','SELECTED');
            })->whereHas('program',function($query) use($request){
                  $query->where('award_id',$request->get('program_level_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'request'=>$request,
            'batches'=>ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->get(),
         ];
         return view('dashboard.admission.admitted-applicants',$data)->withTitle('Admitted Applicants');
    }

    /**
     * Download admitted applicants
     */

     public function downloadAdmittedApplicants(Request $request){

        if(!$request->get('program_level_id')){
            return redirect()->back()->with('error','Please select program level first');
        }
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));
        $headers = [
                        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                        'Content-type'        => 'text/csv',
                        'Content-Disposition' => 'attachment; filename=Admitted-Applicants-'.$award->name.'_'.date('d-m-Y_h:i').'.csv',
                        'Expires'             => '0',
                        'Pragma'              => 'public'
                ];

        $list = Applicant::select('id','first_name','middle_name','surname','index_number','gender','birth_date','batch_id','nationality','entry_mode','phone',
                                'email','status', 'country_id','region_id','district_id','disability_status_id','next_of_kin_id')
                         ->doesntHave('student')
                         ->where('program_level_id', $request->get('program_level_id'))
                         ->where('application_window_id',$request->get('application_window_id'))
                         ->where('status','ADMITTED')
                         ->with([
                            'selections'=>function($query){$query->select('id','campus_program_id','applicant_id','status')->where('status','SELECTED');},
                            'selections.campusProgram:id,code,campus_id',
                            'nectaResultDetails:id,applicant_id,index_number,verified,center_name,points,exam_id',
                            'nectaResultDetails.results:id,necta_result_detail_id,subject_name,grade',
                            'nacteResultDetails:id,applicant_id,avn,verified,diploma_gpa,programme,institution',
                            'nacteResultDetails.results:id,nacte_result_detail_id,subject,grade',
                            'region:id,name',
                            'district:id,name',
                            'disabilityStatus:id,name',
                            'outResultDetails',
                            'outResultDetails:id,gpa,reg_no,applicant_id',
                            'outResultDetails.results:id,subject_name,grade,out_result_detail_id',
                            'nextOfKin:id,phone'
                         ])
                         ->get();

            $batches = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))
                                        ->where('program_level_id', $request->get('program_level_id'))->get();
            $callback = function() use ($list, $batches)
            {
                $o_level_schools    = null;
                $a_level_schools    = null;

                $file_handle = fopen('php://output', 'w');
                fputcsv($file_handle,['S/N', 'FIRST NAME','MIDDLE NAME','SURNAME','SEX', 'NATIONALITY', 'DISABILITY', 'DATEOFBIRTH', 'F4INDEXNO', 'F6INDEXNO', 'AVN NO', 'PROGRAMME ADMITTED', 'INSTITUTION CODE', 'ENTRY CATEGORY', 'OPTS', 'O-LEVEL RESULTS', 'APTS/GPA', 'A-LEVEL RESULTS/DIPLOMA', 'OPEN GPA', 'OPEN RESULTS', 'POSTPONE', 'PHONE NUMBER', 'EMAIL ADDRESS', 'KIN PHONE NUMBER', 'DISTRICT', 'REGION', 'BATCH NO', 'DIPLOMA INSTITUTE', 'PROGRAM COURSE', 'DIPLOMA GPA', 'DIPLOMA RESULTS', 'O-LEVEL SCHOOL', 'CSEE PTS', 'A-LEVEL SCHOOL', 'ACSEE PTS']);
                foreach ($list as $key => $applicant) {

                $batch_number = null;
                foreach($batches as $batch){
                    if($batch->id == $applicant->batch_id){
                        $batch_number = $batch->batch_no;
                        break;
                    }
                }

                $institution_code = $selected_programme = null;

                if($applicant->program_level_id == 1){
                    if($applicant->selections[0]->campusProgram->campus_id == 1) {

                            $institution_code = substr($applicant->selections[0]->campusProgram->code, 0, 3);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }elseif($applicant->selections[0]->campusProgram->campus_id == 2){
                            $institution_code = substr($applicant->selection[0]->campusProgram->code, 0, 4);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }elseif($applicant->selections[0]->campusProgram->campus_id == 3){
                            $institution_code = substr($applicant->selections[0]->campusProgram->code, 0, 4);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }
                }else{
                    if($applicant->selections[0]->campusProgram->campus_id == 1){
                            $institution_code = substr($applicant->selections[0]->campusProgram->code, 0, 2);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }elseif($applicant->selections[0]->campusProgram->campus_id == 2){
                            $institution_code = substr($applicant->selections[0]->campusProgram->code, 0, 3);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }elseif($applicant->selections[0]->campusProgram->campus_id == 3){
                            $institution_code = substr($applicant->selections[0]->campusProgram->code, 0, 3);
                            $selected_programme = $applicant->selections[0]->campusProgram->code;

                    }
                }

                $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

                $exam_year = null;
                foreach($applicant->nectaResultDetails as $detail) {
                    if($detail->exam_id == 2 && $detail->verified == 1){
                        $index_number = $detail->index_number;
                        if(str_contains($index_number,'EQ')){
                            $exam_year = explode('/',$index_number)[1];
                        }else{
                            $exam_year = explode('/', $index_number)[2];
                        }
                        break;
                    }
                }

                $a_level_grades = [];
                if($exam_year < 2014 || $exam_year > 2015){
                    $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];

                }else{
                    $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
                }

                $o_level_points = null;
                $a_level_points = null;
                $o_level_results = [];
                $o_level_schools = [];
                foreach($applicant->nectaResultDetails as $detail){
                    if($detail->exam_id == 1 && $detail->verified == 1){

                        $o_level_schools = $detail->center_name;
                        $o_level_points = $detail->points;
                        foreach($detail->results as $result){
                            foreach($o_level_grades as $grade=>$points){
                                if($result->grade != '' && array_key_exists($result->grade,$o_level_grades)){
                                    $o_level_results[] = $result->subject_name.'-'.$result->grade.'('.$o_level_grades[$result->grade].') ';
                                    break;
                                }else{
                                    $o_level_results[] = $result->subject_name.'-'.$result->grade.' ';
                                    break;
                                }
                            }
                        }
                    }
                }

                $a_level_results = [];
                $diploma_results = [];
                $open_results    = [];
                $a_level_schools = [];
                $a_level_index   = [];

                foreach($applicant->nectaResultDetails as $detail){
                    if($detail->exam_id == 2 && $detail->verified == 1){
                        $a_level_schools = $detail->center_name;
                        $a_level_index[] = $detail->index_number;
                        foreach($detail->results as $result){
                            foreach($a_level_grades as $grade=>$points){
                                if($result->grade != '' && array_key_exists($result->grade,$a_level_grades)){
                                    $a_level_results[] = $result->subject_name.'-'.$result->grade.'('.$a_level_grades[$result->grade].') ';
                                    break;
                                }else{
                                    $a_level_results[] = $result->subject_name.'-'.$result->grade.' ';
                                    break;
                                }
                            }
                        }
                        $a_level_points = $detail->points;
                    } else {
                        $a_level_schools = null;
                    }
                }

                $diploma_gpa            = null;
                $diploma_institution    = null;
                $programme              = null;
                $avn                    = null;

                foreach ($applicant->nacteResultDetails as $nacte_results) {
                    if ($nacte_results->verified == 1) {

                        $diploma_gpa            = $nacte_results->diploma_gpa;
                        $diploma_institution    = $nacte_results->institution;
                        $programme              = $nacte_results->programme;
                        $avn                    = $nacte_results->avn;

                        foreach ($nacte_results->results as $result) {
                            $diploma_results[] = $result->subject.'-'.$result->grade.' ';
                        }
                    }
                }

                $out_gpa = null;
                foreach ($applicant->outResultDetails as $out_results) {
                    if ($out_results->verified == 1) {
                        $out_gpa        = $out_results->gpa;
                        $a_level_index  = $out_results->reg_no;

                        foreach($out_results->results as $result){
                            $open_results[] = $result->subject_name.'-'.$result->grade.' ';
                        }
                    }
                }

                if(is_array($a_level_index)){
                    $a_level_index=implode (',',$a_level_index);
                    }
                    if(is_array($a_level_results)){
                    $a_level_results=implode ($a_level_results);
                    }

                if(is_array($o_level_results)){
                    $o_level_results=implode (',',$o_level_results);
                }

                if(is_array($open_results)){
                    $open_results=implode ($open_results);
                }

                if(is_array($diploma_results)){
                    $diploma_results=implode ($diploma_results);
                }

                if(is_array($o_level_schools)){
                    $o_level_schools=implode ($o_level_schools);
                }

                if(is_array($a_level_schools)){
                    $a_level_schools=implode ($a_level_schools);
                }

                $phone = !empty($applicant->phone)? substr($applicant->phone,3) : null;
                $next_of_kin_phone = !empty($applicant->nextOfKin->phone)? substr($applicant->nextOfKin->phone,3) : null;

                $status = $applicant->has_postponed > 0? 'Postponed' : null;

                fputcsv($file_handle,
                [++$key, $applicant->first_name, $applicant->middle_name, $applicant->surname,
                $applicant->gender , $applicant->nationality, $applicant->disabilityStatus->name, $applicant->birth_date, $applicant->index_number,
                $a_level_index, $avn, $selected_programme, $institution_code, $applicant->entry_mode, 'OPTS', $o_level_results, 'APTS / GPA', $a_level_results,
                $out_gpa, $open_results, $status, $phone, $applicant->email, $next_of_kin_phone,  $applicant->district->name, $applicant->region->name, $batch_number,
                $diploma_institution, $programme, $diploma_gpa, $diploma_results, $o_level_schools, $o_level_points, $a_level_schools, $a_level_points
                ]);
            }
            fclose($file_handle);
        };
        //return $callback();
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Other applicants
     */

    public function otherApplicants(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

        if(Auth::user()->hasRole('admission-officer')){

            $applicants = Applicant::select('id','first_name','middle_name','surname','gender','index_number','status','program_level_id','batch_id')
                                    ->where('campus_id', $staff->campus_id)->where('programs_complete_status', 1)
                                    ->where(function($query) {$query->where('teacher_certificate_status', 1)
                                    ->orWhere('veta_status', 1)->orWhere('program_level_id','>','4')->orWhere('avn_no_results', 1);})
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->where(function($query){$query->whereNull('status')->orWhere('status','NOT SELECTED');})
                                    ->with(['intake','selections:id,applicant_id,campus_program_id','selections.campusProgram:id,code', 'nacteResultDetails:id,applicant_id,exam_id,verified',
                                            'nacteResultDetails:id,applicant_id,verified,avn','programLevel:id,code'])->get();

        }else{
            return redirect()->back()->with('error','This operation can only be done by an Admission Officer');
        }

        $batchids = [];
        foreach($applicants as $applicant){
            if(!in_array($applicant->batch_id,$batchids)){
                $batchids[] = $applicant->batch_id;
            }
        }
/*         $selection_status = ApplicantProgramSelection::whereHas('applicant',function($query) use($request){$query->where('program_level_id',$request->get('program_level_id'));})
        ->where('application_window_id', $request->get('application_window_id'))->where('batch_id', $batch_id)->count();  */

/*
        foreach ($applicants as $applicant) {
            foreach ($applicant->selections as $select) {
                if ($select->status == "SELECTED" || $select->status == "APPROVING" ) {
                    $applicants = null;
                }
            }
        }
 */
        $data = [
            'applicants' => $applicants,
            'batches'=>ApplicationBatch::select('id','batch_no')->whereIn('id',$batchids)->get()
        ];

        return view('dashboard.admission.other-applicants', $data)->withTitle('Other Applicants');
    }

    /**
     * Reject manual selection
     */
    public function rejectOtherApplicants(Request $request){
        Applicant::where('id',$request->get('applicant_id'))->update(['status'=>'NOT SELECTED']);

        return redirect()->to('application/other-applicants')->with('message','Application declined successfully');
    }

    public function viewApplicantDocuments(Request $request)
    {
/*         $applicant = DB::table('applicants')
        ->select('applicant_program_selections.*')
        ->join('applicant_program_selections', 'applicants.id', 'applicant_program_selections.applicant_id')
        ->where('applicant_program_selections.applicant_id', $request->get('applicant_id'))
        ->where('applicant_program_selections.status', 'ELIGIBLE')
        ->get(); */

        $applicant = Applicant::select('id','program_level_id')->whereHas('selections', function($query) use($request){$query->where('applicant_id', $request->get('applicant_id'));})
                                ->with(['selections:id,applicant_id,campus_program_id','selections.campusProgram:id,program_id,code','programLevel:id,name'])->latest()->first();

        $program_codes = $programs_selected = $entry_requirements = array();

        if($applicant->program_level_id == 5){
            foreach ($applicant->selections as $selection) {
                $program_codes[] = $selection->campusProgram->code;
            }
        }else{
            foreach ($applicant->selections as $selection) {
                $programs_selected[] = $selection->campus_program_id;
            }

            foreach($programs_selected as $program_id){
                $entry_requirements[] = EntryRequirement::select('id','campus_program_id','max_capacity')->where('application_window_id', $request->get('application_window_id'))->where('campus_program_id',$program_id)
                                                       ->with('campusProgram:id,code')->first();
            }

            foreach($programs_selected as $programs) {
                foreach($entry_requirements as $requirements) {
                    if($requirements){
                        if($programs == $requirements->campus_program_id){
                            $count_applicants_per_program = ApplicantProgramSelection::where('campus_program_id', $programs)
                            ->where(function($query) {
                                $query->where('applicant_program_selections.status', 'SELECTED')
                                      ->orWhere('applicant_program_selections.status', 'APPROVING');
                            })
                            ->count();

                            if ($count_applicants_per_program < $requirements->max_capacity) {
                                $program_codes[] = $requirements->campusProgram->code;
                            }
                        }
                    }
                }

    /*             if(str_contains(strtolower($applicant->programLevel->name),'master')){
                    foreach ($applicant->selections as $selection) {
                        $program_codes[] = $selection->campusProgram->code;
                    }
                } */
            }
        }


        $data = [
            'applicant'         => Applicant::find($request->get('applicant_id')),
            'program_codes'     => $program_codes,
            'request'           => $request
        ];

        return view('dashboard.admission.view-applicant-documents', $data)->withTitle('Applicant Certificates');
    }

    /**
     * Download selected applicants list
     */
    public function downloadSelectedApplicants(Request $request)
    {

        if(!$request->get('program_level_id')){
            return redirect()->back()->with('error','Please select program level first');
        }
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Selected-Applicants-'.$award->name.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

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

            // $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
            //      $query->where('id',$request->get('application_window_id'));
            // })->whereHas('selections',function($query) use($request){
            //      $query->where('status','ELIGIBLE');
            // })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();

            /* $list = Applicant::where('program_level_id', $request->get('program_level_id'))
            ->where('application_window_id', $request->get('application_window_id'))
            ->where('campus_id', $staff->campus_id)
            ->where('status', 'SELECTED')
            ->with(['nextOfKin', 'region', 'district', 'disabilityStatus', 'nectaResultDetails.results', 'nacteResultDetails', 'outResultDetails.results', 'selections.campusProgram.program'])
            ->get(); */

            $list = Applicant::select('id','first_name','middle_name','surname','index_number','gender','birth_date','batch_id','nationality','entry_mode','phone',
                                            'email','status','confirmation_status','admission_confirmation_status','country_id','region_id','district_id','disability_status_id','created_at','next_of_kin_id','multiple_admissions')
                                ->doesntHave('student')
                                ->where(function($query){$query->where('status', 'SELECTED')->orWhere('status','SUBMITTED');})->where('application_window_id', $request->get('application_window_id'))
                                ->where('program_level_id', $request->get('program_level_id'))
                                ->with(['selections:id,order,campus_program_id,applicant_id,status','selections.campusProgram:id,code,campus_id','selections.campusProgram.program:id,name',
                                'nectaResultDetails:id,applicant_id,index_number,verified,center_name,points,exam_id',
                                'nectaResultDetails.results:id,necta_result_detail_id,subject_name,grade','nacteResultDetails:id,applicant_id,avn,verified,diploma_gpa,programme,institution',
                                'nacteResultDetails.results:id,nacte_result_detail_id,subject,grade','region:id,name', 'district:id,name', 'disabilityStatus:id,name',
                                'outResultDetails','outResultDetails:id,gpa,reg_no,applicant_id','outResultDetails.results:id,subject_name,grade,out_result_detail_id',
                                'nextOfKin:id,phone'])->where('application_window_id',$request->get('application_window_id'))
                                ->get();
         }


        $batches = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))
                                    ->where('program_level_id', $request->get('program_level_id'))->get();
        $callback = function() use ($list, $batches)
        {
        $firstChoice        = null;
        $secondChoice       = null;
        $thirdChoice        = null;
        $fourthChoice       = null;
        $o_level_schools    = null;
        $a_level_schools    = null;

            $file_handle = fopen('php://output', 'w');
            fputcsv($file_handle,['S/N', 'FIRST NAME','MIDDLE NAME','SURNAME','SEX', 'NATIONALITY', 'DISABILITY', 'DATEOFBIRTH', 'F4INDEXNO', 'F6INDEXNO', 'AVN NO', 'CHOICE1', 'CHOICE2', 'CHOICE3', 'CHOICE4', 'PROGRAMME SELECTED', 'INSTITUTION CODE', 'ENTRY CATEGORY', 'OPTS', 'O-LEVEL RESULTS', 'APTS/GPA', 'A-LEVEL RESULTS/DIPLOMA', 'OPEN GPA', 'OPEN RESULTS', 'SELECTED', 'MULTIPLE', 'DATE REGISTERED', 'PHONE NUMBER', 'EMAIL ADDRESS', 'KIN PHONE NUMBER', 'DISTRICT', 'REGION', 'CONFIRM STATUS', 'BATCH NO', 'DIPLOMA INSTITUTE', 'PROGRAM COURSE', 'DIPLOMA GPA', 'DIPLOMA RESULTS', 'O-LEVEL SCHOOL', 'CSEE PTS', 'A-LEVEL SCHOOL', 'ACSEE PTS', 'PROGRESS']);
            foreach ($list as $key => $applicant) {

            $batch_number = null;
            foreach($batches as $batch){
                if($batch->id == $applicant->batch_id){
                    $batch_number = $batch->batch_no;
                    break;
                }
            }
            foreach($applicant->selections as $option){

                if($option->order == 1){
                    $firstChoice = $option->campusProgram->code;
                }elseif($option->order == 2){
                    $secondChoice = $option->campusProgram->code;
                }elseif($option->order == 3){
                    $thirdChoice = $option->campusProgram->code;
                }elseif($option->order == 4){
                    $fourthChoice = $option->campusProgram->code;
                }
            }


                if ($applicant->confirmation_status == 1) {
                    $confirm = 'Confirmed';
                } else {
                    $confirm = 'Not Confirmed';
                }

                $institution_code = $selected_programme = null;
                foreach ($applicant->selections as $selection) {

                    if($applicant->program_level_id == 1){
                        if($selection->campusProgram->campus_id == 1) {
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;
                            }
                        }elseif($selection->campusProgram->campus_id == 2){
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 4);
                                $selected_programme = $selection->campusProgram->code;
                            }
                        }elseif($selection->campusProgram->campus_id == 3){
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 4);
                                $selected_programme = $selection->campusProgram->code;
                            }
                        }
                    }else{
                        if($selection->campusProgram->campus_id == 1) {
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 2);
                                $selected_programme = $selection->campusProgram->code;
                            }
                        }elseif($selection->campusProgram->campus_id == 2){
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;
                            }

                        }elseif($selection->campusProgram->campus_id == 3){
                            if($selection->status == 'APPROVING' || $selection->status == 'SELECTED' || $selection->status == 'PENDING'){
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;
                            }
                        }
                    }

                    $status = null;
                    if($selection->status == 'APPROVING' || $selection->status == 'SELECTED'){
                        $status = $selection->status == 'APPROVING'? 'Internal Selected' : 'Selected';
                        break;
                    }elseif($selection->status == 'PENDING'){
                        $status = 'Awaiting Approval';
                        break;
                    }

                }


                $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

                $exam_year = null;
                foreach($applicant->nectaResultDetails as $detail) {
                    if($detail->exam_id == 2 && $detail->verified == 1){
                        $index_number = $detail->index_number;
                        if(str_contains($index_number,'EQ')){
                            $exam_year = explode('/',$index_number)[1];
                        }else{
                            $exam_year = explode('/', $index_number)[2];
                        }
                        break;
                    }
                }

                $a_level_grades = [];
                if($exam_year < 2014 || $exam_year > 2015){
                    $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];

                }else{
                    $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
                }



                $o_level_points = null;
                $a_level_points = null;
                $o_level_results = [];
                $o_level_schools = [];
                foreach($applicant->nectaResultDetails as $detail){
                    if($detail->exam_id == 1 && $detail->verified == 1){

                        $o_level_schools = $detail->center_name;
                        $o_level_points = $detail->points;
                        foreach($detail->results as $result){
                            foreach($o_level_grades as $grade=>$points){
                                if($result->grade != '' && array_key_exists($result->grade,$o_level_grades)){
                                    $o_level_results[] = $result->subject_name.'-'.$result->grade.'('.$o_level_grades[$result->grade].') ';
                                    break;
                                }else{
                                    $o_level_results[] = $result->subject_name.'-'.$result->grade.' ';
                                    break;
                                }
                            }
                            //$a_level_results[] = $result->subject_name.'-'.$result->grade.'('.$a_level_grades[$result->grade].')';

                        }
                    }
                }

                $a_level_results = [];
                $diploma_results = [];
                $open_results    = [];
                $a_level_schools = [];
                $a_level_index = [];

                foreach($applicant->nectaResultDetails as $detail){
                    if($detail->exam_id == 2 && $detail->verified == 1){
                        $a_level_schools = $detail->center_name;
                        $a_level_index[] = $detail->index_number;
                        foreach($detail->results as $result){
                            foreach($a_level_grades as $grade=>$points){
                                if($result->grade != '' && array_key_exists($result->grade,$a_level_grades)){
                                    $a_level_results[] = $result->subject_name.'-'.$result->grade.'('.$a_level_grades[$result->grade].') ';
                                    break;
                                }else{
                                    $a_level_results[] = $result->subject_name.'-'.$result->grade.' ';
                                    break;
                                }

                            }

                        }
                        $a_level_points = $detail->points;
                    } else {
                        $a_level_schools = null;
                    }
                }

                $diploma_gpa            = null;
                $diploma_institution    = null;
                $programme              = null;
                $avn                    = null;

                foreach ($applicant->nacteResultDetails as $nacte_results) {
                    if ($nacte_results->verified == 1) {

                        $diploma_gpa            = $nacte_results->diploma_gpa;
                        $diploma_institution    = $nacte_results->institution;
                        $programme              = $nacte_results->programme;
                        $avn                    = $nacte_results->avn;

                        foreach ($nacte_results->results as $result) {
                            $diploma_results[] = $result->subject.'-'.$result->grade.' ';
                        }

                    }

                }

                //$out_points = null;
                $out_gpa = null;
                foreach ($applicant->outResultDetails as $out_results) {
                    if ($out_results->verified == 1) {
                        $out_gpa        = $out_results->gpa;
                        $a_level_index  = $out_results->reg_no;

                        foreach($out_results->results as $result){
                            $open_results[] = $result->subject_name.'-'.$result->grade.' ';
                        }
                    }
                }

                $multipe_admission_status = $confirmation_status = null;
                if($status == 'Selected'){
                    if($applicant->multiple_admissions == 1){
                        $multipe_admission_status = 'Yes';
                        if($applicant->confirmation_status == 'CONFIRMED' || $applicant->admission_confirmation_status == 'CONFIRMED'){
                            $confirmation_status = 'Confirmed';
                        }elseif(str_contains($applicant->confirmation_status, 'OTHER') || str_contains($applicant->admission_confirmation_status, 'OTHER')){
                            $confirmation_status = 'Confirmed Elsewhere';
                        }
                    }else{
                        $multipe_admission_status = 'No';
                    }
                    $multipe_admission_status = $applicant->multiple_admissions == 1? 'Yes' : 'No';
                }

                if(is_array($a_level_index)){
                    $a_level_index=implode (',',$a_level_index);
                    }
                    if(is_array($a_level_results)){
                    $a_level_results=implode ($a_level_results);
                    }

                if(is_array($o_level_results)){
                    $o_level_results=implode (',',$o_level_results);
                    }
                if(is_array($open_results)){
                    $open_results=implode ($open_results);
                    }
                    if(is_array($diploma_results)){
                    $diploma_results=implode ($diploma_results);
                    }
                    if(is_array($o_level_schools)){
                    $o_level_schools=implode ($o_level_schools);
                    }
                    if(is_array($a_level_schools)){
                    $a_level_schools=implode ($a_level_schools);
                    }
                    $phone = !empty($applicant->phone)? substr($applicant->phone,3) : null;
                    $next_of_kin_phone = !empty($applicant->nextOfKin->phone)? substr($applicant->nextOfKin->phone,3) : null;

                fputcsv($file_handle,
                [++$key, $applicant->first_name, $applicant->middle_name, $applicant->surname,
                $applicant->gender , $applicant->nationality, $applicant->disabilityStatus->name, $applicant->birth_date, $applicant->index_number,
                $a_level_index, $avn, $firstChoice, $secondChoice, $thirdChoice, $fourthChoice, $selected_programme, $institution_code,
                $applicant->entry_mode, 'OPTS', $o_level_results, 'APTS / GPA', $a_level_results,
                $out_gpa, $open_results, $status, $multipe_admission_status, $applicant->created_at, $phone, $applicant->email, $next_of_kin_phone,
                $applicant->district->name, $applicant->region->name, $confirmation_status, $batch_number,
                $diploma_institution, $programme, $diploma_gpa, $diploma_results, $o_level_schools,
                $o_level_points, $a_level_schools, $a_level_points, $applicant->status
                ]);
            }
            fclose($file_handle);
        };
        //return $callback();
        ApplicationWindow::find($request->get('application_window_id'))->update(['enrollment_report_download_status'=>1]);
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Submit selected applicants
     */
    public function submitSelectedApplicants(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::select('id','name')->find($request->get('program_level_id'));
        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id',$request->get('application_window_id'))
                                 ->where('program_level_id',$award->id)->latest()->first();

        $tcu_username = $tcu_token = $nactvet_authorization_key = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KIVUKONI');
            $nactvet_token = config('constants.NACTE_API_SECRET_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KARUME');
            $nactvet_token = config('constants.NACTE_API_SECRET_KARUME');

        }elseif($staff->campus_id == 3){
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_PEMBA');
            $nactvet_token = config('constants.NACTE_API_SECRET_PEMBA');
        }

        $applicants = [];
        if(str_contains(strtolower($award->name), 'certficate') || str_contains(strtolower($award->name), 'diploma')){
            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','email','region_id','district_id','nationality','next_of_kin_id','disability_status_id','address','entry_mode','birth_date','intake_id','batch_id','program_level_id')
                                ->whereHas('selections', function($query) use($request){$query->where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING');})
                                ->whereIn('id',$request->get('applicant_ids'))
                                ->where('status','SELECTED')
                                ->with(['selections:id,status,campus_program_id,applicant_id',
                                        'selections.campusProgram:id,regulator_code,program_id,code','selections.campusProgram.program:id,nta_level_id',
                                        'selections.campusProgram.program.ntaLevel:id,name',
                                        'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                        'nacteResultDetails'=>function($query){$query->select('id','applicant_id','registration_number','diploma_graduation_year','programme','avn')
                                        ->where('verified',1);},
                                        'outResultDetails'=>function($query){$query->select('id','applicant_id')->where('verified',1);},'disabilityStatus:id,name',
                                        'nextOfKin:id,first_name,surname,region_id,relationship,address,phone','region:id,name','district:id,name','intake:id,name'])->get();
        }else {
            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','email','region_id','district_id',
                            'nationality','next_of_kin_id','disability_status_id','address','entry_mode','birth_date','intake_id','batch_id')
                        ->whereIn('id',$request->get('applicant_ids'))->whereIn('status', ['SELECTED','NOT SELECTED'])
                        ->with(['selections:id,status,campus_program_id,applicant_id',
                                'selections.campusProgram:id,regulator_code,program_id,code','selections.campusProgram.program:id,nta_level_id',
                                'selections.campusProgram.program.ntaLevel:id,name',
                                'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                'nacteResultDetails'=>function($query){$query->select('id','applicant_id','registration_number','diploma_graduation_year','programme','avn')
                                ->where('verified',1);},
                                'outResultDetails'=>function($query){$query->select('id','applicant_id')->where('verified',1);},'disabilityStatus:id,name',
                                'nextOfKin:id,first_name,surname,region_id,relationship,address,phone','region:id,name','district:id,name','intake:id,name'])->get();
        }


            $count = 0;
            if(str_contains(strtolower($award->name),'bachelor')){
                foreach($applicants as $applicant){
                    if(ApplicantSubmissionLog::where('applicant_id',$applicant->id)->where('program_level_id',$applicant->program_level_id)
                                             ->where('application_window_id',$applicant->application_window_id)->where('batch_id',$applicant->batch_id)->count() == 0){

                        //$url='https://api.tcu.go.tz/applicants/submitProgramme';
                        $url='http://api.tcu.go.tz/applicants/submitProgramme';

                        $selected_programs = array();
                        $approving_selection = null;

                        foreach($applicant->selections as $selection){
                            $selected_programs[] = $selection->campusProgram->regulator_code;
                            if($selection->status == 'APPROVING'){
                                $approving_selection = $selection;

                            }
                        }

                        $f6indexno = null;
                        $otherf4indexno = $otherf6indexno = [];
                        foreach($applicant->nectaResultDetails as $detail) {
                            if($detail->exam_id == 2){
                                if($f6indexno != null && $f6indexno != $detail->index_number){
                                    $otherf6indexno[] = $detail->index_number;
                                }else{
                                    $f6indexno = $detail->index_number;
                                }
                            }
                        }

                        foreach($applicant->nacteResultDetails as $detail){
                            if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                                $f6indexno = $detail->avn;
                                break;
                            }
                        }

                        foreach($applicant->nectaResultDetails as $detail) {
                            if($detail->exam_id == 1 && $detail->index_number != $applicant->index_number){
                                $otherf4indexno[]= $detail->index_number;
                            }
                        }

                        if(is_array($selected_programs)){
                            $selected_programs=implode(', ',$selected_programs);
                        }

                        if(is_array($otherf4indexno)){
                        $otherf4indexno=implode(', ',$otherf4indexno);
                        }

                        if(is_array($otherf6indexno)){
                        $otherf6indexno=implode(', ',$otherf6indexno);
                        }

                        $category = null;
                        if($applicant->entry_mode == 'DIRECT'){
                            $category = 'A';

                        }else{
                            // Open university
                            if($applicant->outResultDetails){
                                $category = 'F';

                            }

                            // Diploma holders
                            if($applicant->nacteResultDetails){
                                $category = 'D';

                            }
                        }

                        if($approving_selection){

                            $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                            <Request>
                                <UsernameToken>
                                    <Username>'.$tcu_username.'</Username>
                                    <SessionToken>'.$tcu_token.'</SessionToken>
                                </UsernameToken>
                                <RequestParameters>
                                    <f4indexno>'.$applicant->index_number.'</f4indexno>
                                    <f6indexno>'.$f6indexno.'</f6indexno>
                                    <Gender>'.$applicant->gender.'</Gender>
                                    <SelectedProgrammes>'.$selected_programs.'</SelectedProgrammes>
                                    <MobileNumber>'.'0'.substr($applicant->phone,3).'</MobileNumber>
                                    <OtherMobileNumber></OtherMobileNumber>
                                    <EmailAddress>'.$applicant->email.'</EmailAddress>
                                    <Category>'.$category.'</Category>
                                    <AdmissionStatus>provisional admission</AdmissionStatus>
                                    <ProgrammeAdmitted>'.$approving_selection->campusProgram->regulator_code.'</ProgrammeAdmitted>
                                    <Reason>eligible</Reason>
                                    <Nationality>'.$applicant->nationality.'</Nationality>
                                    <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                                    <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                                    <NationalIdNumber></NationalIdNumber>
                                    <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
                                    <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
                                </RequestParameters>
                            </Request>';

                        }else{

                            $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                            <Request>
                                <UsernameToken>
                                    <Username>'.$tcu_username.'</Username>
                                    <SessionToken>'.$tcu_token.'</SessionToken>
                                </UsernameToken>
                                <RequestParameters>
                                    <f4indexno>'.$applicant->index_number.'</f4indexno >
                                    <f6indexno>'.$f6indexno.'</f6indexno>
                                    <Gender>'.$applicant->gender.'</Gender>
                                    <SelectedProgrammes>'.$selected_programs.'</SelectedProgrammes>
                                    <MobileNumber>'.'0'.substr($applicant->phone,3).'</MobileNumber>
                                    <OtherMobileNumber></OtherMobileNumber>
                                    <EmailAddress>'.$applicant->email.'</EmailAddress>
                                    <Category>'.$category.'</Category>
                                    <AdmissionStatus>not selected</AdmissionStatus>
                                    <ProgrammeAdmitted>'.null.'</ProgrammeAdmitted>
                                    <Reason>max capacity</Reason>
                                    <Nationality>'.$applicant->nationality.'</Nationality>
                                    <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                                    <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                                    <NationalIdNumber></NationalIdNumber>
                                    <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
                                    <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
                                </RequestParameters>
                            </Request>';
                        }
                        //return $xml_request;
                        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                        $json = json_encode($xml_response);
                        $array = json_decode($json,TRUE);

                        if($array['Response']['ResponseParameters']['StatusCode'] == 200){

                            Applicant::where('id',$applicant->id)->update(['status'=>'SUBMITTED']);

                            $log = new ApplicantSubmissionLog;
                            $log->applicant_id = $applicant->id;
                            $log->program_level_id = $request->get('program_level_id');
                            $log->application_window_id = $request->get('application_window_id');
                            $log->batch_id = $applicant->batch_id;
                            $log->submitted = 1;
                            $log->save();
                            $count++;
                        }else{
                            $error_log = new ApplicantFeedBackCorrection;
                            $error_log->applicant_id = $applicant->id;
                            $error_log->application_window_id = $request->get('application_window_id');
                            $error_log->programme_id = $selection->campusProgram->code;
                            $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                            $error_log->remarks = $array['Response']['ResponseParameters']['StatusDescription'];
                            $error_log->save();
                        }
                    }
                }
            }elseif(str_contains(strtolower($award->name),'diploma') || str_contains(strtolower($award->name),'basic')){
                $payment = NactePayment::select('reference_no')->where('campus_id', $staff->campus_id)->latest()->first();

                if(!$payment){
                    return redirect()->back()->with('error','No NACTVET payment set for this campus');
                }

                $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/payment/'.$payment->reference_no.'/'.$nactvet_token);

                if(empty(json_decode($result)->params)){
                    return redirect()->back()->with('error','Invalid call to NACTVET account balance. Please try again.');
                }elseif((json_decode($result)->params[0]->balance) < 5000) { // Needs to crosscheck this
                    return redirect()->back()->with('error','Insufficient balance. Please top up TZS '.count($applicants)*5000 - json_decode($result)->params[0]->balance.' to proceed.');
                }
                $count = 0;

                foreach($applicants as $applicant){

                    $f6indexno = null;
                    foreach ($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 2 && $detail->verified == 1){
                        $f6indexno = $detail->index_number;
                        }
                    }

                    $has_level5 = false;
                    $nta4_reg_no = $nta4_graduation_year = $nta5_reg_no = $nta5_graduation_year = null;
                    foreach($applicant->nacteResultDetails as $detail){
                        if(str_contains(strtolower($detail->programme),'basic')){
                            $nta4_reg_no = $detail->registration_number;
                            $nta4_graduation_year = $detail->diploma_graduation_year;

                        }elseif(!str_contains(strtolower($detail->programme),'basic') && str_contains(strtolower($detail->programme),'certificate')){
                            $nta5_reg_no = $detail->registration_number;
                            $nta5_graduation_year = $detail->diploma_graduation_year;

                            if($detail->diploma_gpa >= 2){
                                $has_level5 = true;
                            }
                        }
                    }
                    $selected_programs = array();
                    $approving_selection = $regulator_programme_id = $programme_code = null;
                    foreach($applicant->selections as $selection){
                        $selected_programs[] = $selection->campusProgram->regulator_code;
                        if($selection->status == 'APPROVING'){
                            $approving_selection = $selection;
                            $regulator_programme_id = $selection->campusProgram->regulator_code;
                            $programme_code = $selection->campusProgram->code;
                        }
                    }

                        //API URL
                    $url = 'https://www.nacte.go.tz/nacteapi/index.php/api/upload';

                    $ch = curl_init($url);

                    $level = null;
                    $string = $approving_selection->campusProgram->program->ntaLevel->name;
                    if($has_level5 || $applicant->program_level_id == 1){
                        $last_character = (strlen($string) - 1);
                        $level = substr($string, $last_character);
                    }else{
                        $last_character = (strlen($string) - 1);
                        $level = substr($string, $last_character) - 1;
                    }


                    $f4indexno = $f4_exam_year = null;
                    if(str_contains(strtolower($applicant->index_number),'eq')){
                        $f4_exam_year = explode('/',$applicant->index_number)[1];
                        $f4indexno = explode('/',$applicant->index_number)[0];
                    }else{
                        $f4_exam_year = explode('/', $applicant->index_number)[2];
                        $f4indexno = explode('/',$applicant->index_number)[0].'/'.explode('/',$applicant->index_number)[1];
                    }

                    $f6_exam_year = null;
                    if(!empty($f6indexno)){
                        if(str_contains(strtolower($f6indexno),'eq')){
                            $f6_exam_year = explode('/',$f6indexno)[1];
                            $f6indexno = explode('/',$f6indexno)[0];
                        }else{
                            $f6_exam_year = explode('/', $f6indexno)[2];
                            $f6indexno = explode('/',$f6indexno)[0].'/'.explode('/',$f6indexno)[1];
                        }
                    }

                    $impairment = null;
                    if($applicant->disability_status_id == 1){
                        $impairment = 'None';
                    }elseif($applicant->disability_status_id == 2 || $applicant->disability_status_id == 7){
                        $impairment = 'Physical Impairments';
                    }elseif($applicant->disability_status_id == 3 || $applicant->disability_status_id == 4){
                        $impairment = 'Sensory Impairments';
                    }elseif($applicant->disability_status_id == 8 || $applicant->disability_status_id == 9){
                        $impairment = 'Cognitive Impairments';
                    }elseif($applicant->disability_status_id == 10){
                        $impairment = 'Learning Difficulties';
                    }

                    $data = array(
                        'heading' => array(
                            'authorization' => $nactvet_authorization_key,
                            'intake' => strtoupper($applicant->intake->name),
                            'programme_id' => $regulator_programme_id,
                            'application_year' => date('Y'),
                            'level' => strval($level),
                            'payment_reference_number' => $payment->reference_no,
                        ),
                        'students' => array(
                            ['particulars' => array(
                                    'firstname' => $applicant->first_name,
                                    'secondname' => $applicant->middle_name != null? $applicant->middle_name : '',
                                    'surname' => $applicant->surname,
                                    'DOB' => DateMaker::toStandardDate($applicant->birth_date),
                                    'gender' => $applicant->gender == 'M'? 'Male' : 'Female',
                                    'impairement' => $impairment,
                                    'form_four_indexnumber' => $f4indexno,
                                    'form_four_year' => $f4_exam_year,
                                    'form_six_indexnumber' => $f6indexno? $f6indexno : '',
                                    'form_six_year' => $f6indexno? $f6_exam_year : '',
                                    'NTA4_reg' => !empty($nta4_reg_no)? $nta4_reg_no : '',
                                    'NTA4_grad_year' => !empty($nta4_graduation_year)? explode('/',$nta4_graduation_year)[1] : '',
                                    'NTA5_reg' => !empty($nta5_reg_no)? $nta5_reg_no : '',
                                    'NTA5_grad_year' => !empty($nta5_graduation_year)? explode('/',$nta5_graduation_year)[1] : '',
                                    'email_address' => $applicant->email,
                                    'mobile_number' => '0'.substr($applicant->phone,3),
                                    'address' => $applicant->address,
                                    'region' => $applicant->region->name,
                                    'district' => $applicant->district->name,
                                    'nationality' => $applicant->nationality,
                                    'next_kin_name' => $applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->surname,
                                    'next_kin_address' => $applicant->nextOfKin->address,
                                    'next_kin_email_address' => $applicant->nextOfKin->email? $applicant->nextOfKin->email : '',
                                    'next_kin_phone' => '0'.substr($applicant->nextOfKin->phone,3),
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


                    if(isset(json_decode($result)->code)){

                        if(json_decode($result)->code == 200){

                            Applicant::where('id',$applicant->id)->update(['status'=>'SUBMITTED']);

                            $log = new ApplicantSubmissionLog;
                            $log->applicant_id = $applicant->id;
                            $log->program_level_id = $request->get('program_level_id');
                            $log->application_window_id = $request->get('application_window_id');
                            $log->batch_id = $applicant->batch_id;
                            $log->submitted = 1;
                            $log->save();

                            $count++;

                        }else{
                            $error_log = new ApplicantFeedBackCorrection;
                            $error_log->applicant_id = $applicant->id;
                            $error_log->application_window_id = $request->get('application_window_id');
                            $error_log->programme_id = $programme_code;
                            $error_log->error_code = json_decode($result)->code;
                            $error_log->remarks = json_decode($result)->message;
                            $error_log->save();
                        }
                    }
                }
                $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/payment/'.$payment->reference_no.'/'.$nactvet_token);
                NactePayment::where('reference_no',$payment->reference_no)->update(['balance'=>$payment->balance = json_decode($result)->params[0]->balance]);
            }

        return redirect()->back()->with('message',$count.' applicants have been successfully submitted.');
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
     * Download applicants submitted to regulators
     */
    public function downloadSubmittedApplicants(Request $request){
        if(!$request->get('program_level_id')){
            return redirect()->back()->with('error','Please select program level first');
        }
        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Submitted-Applicants-'.$award->name.'-'.date('Y-m-d'.'\T'.'h:i:s').'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

            $list = Applicant::select('id','first_name','middle_name','surname','index_number','gender','birth_date','batch_id','nationality','entry_mode','phone',
                                            'email','status','country_id','region_id','district_id','disability_status_id')
                                    ->doesntHave('student')->where('status', 'SUBMITTED')->where('application_window_id', $request->get('application_window_id'))
                                    ->where('program_level_id', $request->get('program_level_id'))
                                    ->with(['selections'=>function($query) use($request){$query->select('id','order','campus_program_id','applicant_id')->where('status','APPROVING')
                                            ->where('application_window_id',$request->get('application_window_id'));},
                                            'selections.campusProgram'=>function($query) use($staff){$query->select('id','code','campus_id')->where('campus_id',$staff->campus_id);},
                                            'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                            'nacteResultDetails'=>function($query){$query->select('id','applicant_id','avn')->where('verified',1);}])->get();

            $batches = ApplicationBatch::select('id','batch_no')->where('application_window_id',$request->get('application_window_id'))
                                        ->where('program_level_id', $request->get('program_level_id'))->get();

            $callback = function() use ($list,$batches)
            {
                $file_handle = fopen('php://output', 'w');
                fputcsv($file_handle,['S/N', 'FIRST NAME','MIDDLE NAME','SURNAME','SEX', 'NATIONALITY', 'PHONE NUMBER', 'EMAIL ADDRESS', 'DISABILITY', 'DATEOFBIRTH', 'DISTRICT', 'REGION', 'F4INDEXNO', 'F6INDEXNO', 'AVN NO', 'PROGRAMME SELECTED', 'INSTITUTION CODE', 'ENTRY CATEGORY', 'BATCH NO', 'STATUS']);
                foreach ($list as $key=>$applicant) {

                    $batch_number = null;
                    foreach($batches as $batch){
                        if($batch->id == $applicant->batch_id){
                            $batch_number = $batch->batch_no;
                            break;
                        }
                    }

                    $institution_code = $selected_programme = null;
                    foreach ($applicant->selections as $selection) {
                        if($applicant->program_level_id == 1){
                            if($applicant->selections[0]->campusProgram->campus_id == 1) {
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;

                            }elseif($applicant->selections[0]->campusProgram->campus_id == 2){
                                $institution_code = substr($selection->campusProgram->code, 0, 4);
                                $selected_programme = $selection->campusProgram->code;

                            }elseif($applicant->selections[0]->campusProgram->campus_id == 3){
                                $institution_code = substr($selection->campusProgram->code, 0, 4);
                                $selected_programme = $selection->campusProgram->code;

                            }
                        }else{
                            if($applicant->selections[0]->campusProgram->campus_id == 1) {
                                $institution_code = substr($selection->campusProgram->code, 0, 2);
                                $selected_programme = $selection->campusProgram->code;

                            }elseif($applicant->selections[0]->campusProgram->campus_id == 2){
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;

                            }elseif($applicant->selections[0]->campusProgram->campus_id == 3){
                                $institution_code = substr($selection->campusProgram->code, 0, 3);
                                $selected_programme = $selection->campusProgram->code;

                            }
                        }
                    }

                    $a_level_index = [];
                    foreach($applicant->nectaResultDetails as $detail){
                        if($detail->exam_id == 2){
                            $a_level_index[] =  $detail->index_number;

                        }
                    }

                    $avn = [];
                    foreach ($applicant->nacteResultDetails as $nacte_results) {
                        $avn[] = $nacte_results->avn;

                    }

                    if(is_array($a_level_index)){
                        $a_level_index=implode (';', $a_level_index);
                    }

                    if(is_array($avn)){
                        $avn=implode (';',$avn);
                    }
                        $phone = !empty($applicant->phone)? substr($applicant->phone,3) : null;

                    fputcsv($file_handle,
                    [++$key, $applicant->first_name, $applicant->middle_name, $applicant->surname, $applicant->gender , $applicant->nationality, $phone, $applicant->email,
                    $applicant->disabilityStatus->name, $applicant->birth_date, $applicant->district->name, $applicant->region->name, $applicant->index_number,
                    $a_level_index, $avn, empty($selected_programme)? 'N/A' : $selected_programme, $institution_code, $applicant->entry_mode, $batch_number, $applicant->status
                    ]);
                }
                fclose($file_handle);
            };
              //return $callback();
              ApplicationWindow::find($request->get('application_window_id'))->update(['enrollment_report_download_status'=>1]);
              return response()->stream($callback, 200, $headers);
    }


    /**
     * Select program
     */
    public function selectProgram(Request $request)
    {
		$applicant = Applicant::where('id',$request->get('applicant_id'))->with(['nectaResultDetails:id,applicant_id,index_number,verified,exam_id','nacteResultDetails:id,applicant_id,verified,avn',
        'outResultDetails:id,applicant_id,verified'])->latest()->first();

		$window = $applicant->applicationWindow;
        $batch = ApplicationBatch::where('application_window_id',$window->id)->where('program_level_id',$applicant->program_level_id)->latest()->first();

        if(!str_contains($applicant->programLevel->name,'Masters')){
            $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                    $query->where('award_id',$applicant->program_level_id);
            })->with(['program','campus','entryRequirements'=>function($query) use($window){
                    $query->where('application_window_id',$window->id);
            }])->where('campus_id',session('applicant_campus_id'))->get() : [];
        }else{
            $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                $query->where('award_id',$applicant->program_level_id);
        })->with(['program','campus'])->where('campus_id',session('applicant_campus_id'))->get() : [];
        }
         $count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('batch_id',$batch->id)->count();


        //$applicant = Applicant::find($request->get('applicant_id'));
/*         if($applicant->is_continue == 1){
            $applicant->status = 'ADMITTED';
            $applicant->save();
        }
 */
/* 		$previous_studied_programme = Applicant::whereHas('selections', function($query) {$query->where('status', 'SELECTED');})->with('selections.campusProgram.program')
									->where('index_number', $applicant->index_number)->where('program_level_id', $applicant->program_level_id - 1)->first();
		//return $previous_studied_programme->selections[0]; */
        $similar_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))
												  ->where('campus_program_id',$request->get('campus_program_id'))->where('batch_id',$batch->id)->count();
        if($similar_count == 0){
             if($count >= 4){
                return redirect()->back()->with('error','You cannot select more than 4 programmes');
             }else{

                 $selection = new ApplicantProgramSelection;
                 $selection->applicant_id = $request->get('applicant_id');
                 $selection->campus_program_id = $request->campus_program_id;
                 $selection->application_window_id = $request->get('application_window_id');
                 $selection->order = $request->get('choice');
                 $selection->o_level_points = $request->get('o_level_points');
                 $selection->a_level_points = $request->get('a_level_points');
                 $selection->diploma_grade = $request->get('diploma_grade');
                 $selection->open_grade = $request->get('open_grade');
                 if($applicant->is_continue == 1){
                    $selection->status = 'SELECTED';
                 }
                 $selection->batch_id = $applicant->batch_id;
                 $selection->save();

                 if(!str_contains($applicant->programLevel->name,'Masters')){
                    // salim added avn results check on 1/30/2023
                    foreach ($campus_programs as $program) {
                        if ($program->id == $request->get('campus_program_id')) {
                            if (unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && $applicant->entry_mode != 'DIRECT'){
                                $applicant_has_nacte_results = NacteResultDetail::where('applicant_id', $request->get('applicant_id'))->where('verified',1)->first();
                                if($applicant_has_nacte_results && empty(NacteResult::where('nacte_result_detail_id', $applicant_has_nacte_results->id)->first())){
                                    $applicant->avn_no_results = 1;
                                    $applicant->save();
                                }
                            }
                        }
                    }
                }
                $select_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('batch_id',$batch->id)->count();

                if($request->get('choice') == 1){
                    $applicant = Applicant::find($request->get('applicant_id'));
                    $applicant->programs_complete_status = 1;
                    if($applicant->entry_mode == 'DIRECT' && !str_contains(strtolower($applicant->programLevel->name),'masters')){
                        $applicant->documents_complete_status = 1;
                    }
                    $applicant->save();
                }

                // if(($applicant->is_tcu_added == null || $applicant->is_tcu_added == 0) && str_contains(strtolower($applicant->programLevel->name),'bachelor')){ old
                if(($applicant->is_tcu_added != 1) && str_contains(strtolower($applicant->programLevel->name),'bachelor')){
                    $tcu_username = $tcu_token = null;
                    if(session('applicant_campus_id') == 1){
                        $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
                        $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
                        $nacte_secret_key = config('constants.NACTE_API_SECRET_KIVUKONI');

                    }elseif(session('applicant_campus_id') == 2){
                        $tcu_username = config('constants.TCU_USERNAME_KARUME');
                        $tcu_token = config('constants.TCU_TOKEN_KARUME');
                        $nacte_secret_key = config('constants.NACTE_API_SECRET_KIVUKONI');

                    }

                    $url='http://api.tcu.go.tz/applicants/add';

                    $f6indexno = null;
                    foreach ($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 2 && $detail->verified == 1){
                            $f6indexno = $detail->index_number;
                            break;
                        }
                    }

                    $otherf4indexno = [];
                    foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 1 && $detail->verified == 1 && $detail->index_number != $applicant->index_number){
                            $otherf4indexno[]= $detail->index_number;
                        }
                    }

                    $otherf6indexno = [];
                    foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 2 && $detail->verified == 1 && $detail->index_number != $f6indexno){
                            $otherf6indexno = $detail->index_number;
                        }
                    }

                    if(is_array($otherf4indexno)){
                        $otherf4indexno=implode(', ',$otherf4indexno);
                    }

                    if(is_array($otherf6indexno)){
                        $otherf6indexno=implode(', ',$otherf6indexno);
                    }

                    $category = null;
                    if($applicant->entry_mode == 'DIRECT'){
                        $category = 'A';

                    }else{
                        // Open university
                        if($applicant->outResultDetails){
                            foreach($applicant->outResultDetails as $detail){
                                if($detail->verified == 1){
                                    $category = 'F';
                                    break;
                                }
                            }
                        }

                        // Diploma holders
                        if($applicant->nacteResultDetails){
                            foreach($applicant->nacteResultDetails as $detail){
                                if($detail->verified == 1){
                                    $f6indexno = $f6indexno == null? $detail->avn : $f6indexno;
                                    $category = 'D';
                                    break;
                                }
                            }
                        }
                    }

                    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                    <Request>
                        <UsernameToken>
                            <Username>'.$tcu_username.'</Username>
                            <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                            <f4indexno>'.$applicant->index_number.'</f4indexno>
                            <f6indexno>'.$f6indexno.'</f6indexno>
                            <Gender>'.$applicant->gender.'</Gender>
                            <Category>'.$category.'</Category>
                            <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
                            <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
                        </RequestParameters>
                    </Request>';

                    $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                    $json = json_encode($xml_response);
                    $array = json_decode($json,TRUE);

                    if(isset($array['Response'])){
                        //return $array['Response']['ResponseParameters']['StatusDescription'];
                        Applicant::where('id',$applicant->id)->update(['is_tcu_added'=> $array['Response']['ResponseParameters']['StatusCode'] == 200? 1 : 0,
                                                                       'is_tcu_reason'=> $array['Response']['ResponseParameters']['StatusDescription']]);
                    }
                }

                return redirect()->back()->with('message','Programme selected successfully');
             }
        }else{
           return redirect()->back()->with('error','Programme already selected');
        }
    }


    /**
     * Show Failed Applicants' Submissions to NACTVET and TCU
     */
    public function showRegulatorFailedCases(Request $request)
    {
/*         $data = [
            'applicants'=>Applicant::where('is_tcu_added',0)->where('campus_id',$request->get('staff_campus_id'))->where('program_level_id',4)->get(),
            'program_level'=>Award::find($request->get('program_level_id')),
            'selected_applicants'=>Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->get(),
            'request'=>$request
        ]; */
        $data = [
            'staff'=> User::find(Auth::user()->id)->staff,
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'nta_levels'=>NTALevel::all(),
            'departments'=>Department::all(),
            'campus_programs'=>CampusProgram::with('program')->get(),
            'applicants'=>Applicant::where('is_tcu_added',0)->where('campus_id',$request->get('campus_id'))->where('program_level_id',4)->get(),
            'request'=>$request,
            'batches'=>ApplicationBatch::all()
        ];
        return view('dashboard.application.tcu-failed-submissions',$data)->withTitle('TCU Failed Submissions');
    }


    /**
     * Reset program selection
     */
    public function resetProgramSelection($id)
    {
        try{
          $selection = ApplicantProgramSelection::with('applicant')->findOrFail($id);

          $applicant = Applicant::find($selection->applicant_id);

          $window = $applicant->applicationWindow;

          if(!str_contains($applicant->programLevel->name,'Masters')){
            $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                    $query->where('award_id',$applicant->program_level_id);
            })->with(['program','campus','entryRequirements'=>function($query) use($window){
                    $query->where('application_window_id',$window->id);
            }])->where('campus_id',session('applicant_campus_id'))->get() : [];
          }else{
            $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                $query->where('award_id',$applicant->program_level_id);
            })->with(['program','campus'])->where('campus_id',session('applicant_campus_id'))->get() : [];
          }

          $applicant_has_results = DB::table('nacte_results')->where('applicant_id', $selection->applicant_id)->get();

          if($selection->applicant->is_continue == 1){
            $applicant = Applicant::find($selection->applicant_id);
            $applicant->status = null;
            $applicant->save();
          }

          if($selection->order == 1){
              Applicant::where('id',$selection->applicant_id)->update(['programs_complete_status'=>0,'submission_complete_status'=>0]);
          }
          $selection->delete();

          // retrieved all sections of applicant by salim on 1/31/2023
          $applicant_selections = ApplicantProgramSelection::where('applicant_id', $applicant->id)->get();

          if(!str_contains($applicant->programLevel->name,'Masters')){
            // declared selection flag to check if equivalent must subjects exists by salim on 1/31/2023
            $selection_flag = null;

            // check for equivalent musts subjects on selections by salim on 1/31/2023
            foreach ($campus_programs as $program) {
                foreach ($applicant_selections as $selection) {
                    if ($program->id == $selection->campus_program_id) {
                        if (unserialize($program->entryRequirements[0]->equivalent_must_subjects) != null) {
                            $selection_flag = true;
                        }
                    }
                }
            }

            // check flag if true to update avn no results by salim on 1/31/2023
            if ($selection_flag) {
                $applicant->avn_no_results = 1;
                $applicant->save();
            } else {
                $applicant->avn_no_results = null;
                $applicant->save();
            }
        }

          // didn't succeed to reset selection of program that has must equivalent subjects by salim on 1/30/2023
        //   $selection = ApplicantProgramSelection::with('applicant')->findOrFail($id);
          //return sizeof($selection);
        //   return $selection;

            // foreach ($campus_programs as $program) {
            //     if ($program->id == $selection->campus_program_id) {

            //         if (unserialize($program->entryRequirements[0]->equivalent_must_subjects) == '') {
            //             $applicant->avn_no_results = null;
            //             $applicant->save();
            //         }
            //     }
            // }

            // $selection = null;

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
        if($request->get('document_name') == 'passport'){
            $validation = Validator::make($request->all(),[
                'document'=>'required|mimes:png,jpeg,jpg'
            ]);
        }else{
            $validation = Validator::make($request->all(),[
                'document'=>'required|mimes:pdf,png,jpeg,jpg'
            ]);
        }

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

    public function viewDocument(Request $request)
    {
        $data = [
            'request' => $request,
            'applicant' =>$request->get('applicant_id')? Applicant::with('programLevel','insurances')->where('id',$request->get('applicant_id'))->first():
			 Applicant::with('programLevel','insurances')->where('user_id',Auth::user()->id)->where('campus_id',session('applicant_campus_id'))->first()
        ];

        return view('dashboard.admission.applicant-document', $data);
    }

    /**
     * Delete uploaded document
     */
    public function deleteDocument(Request $request)
    {
        $applicant = Applicant::with('programLevel')->where('user_id',Auth::user()->id)->where('campus_id',session('applicant_campus_id'))->latest()->first();
        try{
/*             if($request->get('name') == 'insurance'){
			   $insurance = HealthInsurance::where('applicant_id', $applicant->id)->first();
               unlink(public_path().'/uploads/'.$insurance->card);
               $insurance->card = null;
			   $insurance->save();
            } */

            if($request->get('name') == 'birth_certificate'){
               unlink(public_path().'/uploads/'.$applicant->birth_certificate);
               $applicant->birth_certificate = null;
            }

            if($request->get('name') == 'o_level_certificate'){
               unlink(public_path().'/uploads/'.$applicant->o_level_certificate);
               $applicant->o_level_certificate = null;
            }

            if($request->get('name') == 'basic_certificate'){
               unlink(public_path().'/uploads/'.$applicant->nacte_reg_no);
               $applicant->nacte_reg_no = null;
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

            if($request->get('name') == 'teacher_diploma_certificate'){
                unlink(public_path().'/uploads/'.$applicant->teacher_diploma_certificate);
                $applicant->teacher_diploma_certificate = null;
            }

            if($request->get('name') == 'veta_certificate'){
                unlink(public_path().'/uploads/'.$applicant->veta_certificate);
                $applicant->veta_certificate = null;
            }

            if($request->get('name') == 'degree_certificate'){
                unlink(public_path().'/uploads/'.$applicant->degree_certificate);
                $applicant->degree_certificate = null;
            }

            if($request->get('name') == 'degree_transcript'){
                unlink(public_path().'/uploads/'.$applicant->degree_transcript);
                $applicant->degree_transcript = null;
            }

        }catch(\Exception $e){
            return redirect()->back()->with('error','Document could not be found');
        }

        if($applicant->entry_mode == 'DIRECT'){
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){

                if($applicant->status == null){
                    if($applicant->o_level_certificate && $applicant->a_level_certificate && $applicant->degree_certificate
                        && $applicant->degree_transcript){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }

                }elseif($applicant->status == 'ADMITTED'){
                    if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate
                        && $applicant->degree_certificate && $applicant->degree_transcript){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }
                }
            }
        }else{
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->diploma_certificate){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){
                if($applicant->status == null) {
                    if($applicant->o_level_certificate && $applicant->diploma_certificate && $applicant->degree_certificate
                        && $applicant->degree_transcript && $applicant->degree_transcript) {
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }

                }elseif($applicant->status == 'ADMITTED') {

                    if($applicant->birth_certificate && $applicant->o_level_certificate
                    && $applicant->diploma_certificate && $applicant->degree_certificate && $applicant->degree_transcript){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }
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
        $applicant = User::find(Auth::user()->id)->applicants()->with(['nextOfKin.country','nextOfKin.region','nextOfKin.district','nextOfKin.ward','country',
        'region','district','ward','disabilityStatus','nectaResultDetails.results','nacteResultDetails.results','selections','applicationWindow','intake'])
        ->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
	   if($applicant->is_transfered != 1){
        if($applicant->payment_complete_status == 0){
            return redirect()->back()->with('error','Payment section not completed');
        }
	   }
       if($applicant->results_complete_status == 0){
          return redirect()->back()->with('error','Results section not completed');
       }

       if($applicant->avn_no_results === 1 || $applicant->teacher_certificate_status === 1 || $applicant->veta_status == 1 ||
       str_contains(strtolower($applicant->programLevel->name),'masters') || (str_contains($applicant->programLevel->name,'Certificate')
       && $applicant->entry_mode == 'EQUIVALENT')){
          if($applicant->documents_complete_status == 0){
             return redirect()->back()->with('error','Documents section not completed');
          }
       }
	   if($applicant->is_transfered != 1){
          if($applicant->programs_complete_status == 0){
             return redirect()->back()->with('error','Programmes selection section not completed');
          }
	   }

       $applicant->submission_complete_status = 1;
       $applicant->submitted_at = now();
       $applicant->save();

	   if($applicant->is_transfered == 1){
		  $applicant = Applicant::with(['selections.campusProgram.program','nectaResultDetails'=>function($query){$query->where('verified',1);},
                                        'nacteResultDetails'=>function($query){$query->where('verified',1);},
                                        'outResultDetails'=>function($query){$query->where('verified',1);},'selections.campusProgram.campus','nectaResultDetails.results',
                                        'nacteResultDetails.results','outResultDetails.results','programLevel','applicationWindow'])->find($applicant->id);

            $window = $applicant->applicationWindow;

            $campus_program = $window? $window->campusPrograms()
                                                ->with(['program','campus','entryRequirements'=>function($query) use($window){$query->where('application_window_id',$window->id);}])
                                                ->where('id',$applicant->selections[0]->campus_program_id)->first() : [];

            $entry_requirements[] = EntryRequirement::select('id','campus_program_id','max_capacity')->where('application_window_id', $window->id)->where('campus_program_id',$campus_program->id)
                                                    ->with('campusProgram:id,code')->first();

            $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
    
            $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];
    
            $index_number = $applicant->index_number;
            if(str_contains($index_number,'EQ')){
                $exam_year = explode('/',$index_number)[1];
            }else{
                $exam_year = explode('/', $index_number)[2];
            }
    
            foreach($applicant->nectaResultDetails as $detail) {
                if($detail->exam_id == 2 && $detail->verified == 1){
                    $index_number = $detail->index_number;
                    if(str_contains($index_number,'EQ')){
                        $exam_year = explode('/',$index_number)[1];
                    }else{
                        $exam_year = explode('/', $index_number)[2];
                    }
                }
            }
    
            if($exam_year < 2014 || $exam_year > 2015){
                $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
                $diploma_principle_pass_grade = 'E';
                $diploma_subsidiary_pass_grade = 'S';
                $principle_pass_grade = 'E';
                $subsidiary_pass_grade = 'S';
            }else{
                $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
                $diploma_principle_pass_grade = 'D';
                $diploma_subsidiary_pass_grade = 'E';
                $principle_pass_grade = 'D';
                $subsidiary_pass_grade = 'E';
            }

            $o_level_points = $a_level_points = $diploma_gpa = null;
            $subject_count = 0;

            $o_level_points = $a_level_points = $diploma_gpa = null;
            $o_level_pass_count = 0;
            $o_level_other_pass_count = 0;
            $a_level_principle_pass_count = 0;
            $a_level_principle_pass_points = 0;
            $a_level_subsidiary_pass_count = 0;
            $a_level_out_principle_pass_count = 0;
            $a_level_out_subsidiary_pass_count = 0;

            $qualified = false;
            foreach ($applicant->nectaResultDetails as $detail) {
                if($detail->exam_id == 1 && $detail->verified == 1){
                    $other_must_subject_ready = false;
                    foreach ($detail->results as $result) {

                        if($o_level_grades[$result->grade] >= $o_level_grades[$campus_program->entryRequirements[0]->pass_grade]){
                            $applicant->rank_points += $o_level_grades[$result->grade];
                            $subject_count += 1;

                            if(unserialize($campus_program->entryRequirements[0]->must_subjects) != ''){
                                if(unserialize($campus_program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                    if(unserialize($campus_program->entryRequirements[0]->other_must_subjects) != '' && 
                                    (count(unserialize($campus_program->entryRequirements[0]->must_subjects)) + count(unserialize($campus_program->entryRequirements[0]->other_must_subjects))) < $campus_program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                    }elseif(count(unserialize($campus_program->entryRequirements[0]->must_subjects)) < $campus_program->entryRequirements[0]->pass_subjects && 
                                        ($o_level_other_pass_count < ($campus_program->entryRequirements[0]->pass_subjects - count(unserialize($campus_program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($campus_program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }

                            if(unserialize($campus_program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($campus_program->entryRequirements[0]->other_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                    $other_must_subject_ready = true;
                                }

                                }elseif(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                if(unserialize($campus_program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($campus_program->entryRequirements[0]->must_subjects)) + count(unserialize($campus_program->entryRequirements[0]->other_must_subjects))) < $campus_program->entryRequirements[0]->pass_subjects){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }elseif(count(unserialize($campus_program->entryRequirements[0]->must_subjects)) < $campus_program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($campus_program->entryRequirements[0]->pass_subjects - count(unserialize($campus_program->entryRequirements[0]->must_subjects))))){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }
                                }
                            }elseif(unserialize($campus_program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }
                        }
                    }
                    }elseif($detail->exam_id == 2 && $detail->verified == 1){
                        $other_advance_must_subject_ready = false;
                        $other_out_advance_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;
                                if(unserialize($campus_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_principle_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                                }elseif(unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                }
                                }else{
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }
                            if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       
                                if(unserialize($campus_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                                }elseif(unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                }
                                }else{
                                $a_level_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                                }
                            }

                            if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){

                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;
                                if(unserialize($campus_program->entryRequirements[0]->advance_must_subjects) != ''){

                                if(unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                        $a_level_out_principle_pass_count += 1;
                                        $other_out_advance_must_subject_ready = true;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }else{
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                }
                                }
                                }elseif(unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];

                                }
                                }else{
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }

                            if($a_level_grades[$result->grade] == $a_level_grades[$diploma_subsidiary_pass_grade]){

                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;
                                if(unserialize($campus_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $other_out_advance_must_subject_ready = true;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }else{
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                }
                                }
                                }elseif(unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];

                                }
                                }else{
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }

                            if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){

                                if(unserialize($campus_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                                }elseif(unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($campus_program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];

                                }
                                }else{
                                $a_level_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                                }
                            }
                        }
                    }
                }

                if(unserialize($campus_program->entryRequirements[0]->must_subjects) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $campus_program->entryRequirements[0]->principle_pass_points){

                        $qualified = true;
                    }
                }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $campus_program->entryRequirements[0]->principle_pass_points){

                    $qualified = true;

                } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                    $qualified = true;

                }

                $has_major = false;
                $equivalent_must_subjects_count = 0;
                $diploma_gpa = null;
                $out_gpa = null;

                if(unserialize($campus_program->entryRequirements[0]->equivalent_majors) != ''){
                    foreach($applicant->nacteResultDetails as $detail){
                        if($detail->verified == 1){
                            foreach(unserialize($campus_program->entryRequirements[0]->equivalent_majors) as $sub){
                                if(str_contains(strtolower($detail->programme),strtolower($sub))){
    
                                    $has_major = true;
                                }
                            }
                            $diploma_gpa = $detail->diploma_gpa;
                        }
                    }
                    if(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                        foreach($applicant->nacteResultDetails as $detail){
                            if($detail->verified == 1){
                                foreach($detail->results as $result){
                                    foreach(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                        if(str_contains(strtolower($result->subject),strtolower($sub))){
                                            $equivalent_must_subjects_count += 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else{
                    if(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                        foreach($applicant->nacteResultDetails as $detail){
                            if($detail->verified == 1){
                                foreach($detail->results as $result){
                                    foreach(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                        if(str_contains(strtolower($result->subject),strtolower($sub))){
                                            $equivalent_must_subjects_count += 1;
                                        }
                                    }
                                }
                                $diploma_gpa = $detail->diploma_gpa;
                            }
                        }
                    }
                }
                if(unserialize($campus_program->entryRequirements[0]->equivalent_majors) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $has_major && $diploma_gpa >= $campus_program->entryRequirements[0]->equivalent_gpa){

                        $qualified = true;

                    }
                }elseif(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                    if((($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects &&
                        $equivalent_must_subjects_count >= count(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects)) &&
                        $diploma_gpa >= $campus_program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $campus_program->entryRequirements[0]->pass_subjects &&
                        $applicant->avn_no_results === 1 && $diploma_gpa >= $campus_program->entryRequirements[0]->equivalent_gpa)){

                        $qualified = true;

                    }
                }

                $out_pass_subjects_count = 0;
                if(unserialize($campus_program->entryRequirements[0]->open_exclude_subjects) != '') //['OFC 017','OFP 018','OFP 020'];
                {
                    $exclude_out_subjects_codes = unserialize($campus_program->entryRequirements[0]->open_exclude_subjects);

                    foreach($applicant->outResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach($detail->results as $key => $result){
                            if(!Util::arrayIsContainedInKey($result->subject_code, $exclude_out_subjects_codes)){
                                if($out_grades[$result->grade] >= $out_grades['C']){
                                $out_pass_subjects_count += 1;
                                }
                            }
                        }
                        $out_gpa = $detail->gpa;
                    }
                    }
                }else{
                    foreach($applicant->outResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach($detail->results as $result){
                            if($out_grades[$result->grade] >= $out_grades['C']){
                                $out_pass_subjects_count += 1;
                            }
                        }
                        $out_gpa = $detail->gpa;
                    }
                    }
                }

                if(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 &&
                    $a_level_out_principle_pass_count >= 1){

                        $qualified = true;
                }

                // OUT with diploma of 2.0 and above
                if(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                    if((($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                        $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($campus_program->entryRequirements[0]->equivalent_must_subjects)) &&
                        $diploma_gpa >= $campus_program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $campus_program->entryRequirements[0]->pass_subjects &&
                        $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa)){

                            $qualified = true;

                    }
                }elseif(unserialize($campus_program->entryRequirements[0]->equivalent_majors) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa && $has_major &&
                        $diploma_gpa >= $campus_program->entryRequirements[0]->min_equivalent_gpa){

                            $qualified = true;

                    }
                }elseif(unserialize($campus_program->entryRequirements[0]->equivalent_majors) == ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa &&
                        $diploma_gpa >= $campus_program->entryRequirements[0]->min_equivalent_gpa){

                            $qualified = true;

                    }
                }

                if(($o_level_pass_count+$o_level_other_pass_count) >= $campus_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $campus_program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){

                    $qualified = true;

                }
        
                if($qualified){

                    ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('batch_id',$applicant->batch_id)->where('order',5)->update(['status'=>'SELECTED']);

                    $app = Applicant::find($applicant->id);
                    $app->status = 'ADMITTED';
                    $app->save();
                    ExternalTransfer::where('applicant_id',$app->id)->update(['status'=>'ELIGIBLE']);
                }else{
                    ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'NOT ELIGIBLE']);
                }       
            // }else{
            //     ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'NOT ELIGIBLE']);
            // }




















//             $campus_programs = $window? [$applicant->selections[0]->campusProgram] : [];


//             $award = $applicant->programLevel;
//             $programs = [];

//             $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

//             $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

//             $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

//             $selected_program = array();

//            $index_number = $applicant->index_number;
//            $exam_year = explode('/', $index_number)[2];

//            foreach($applicant->nectaResultDetails as $detail) {
//               if($detail->exam_id == 2){
//                   $index_number = $detail->index_number;
//                   $exam_year = explode('/', $index_number)[2];
//               }
//            }

//            if($exam_year < 2014 || $exam_year > 2015){
//              $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
//              $diploma_principle_pass_grade = 'E';
//              $diploma_subsidiary_pass_grade = 'S';
//              $principle_pass_grade = 'D';
//              $subsidiary_pass_grade = 'S';
//            }else{
//              $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
//              $diploma_principle_pass_grade = 'D';
//              $diploma_subsidiary_pass_grade = 'E';
//              $principle_pass_grade = 'C';
//              $subsidiary_pass_grade = 'E';
//            }
//            // $selected_program[$applicant->id] = false;
//            $subject_count = 0;
// 		   $has_capacity = true;
//               foreach($campus_programs as $program){

//                   if(count($program->entryRequirements) == 0){
//                     return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
//                   }

//                   // if($program->entryRequirements[0]->max_capacity == null){
//                   //   return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
//                   // }

//                    // Certificate
//                    if(str_contains($award->name,'Certificate')){
//                        $o_level_pass_count = 0;
//                        foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
//                          if($detail->exam_id == 1){
//                            $other_must_subject_ready = false;
//                            foreach ($detail->results as $key => $result) {

//                               if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

//                                 // $applicant->rank_points += $o_level_grades[$result->grade];
//                                 $subject_count += 1;

//                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
//                                          $o_level_pass_count += 1;
//                                          $other_must_subject_ready = true;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
//                                          $o_level_pass_count += 1;
//                                     }
//                                  }else{
//                                     $o_level_pass_count += 1;
//                                  }
//                               }
//                            }
//                          }
//                          if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
//                            $programs[] = $program;
//                          }
//                        }
//                    }

//                    // Diploma
//                    if(str_contains($award->name,'Diploma')){
//                        $o_level_pass_count = 0;
//                        $a_level_principle_pass_count = 0;
//                        $a_level_subsidiary_pass_count = 0;
//                        $diploma_major_pass_count = 0;
//                        foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
//                          if($detail->exam_id == 1){
//                            $other_must_subject_ready = false;
//                            foreach ($detail->results as $key => $result) {

//                               if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

//                                 // $applicant->rank_points += $o_level_grades[$result->grade];
//                                 $subject_count += 1;


//                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
//                                          $o_level_pass_count += 1;
//                                          $other_must_subject_ready = true;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
//                                          $o_level_pass_count += 1;
//                                     }
//                                  }else{
//                                      $o_level_pass_count += 1;
//                                  }
//                               }
//                            }
//                          }elseif($detail->exam_id === 2){
//                            $other_advance_must_subject_ready = false;
//                            $other_advance_subsidiary_ready = false;
//                            foreach ($detail->results as $key => $result) {

//                               if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

//                                  // $applicant->rank_points += $a_level_grades[$result->grade];
//                                  $subject_count += 1;
//                                  if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_principle_pass_count += 1;
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
//                                          $a_level_principle_pass_count += 1;
//                                          $other_advance_must_subject_ready = true;
//                                        }

//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_principle_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                         $a_level_principle_pass_count += 1;

//                                     }
//                                  }else{
//                                     $a_level_principle_pass_count += 1;
//                                  }
//                               }
//                               if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){
//                               // if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

//  /*                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                  } */
// 								 //lupi changed to properly count subsidiary points
// 								 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
//                                          $a_level_subsidiary_pass_count += 1;
//                                          $other_advance_must_subject_ready = true;
//                                        }

//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                         $a_level_subsidiary_pass_count += 1;

//                                     }
//                                  }else{
//                                     $a_level_subsidiary_pass_count += 1;
//                                  }
//                               }
//                            }
//                          }

//                        }

//                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && (($a_level_principle_pass_count > 0
// 					   && ($a_level_subsidiary_pass_count + $a_level_principle_pass_count >= 2)) || $a_level_principle_pass_count >= 2)){
//                            $programs[] = $program;
//                         }

//                        $has_btc = false;


//                        if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
//                            foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
//                                 foreach($applicant->nacteResultDetails as $det){
//                                    if(str_contains(strtolower($det->programme),strtolower($sub)) && str_contains(strtolower($det->programme),'basic')){
//                                      $has_btc = true;
//                                    }
//                                 }
//                            }
//                        }else{       // lupi added the else part to determine btc status when equivalent majors have not been defined
//                             foreach($applicant->nacteResultDetails as $det){
//                                    if(str_contains(strtolower($det->programme),'basic')){
//                                      $has_btc = true;
//                                    }
//                                 }
//                        }


//                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){
//                            $programs[] = $program;
//                        }
//                    }

//                    // Bachelor
//                    if(str_contains($award->name,'Bachelor')){
//                        $o_level_pass_count = 0;
//                        $a_level_principle_pass_count = 0;
//                        $a_level_principle_pass_points = 0;
//                        $a_level_subsidiary_pass_count = 0;
//                        $a_level_out_principle_pass_count = 0;
//                        $a_level_out_principle_pass_points = 0;
//                        $a_level_out_subsidiary_pass_count = 0;
//                        $diploma_pass_count = 0;

//                        foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
//                          if($detail->exam_id == 1){
//                            $other_must_subject_ready = false;
//                            foreach ($detail->results as $key => $result) {

//                               if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

//                                  // $applicant->rank_points += $o_level_grades[$result->grade];
//                                  $subject_count += 1;

//                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
//                                          $o_level_pass_count += 1;
//                                          $other_must_subject_ready = true;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
//                                          $o_level_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
//                                          $o_level_pass_count += 1;
//                                     }
//                                  }else{
//                                       $o_level_pass_count += 1;
//                                  }
//                               }
//                            }
//                          }elseif($detail->exam_id == 2){
//                            $other_advance_must_subject_ready = false;
//                            $other_advance_subsidiary_ready = false;
//                            $other_out_advance_must_subject_ready = false;
//                            $other_out_advance_subsidiary_ready = false;
//                            foreach ($detail->results as $key => $result) {

//                               if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

//                                  // $applicant->rank_points += $a_level_grades[$result->grade];
//                                  $subject_count += 1;
//                                  if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_principle_pass_count += 1;
//                                          $a_level_principle_pass_points += $a_level_grades[$result->grade];
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
//                                          $a_level_principle_pass_count += 1;
//                                          $other_advance_must_subject_ready = true;
//                                          $a_level_principle_pass_points += $a_level_grades[$result->grade];
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_principle_pass_count += 1;
//                                          $a_level_principle_pass_points += $a_level_grades[$result->grade];
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_principle_pass_count += 1;
//                                          $a_level_principle_pass_points += $a_level_grades[$result->grade];
//                                     }
//                                  }else{
//                                      $a_level_principle_pass_count += 1;
//                                      $a_level_principle_pass_points += $a_level_grades[$result->grade];
//                                  }
//                               }
//                               if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){
//                               // if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){		original
// /*                                  if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){			original
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                  } */

// /*								 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){			// lupi changed this to get rid of subsidiary_subjects
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){     original
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                     }
//                                  }
//        */                        if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
//                                          $a_level_subsidiary_pass_count += 1;
//                                          $other_advance_must_subject_ready = true;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_subsidiary_pass_count += 1;
//                                     }
//                                  }else{
//                                      $a_level_subsidiary_pass_count += 1;
//                                  }
//                              }

//                               if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){    // lupi reduce the filter
//                               // if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){     original

//                                  // $applicant->rank_points += $a_level_grades[$result->grade];
//                                  $subject_count += 1;
//                                  if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_out_principle_pass_count += 1;
//                                          $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
//                                          $a_level_out_principle_pass_count += 1;
//                                          $other_out_advance_must_subject_ready = true;
//                                          $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_out_principle_pass_count += 1;
//                                          $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_out_principle_pass_count += 1;
//                                          $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
//                                     }
//                                  }else{
//                                      $a_level_out_principle_pass_count += 1;
//                                      $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
//                                  }
//                               }
//                               if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){   // lupi changed to reduce the scope and get rid of diploma_subsidiary_pass_grade
//                               // if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_subsidiary_pass_grade]){    original
// /*                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){      original
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                        }
//                                  }
// */
//  /*                               if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){          // lupi changed this to get rid of subsidiary_subjects
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                         if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                         }
//                                     }else{
//                                         if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                         }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                     }
//                                  }*/
//                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
//                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                        }

//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                          $other_advance_must_subject_ready = true;
//                                        }
//                                     }else{
//                                        if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                        }
//                                     }
//                                  }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
//                                     if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
//                                          $a_level_out_subsidiary_pass_count += 1;
//                                     }
//                                  }else{
//                                      $a_level_out_subsidiary_pass_count += 1;
//                                  }
//                               }
//                            }
//                          }
//                        }

//                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2){       // lupi changed to discard principle_pass_points

// /*                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects &&         original $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){
// */
//                            $programs[] = $program;
//                        }

//                        // foreach ($applicant->nacteResultDetails as $detailKey=>$detail) {
//                        //   foreach ($detail->results as $key => $result) {
//                        //        if($diploma_grades[$result->grade] >= $diploma_grades[$program->entryRequirements[0]->equivalent_average_grade]){
//                        //           $diploma_pass_count += 1;
//                        //        }
//                        //     }
//                        //  }

//                        $has_major = false;
//                        $equivalent_must_subjects_count = 0;
//                        $nacte_gpa = null;
//                        $out_gpa = null;

//                        if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
//                            foreach($applicant->nacteResultDetails as $detail){
//                              foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){

//                                 if(str_contains(strtolower($detail->programme),strtolower($sub))){   //lupi changed to convert all to lower cases
//                                 //if(str_contains($detail->programme,$sub)){
//                                    $has_major = true;
//                                 }
//                              }
//                              $nacte_gpa = $detail->diploma_gpa;
//                            }
//                        }else{
//                           if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
//                               foreach($applicant->nacteResultDetails as $detail){
//                                   foreach($detail->results as $result){
//                                       foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
//                                           if(str_contains($result->subject,$sub)){
//                                               $equivalent_must_subjects_count += 1;
//                                           }
//                                       }
//                                   }
//                                   $nacte_gpa = $detail->diploma_gpa;
//                               }
//                           }
//                        }

//                         if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && unserialize($program->entryRequirements[0]->equivalent_majors) == ''){       // lupi changed to prevent programmes with both majors and equivalent subjects      Original
//                        /*if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){*/
//                             if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_major && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){

//                                $programs[] = $program;
//                             }
//                         }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
//                             if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

//                                $programs[] = $program;
//                             }
//                         }


//                         $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects); //['OFC 017','OFP 018','OFP 020'];
//                         $out_pass_subjects_count = 0;

//                         foreach($applicant->outResultDetails as $detail){
//                             foreach($detail->results as $key => $result){
//                                 if(!in_array($result->code, $exclude_out_subjects_codes)){
//                                    if($out_grades[$result->grade] >= $out_grades['C']){
//                                       $out_pass_subjects_count += 1;
//                                    }
//                                 }
//                             }
//                             $out_gpa = $detail->gpa;

//                         }


//                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa &&
//                              $a_level_out_subsidiary_pass_count >= 1 && $a_level_out_principle_pass_count >= 1){
//                                 $programs[] = $program;
//                         }

//                         if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
//                             if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa &&
//                                 $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa)
//                                 || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){
//                                     $programs[] = $program;
//                             }
//                         }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
//                             if($out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){
//                                     $programs[] = $program;
//                             }
//                         }

//                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){
//                               $programs[] = $program;
//                         }
//                 }
//             }

// 			if(count($programs) != 0){
// 				if($programs[0]->id == $applicant->selections[0]->campus_program_id){
// 				   $selection = ApplicantProgramSelection::find($applicant->selections[0]->id);
// 				   $selection->status = 'SELECTED';
// 				   $selection->save();

// 				   $app = Applicant::find($applicant->id);
// 				   $app->status = 'ADMITTED';
// 				   $app->save();

// 				   ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'ELIGIBLE']);
// 				}
// 			}else{
// 				ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'NOT ELIGIBLE']);
// 			}
         }
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

        if($applicant->basic_info_complete_status != 1){
            return redirect()->back()->with('error','Please complete basic information first.');
        }

        $fee_amount = FeeAmount::with(['feeItem.feeType'])->find($request->get('fee_amount_id'));
        $usd_currency = Currency::where('code','USD')->first();

        $invoiceRequestCheck = Invoice::where('payable_id', $applicant->id)->where('payable_type', 'applicant')->where('applicable_id', $applicant->application_window_id)->where('applicable_type', 'application_window')->where('fee_type_id', $fee_amount->feeItem->fee_type_id)->first();

        if(!$invoiceRequestCheck){
            $invoice = new Invoice;
            $invoice->reference_no = 'MNMA-'.time();
            if(str_contains($applicant->nationality,'Tanzania')){
               $invoice->amount = round($fee_amount->amount_in_tzs);
               $invoice->actual_amount = $invoice->amount;
               $invoice->currency = 'TZS';
            }else{
               $invoice->amount = round($fee_amount->amount_in_usd*$usd_currency->factor);
               $invoice->actual_amount = $invoice->amount;
               $invoice->currency = 'TZS';//'USD';
            }
            $invoice->payable_id = $applicant->id;
            $invoice->payable_type = 'applicant';
            $invoice->applicable_id = $applicant->application_window_id;
            $invoice->applicable_type = 'application_window';
            $invoice->fee_type_id = $fee_amount->feeItem->fee_type_id;
            $invoice->save();


            $payable = Invoice::find($invoice->id)->payable;
            $fee_type = $fee_amount->feeItem->feeType;

            $firstname = str_contains($payable->first_name,"'")? str_replace("'","",$payable->first_name) : $payable->first_name;
            $surname = str_contains($payable->surname,"'")? str_replace("'","",$payable->surname) : $payable->surname;

            $generated_by = 'SP';
            $approved_by = 'SP';
            $inst_id = Config::get('constants.SUBSPCODE');

            $number_filter = preg_replace('/[^0-9]/','',$payable->email);
            $payer_email = empty($number_filter)? $payable->email : 'admission@mnma.ac.tz';
            return $this->requestControlNumber($request,
                                      $invoice->reference_no,
                                      $inst_id,
                                      $invoice->amount,
                                      $fee_type->description,
                                      $fee_type->gfs_code,
                                      $fee_type->payment_option,
                                      $payable->id,
                                      $firstname.' '.$surname,
                                      $payable->phone,
                                      $payer_email,
                                      $generated_by,
                                      $approved_by,
                                      $fee_type->duration,
                                      $invoice->currency);
        }else{
            return redirect()->back()->with('error','Control number already requesed, Please use the control number already requested for payments');
        }

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
            'index_number'=>'required',
            'entry_mode'=>'required',
            'password'=>'required|min:8',
			'password_confirmation'=>'same:password'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $pattern = null;
        if(str_contains(strtolower($request->get('index_number')),'eq')){
            $pattern = "/eq(?:[0-9]{10}\/[0-9]{4})/i";
        }else{
            $pattern = "/^[A-Za-z]{1}\d{4}\/\d{4}\/\d{4}$/";
        }

        if(!preg_match($pattern,$request->get('index_number'))){
            return redirect()->back()->with('error','Incorrect index number');
        }

        $graduate = Applicant::whereHas('student',function($query){$query
                                                ->where('studentship_status_id',2);})
                             ->where('index_number',$request->get('index_number'))
                             ->first();

        $previous_intake_applicant = null;
        $march_intake = ApplicationWindow::where('status','ACTIVE')->where('intake_id',2)->first();

        if(!empty($march_intake)){
            $previous_intake_applicant = Applicant::whereDoesntHave('intake',function($query){$query->where('name','March');})
                                                  ->where('index_number',$request->get('index_number'))
                                                  ->where('programs_complete_status',0)
                                                  ->whereNull('is_tamisemi')
                                                  ->latest()
                                                  ->first();
        }else{
            $previous_intake_applicant = Applicant::whereDoesntHave('intake',function($query){$query->where('name','September');})
                                                  ->where('index_number',$request->get('index_number'))
                                                  ->where('programs_complete_status',0)
                                                  ->whereNull('is_tamisemi')
                                                  ->latest()
                                                  ->first();
        }

        $other = Applicant::where('index_number',$request->get('index_number'))
                          ->first();

        if($other && !$previous_intake_applicant && !$graduate){
            return redirect()->back()->with('error','The index number has already been used.');
        }

        DB::beginTransaction();
        if($usr = User::where('username',$request->get('index_number'))->first()){
            $user = $usr;
        }else{
            $user = new User;
            $user->username = strtoupper($request->get('index_number'));
            $user->password = Hash::make($request->get('password'));
            $user->save();
        }

        $role = Role::where('name','applicant')->first();
        $user->roles()->sync([$role->id]);

        if($previous_intake_applicant){
            if($previous_intake_applicant->results_complete_status == 1){
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                NectaResultDetail::where('applicant_id',$previous_intake_applicant->id)->update(['applicant_id'=>0]);
                NectaResult::where('applicant_id',$previous_intake_applicant->id)->update(['applicant_id'=>0]);

                if($previous_intake_applicant->entry_mode == 'EQUIVALENT'){
                    NacteResultDetail::where('applicant_id',$previous_intake_applicant->id)->update(['applicant_id'=>0]);
                    NacteResult::where('applicant_id',$previous_intake_applicant->id)->update(['applicant_id'=>0]);
                    OutResultDetail::where('applicant_id',$previous_intake_applicant->id)->update(['applicant_id'=>0]);;
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            
            $previous_intake_applicant->first_name = strtoupper($request->get('first_name'));
            $previous_intake_applicant->middle_name = strtoupper($request->get('middle_name'));
            $previous_intake_applicant->surname = strtoupper($request->get('surname'));
            $previous_intake_applicant->campus_id = 0;
            $previous_intake_applicant->index_number = strtoupper($request->get('index_number'));
            $previous_intake_applicant->entry_mode = $request->get('entry_mode');
            $previous_intake_applicant->program_level_id = $request->get('program_level_id');
            $previous_intake_applicant->application_window_id = null;
            $previous_intake_applicant->batch_id = null;
            $previous_intake_applicant->intake_id = null;
            $previous_intake_applicant->email =$request->get('email');
            $previous_intake_applicant->phone = null;
            $previous_intake_applicant->birth_date = null;
            $previous_intake_applicant->nationality = null;
            $previous_intake_applicant->gender = null;
            $previous_intake_applicant->disability_status_id = null;
            $previous_intake_applicant->address = null;
            $previous_intake_applicant->country_id = null;
            $previous_intake_applicant->region_id = null;
            $previous_intake_applicant->district_id = null;
            $previous_intake_applicant->ward_id = null;
            $previous_intake_applicant->street = null;
            $previous_intake_applicant->nin = null;
            $previous_intake_applicant->is_tcu_verified = null;
            $previous_intake_applicant->diploma_certificate = null;
            $previous_intake_applicant->basic_info_complete_status = 0;
            $previous_intake_applicant->payment_complete_status = 0;
            $previous_intake_applicant->results_complete_status = 0;
            $previous_intake_applicant->teacher_diploma_certificate = null;
            $previous_intake_applicant->veta_certificate = null;
            $previous_intake_applicant->veta_status = null;
            $previous_intake_applicant->rank_points = null;
            $previous_intake_applicant->nacte_reg_no = null;
            $previous_intake_applicant->avn_no_results = null;
            $previous_intake_applicant->teacher_certificate_status = null;
            $previous_intake_applicant->next_of_kin_id = null;
            $previous_intake_applicant->next_of_kin_complete_status = 0;
            $previous_intake_applicant->save();
        }else{
            $applicant = new Applicant;
            $applicant->first_name = strtoupper($request->get('first_name'));
            $applicant->middle_name = strtoupper($request->get('middle_name'));
            $applicant->surname = strtoupper($request->get('surname'));
            $applicant->user_id = $user->id;
            $applicant->campus_id = 0;
            $applicant->index_number = strtoupper($request->get('index_number'));
            $applicant->entry_mode = $request->get('entry_mode');
            $applicant->program_level_id = $request->get('program_level_id');
            $applicant->save();
        }
        DB::commit();

        return redirect()->to('application/login')->with('message','Applicant registered successfully');

    }

    /**
     * Display run selection page
     */
    public function showRunSelection(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
       ///return ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->get();
        $data = [
           'staff'=>$staff,
           'awards'=>Award::all(),
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'request'=>$request,
           'batches'=>ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->get(),
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
     * Select Applicant
     */

    public function selectApplicant(Request $request)
    {
        $decision               = $request->get('decision_btn');
        $applicant_id           = $request->get('applicant_id');
        $application_window_id  = $request->get('application_window_id');
        $program_code           = $request->get('program_code');
        $staff                  = User::find(Auth::user()->id)->staff;

 /*        $closed_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('end_date','>=', implode('-', explode('-', now()->format('Y-m-d'))))
                                            ->where('status','INACTIVE')->latest()->first();
  */

       if($program_code == null){
        return redirect()->back()->with('error','This action cannot be performed now.');
       }

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','INACTIVE')->latest()->first()){
            return redirect()->back()->with('error','Application window is not active');
        }

        $applicant = Applicant::select('id','program_level_id','status')->where('id',$request->get('applicant_id'))->with('programLevel:id,name')->first();

        if(str_contains(strtolower($applicant->programLevel->name),'basic') || str_contains(strtolower($applicant->programLevel->name),'diploma')){
            // $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            // ->where('bsc_end_date','>=',now()->format('Y-m-d'))->first();

            $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  now()->format('Y-m-d'))->latest()->first();

        }elseif(str_contains(strtolower($applicant->programLevel->name),'bachelor')){
            // $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            // ->where('bsc_end_date','>=',now()->format('Y-m-d'))->first();

            $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  now()->format('Y-m-d'))->latest()->first();

        }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){
            // $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            // ->where('msc_end_date','>=',now()->format('Y-m-d'))->first();

            $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }

            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  now()->format('Y-m-d'))->latest()->first();
            
        }

        if($window_batch){
            return redirect()->back()->with('error','Application window not closed yet');
        }

        $batch_id = $batch_no = 0;

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))
        ->where('program_level_id',$applicant->program_level_id)->latest()->first();

        if($batch->batch_no > 1){
                    if(Applicant::whereDoesntHave('selections',function($query) use($request, $batch){$query->whereIn('status',['SELECTED','PENDING','APPROVING'])
                        ->where('application_window_id',$request->get('application_window_id'))
                        ->where('batch_id',$batch->id);})
                        ->where('programs_complete_status', 1)
                        ->where('application_window_id', $request->get('application_window_id'))
                        ->whereNull('status')
                        ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                                $batch_id = $batch->id;
                                $batch_no = $batch->batch_no;

                            }else{
                $previous_batch = null;

                $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))
                ->where('program_level_id',$applicant->program_level_id)->where('batch_no', $batch->batch_no - 1)->first();
                $batch_id = $previous_batch->id;
                $batch_no = $previous_batch->batch_no;

            }
        }else{
            $batch_id = $batch->id;
            $batch_no = $batch->batch_no;
        }

        $campus_program = CampusProgram::select('id')->where('code',$program_code)->where('campus_id',$staff->campus_id)->first();

        $applicant->status = "SELECTED";
        $applicant->save();

        $applicant->program_level_id >= 5? ApplicantProgramSelection::where('campus_program_id',$campus_program->id)->where('applicant_id', $applicant_id)
                                                                    ->where('application_window_id', $application_window_id)->update(['status' => 'SELECTED']):
                                          ApplicantProgramSelection::where('campus_program_id',$campus_program->id)->where('applicant_id', $applicant_id)
                                          ->where('application_window_id', $application_window_id)->update(['status' => 'APPROVING']);

        $current_batch = ApplicationBatch::select('id')->where('application_window_id', $request->get('application_window_id'))
        ->where('program_level_id',$applicant->program_level_id)->latest()->first();

        if($current_batch->id == $batch_id){
            $new_batch = new ApplicationBatch;
            $new_batch->application_window_id = $request->get('application_window_id');
            $new_batch->program_level_id = $applicant->program_level_id;
            $new_batch->batch_no = $batch_no + 1;
            $new_batch->begin_date = date('Y-m-d');
            $new_batch->end_date = date('Y-m-d');

            $new_batch->save();

            Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$applicant->program_level_id)
                        ->where('programs_complete_status',0)->update(['batch_id'=>$new_batch->id]);
        }

        return redirect()->to('application/other-applicants')->with('message','Applicant selected successfully');

    }

    /**
     * Run application selection
     */
    public function runSelection(Request $request)
    {
        if($request->get('award_id') >= 5){
            return redirect()->back()->with('error','Selection for this programme level cannot be conducted by the system');
        }

        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;

        // $closed_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','INACTIVE')->latest()->first();
        // changed closed window query

/*         $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        ->where('end_date','>=', implode('-', explode('-', now()->format('Y-m-d'))))
        ->where('status','INACTIVE')->latest()->first(); */

        $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        ->where('status','INACTIVE')->latest()->first();

        if($closed_window){
            return redirect()->back()->with('error','Application window is inactive');
        }

        $award = Award::find($request->get('award_id'));

        if(str_contains(strtolower($award->name),'basic') || str_contains(strtolower($award->name),'diploma')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('end_date','>=',now()->format('Y-m-d'))->first();
        }elseif(str_contains(strtolower($award->name),'bachelor')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('bsc_end_date','>=',now()->format('Y-m-d'))->first();
        }elseif(str_contains(strtolower($award->name),'master')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('msc_end_date','>=',now()->format('Y-m-d'))->first();
        }

        if($open_window){
             return redirect()->back()->with('error','Application window not closed yet');
        }

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('end_date','>=',implode('-', explode('-', now()->format('Y-m-d'))))->where('status','INACTIVE')->latest()->first()){
             return redirect()->back()->with('error','Application window is not active');
        }

        $batch_id = $batch_no = 0;
        if(!empty($request->get('award_id'))){
            $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))->where('program_level_id',$request->get('award_id'))->latest()->first();

            if($batch->batch_no > 1){
                if(Applicant::whereDoesntHave('selections',function($query) use($request, $batch){$query->whereIn('status',['SELECTED','PENDING','APPROVING'])
                    ->where('application_window_id',$request->get('application_window_id'))
                    ->where('batch_id',$batch->id);})
                    ->where('programs_complete_status', 1)
                    ->where('application_window_id', $request->get('application_window_id'))
                    ->whereNull('status')
                    ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                            $batch_id = $batch->id;
                            $batch_no = $batch->batch_no;
                        }else{
                            $previous_batch = null;

                            $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))
                            ->where('program_level_id',$award->id)->where('batch_no', $batch->batch_no - 1)->first();
                            $batch_id = $previous_batch->id;
                            $batch_no = $previous_batch->batch_no;

                }
            }else{
                $batch_id = $batch->id;
                $batch_no = $batch->batch_no;
            }

        }

        // Phase I
/*         $campus_programs = CampusProgram::whereHas('applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('award_id'));
        })->with(['program','entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('campus_id',$staff->campus_id)->get();
 */

        $campus_programs = CampusProgram::select('id','program_id')
                                        ->whereHas('applicationWindows',function($query) use($request){$query->where('id',$request->get('application_window_id'));})
                                        ->whereHas('program',function($query) use($request){ $query->where('award_id',$request->get('award_id'));})
                                        ->whereHas('entryRequirements', function($query) use($request){$query->where('application_window_id',$request->get('application_window_id'));})
                                        ->with(['program:id,name','entryrequirements:id,max_capacity,campus_program_id'])->where('campus_id',$staff->campus_id)->get();

        foreach($campus_programs as $program){
            $count_selections = ApplicantProgramSelection::where('campus_program_id', $program->id)->where('batch_id',$batch_id)->where('status', 'APPROVING')->count();
            $count[$program->id] = $count_selections;
        }

        if (Auth::user()->hasRole('admission-officer')) {

/*             $applicants = Applicant::whereHas('selections',function($query) use($request, $staff, $batch_id){
                $query->where('application_window_id',$request->get('application_window_id'))->where('batch_id',$batch_id)->where('campus_id', $staff->campus_id);
            })->with(['selections'=>function($query) use($batch_id){$query->where('batch_id',$batch_id);},'nectaResultDetails.results','nacteResultDetails.results'])
            ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get(); */

/*             $applicants = Applicant::whereHas('selections',function($query) use($request, $staff, $batch_id){$query->where('id',$request->get('application_window_id'))
                                    ->where('batch_id',$batch_id)->where('campus_id', $staff->campus_id)->where('status','ELIGIBLE');})
                                    ->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])
                                    ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get(); */
            $applicants = Applicant::select('id','rank_points','program_level_id','avn_no_results','entry_mode','teacher_certificate_status','batch_id')
                                    ->whereHas('selections',function($query) use($request, $batch_id){$query->where('application_window_id',$request->get('application_window_id'))
                                    ->where('batch_id',$batch_id)->where('status','ELIGIBLE');})
                                    ->with(['selections:id,order,batch_id,campus_program_id,status,applicant_id','nacteResultDetails:id,applicant_id',
                                    'nacteResultDetails.results:id,nacte_result_detail_id'])->whereNull('status')
                                    ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();


        }else{
            return redirect()->back()->with('error','Sorry, this task can only be done by a respective Admission Officer.');

        }

        // Phase II
        $choices = array(1,2,3,4);
/*         $applicants = Applicant::with(['selections'=>function($query) use($batch_id){$query->where('batch_id',$batch_id);},'nectaResultDetails.results','nacteResultDetails.results'])
        ->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->get(); */



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

        $selection_status = false;
        foreach($choices as $choice){
            foreach ($campus_programs as $program) {

                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                if($program->entryRequirements[0]->max_capacity == null){
                     return redirect()->back()->with('error','mMximum capacity for '.$program->program->name.' have not been specified.');
                }

                if(isset($program->entryRequirements[0])){

                    foreach($applicants as $applicant){
                        $has_results = true;
                        if($applicant->teacher_certificate_status !== 1){
                            if(count($applicant->nacteResultDetails) != 0 && $applicant->program_level_id !=2){
                                if(count($applicant->nacteResultDetails[0]->results) == 0){
                                    $has_results = false;
                                }
                            }
                            if($has_results){
                                foreach($applicant->selections as $selection){
                                    if($selection->order == $choice && $selection->batch_id == $batch_id && $selection->campus_program_id == $program->id){
                                        if($count[$program->id] < $program->entryRequirements[0]->max_capacity && !$selected_program[$applicant->id]){
                                            if((ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->where('batch_id',$batch->id)->count() == 0) &&
                                                $applicant->avn_no_results !== 1 || ($applicant->avn_no_results == 1 && $applicant->entry_mode == 'DIRECT')){
                                                $select = ApplicantProgramSelection::find($selection->id);
                                                $select->status = 'APPROVING';
                                                $select->status_changed_at = now();
                                                $select->save();

                                                Applicant::where('id',$applicant->id)->update(['status'=>'SELECTED']);
                                                $selection_status = true;
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
           }
        }

        if($selection_status){
            $current_batch = ApplicationBatch::select('id')->where('application_window_id', $request->get('application_window_id'))
            ->where('program_level_id',$request->get('award_id'))->latest()->first();

            if($current_batch->id == $batch_id){
                $new_batch = new ApplicationBatch;
                $new_batch->application_window_id = $request->get('application_window_id');
                $new_batch->program_level_id = $request->get('award_id');
                $new_batch->batch_no = $batch_no + 1;
                $new_batch->begin_date = date('Y-m-d');
                $new_batch->end_date = date('Y-m-d');

                $new_batch->save();

                Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('award_id'))
                          ->where('programs_complete_status',0)->update(['batch_id'=>$new_batch->id]);
            }

            return redirect()->back()->with('message','Selection run successfully');
        }else{
            return redirect()->back()->with('error','Selection has not been successfully. Please try again.');
        }

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

        if(str_contains(strtolower($prog->program->award_id),1) || str_contains(strtolower($prog->program->award_id),2)){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first();
        }elseif(str_contains(strtolower($prog->program->award_id),4)){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('bsc_end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first();
        }elseif(str_contains(strtolower($prog->program->award_id),5)){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('msc_end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first();
        }

        if($open_window){
             return redirect()->back()->with('error','Application window not closed yet');
        }

        $batch_id = $batch_no = 0;
        if(!empty($request->get('campus_program_id'))){
            $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))
                                        ->where('program_level_id',$prog->program->award_id)->latest()->first();

            if($batch->batch_no > 1){
                if(Applicant::whereDoesntHave('selections',function($query) use($request, $batch){$query->whereIn('status',['SELECTED','PENDING','APPROVING'])
                    ->where('application_window_id',$request->get('application_window_id'))
                    ->where('batch_id',$batch->id);})
                    ->where('programs_complete_status', 1)
                    ->where('application_window_id', $request->get('application_window_id'))
                    ->whereNull('status')
                    ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                            $batch_id = $batch->id;
                            $batch_no = $batch->batch_no;

                        }else{

                                $previous_batch = null;
                                if($batch->batch_no > 1){
                                    $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$prog->program->award_id)
                                                                        ->where('batch_no', $batch->batch_no - 1)->first();
                                    $batch_id = $previous_batch->id;
                                    $batch_no = $previous_batch->batch_no;
                                }
                }
            }else{
                $batch_id = $batch->id;
                $batch_no = $batch->batch_no;
            }

        }

        if(ApplicantProgramSelection::whereHas('applicant',function($query) use($request,$prog,){
            $query->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$prog->program->award_id);
        })->where('status','APPROVING')->where('batch_id',$batch_id)->count() == 0){
            return redirect()->back()->with('error','You cannot run selection by programme before running by NTA level');
        }

        $program = CampusProgram::select('id','program_id')
        ->whereHas('applicationWindows',function($query) use($request){$query->where('id',$request->get('application_window_id'));})
        ->whereHas('program',function($query) use($request){ $query->where('award_id',$request->get('award_id'));})
        ->whereHas('entryRequirements', function($query) use($request){$query->where('application_window_id',$request->get('application_window_id'));})
        ->with(['program:id,name','entryrequirements:id,max_capacity,campus_program_id'])->where('id',$request->get('campus_program_id'))->first();

        $count = ApplicantProgramSelection::where('campus_program_id', $program->id)->where('batch_id',$batch_id)->whereIn('status',['APPROVING','SELECTED'])->count();

        if (Auth::user()->hasRole('admission-officer')) {

            $applicants = Applicant::select('id','rank_points','program_level_id','avn_no_results','entry_mode','teacher_certificate_status','batch_id')
                                    ->whereHas('selections',function($query) use($request, $batch_id){$query->where('application_window_id',$request->get('application_window_id'))
                                    ->where('batch_id',$batch_id)->whereNotIn('status',['APPROVING','SELECTED'])->where('campus_program_id',$request->get('campus_program_id'));})
                                    ->with(['selections:id,order,batch_id,campus_program_id,status,applicant_id','nacteResultDetails:id,applicant_id',
                                    'nacteResultDetails.results:id,nacte_result_detail_id'])->where('status',null)->whereNull('is_tamisemi')->get();

        }else{
            return redirect()->back()->with('error','Sorry, this task can only be done by a respective Admission Officer.');

        }
        // Phase I
/*
        $campus_programs = CampusProgram::whereHas('applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->with(['program','entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('id',$request->get('campus_program_id'))->get();

        foreach($campus_programs as $program){
            $count_selections = ApplicantProgramSelection::where('campus_program_id', $program->id)->where('status', 'APPROVING')->where('batch_id',$batch->id)->count();
            $count[$program->id] = $count_selections;
        }

        $award = Award::find($request->get('award_id'));

        if(Auth::user()->hasRole('admission-officer')){
            $applicants = Applicant::whereHas('selections',function($query) use($request, $staff, $batch){
                $query->where('application_window_id',$request->get('application_window_id'))->where('batch_id',$batch->id)->where('campus_id', $staff->campus_id);
            })->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();
        }else{
            $applicants = Applicant::whereHas('selections',function($query) use($request, $batch){
                $query->where('application_window_id',$request->get('application_window_id'))->where('batch_id',$batch->id);
            })->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();
        } */


        // Phase II
        $choices = array(1,2,3,4);

/*         $applicants = Applicant::with(['selections'=>function($query) use($batch){$query->where('batch_id',$batch->id);},'nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$prog->program->award_id)
                        ->whereHas('selections',function($query) use($request){$query->where('application_window_id',$request->get('application_window_id'));})->get();
 */
        for($i = 0; $i < count($applicants); $i++){
            for($j = $i + 1; $j < count($applicants); $j++){
               if($applicants[$i]->rank_points < $applicants[$j]->rank_points){
                 $temp = $applicants[$i];
                 $applicants[$i] = $applicants[$j];
                 $applicants[$j] = $temp;
               }
            }
        }
/*
        $selected_program = [];
        foreach ($applicants as $applicant) {
          $selected_program[$applicant->id] = false;
        }
 */
        $selection_status = false;
        foreach($choices as $choice){
            //foreach ($campus_programs as $program) {

/*                 if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                if($program->entryRequirements[0]->max_capacity == null){
                     return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                } */

                if(isset($program->entryRequirements[0])){
                    foreach($applicants as $applicant){
                        $has_results = true;
                        if($applicant->teacher_certificate_status !== 1){
                            if(count($applicant->nacteResultDetails) != 0 && $applicant->program_level_id !=2){
                                if(count($applicant->nacteResultDetails[0]->results) == 0){
                                    $has_results = false;
                                }
                            }

                            if($has_results){
                                foreach($applicant->selections as $selection){

                                    if($selection->order == $choice && $selection->batch_id == $batch_id && $selection->campus_program_id == $program->id){
                                        if($count < $program->entryRequirements[0]->max_capacity){
                                            if((ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->where('batch_id',$batch_id)->count() == 0) &&
                                                $applicant->avn_no_results !== 1 || ($applicant->avn_no_results == 1 && $applicant->entry_mode == 'DIRECT')){
                                                $select = ApplicantProgramSelection::find($selection->id);
                                                $select->status = 'APPROVING';
                                                $select->status_changed_at = now();
                                                $select->save();

                                                Applicant::where('id',$applicant->id)->update(['status'=>'SELECTED']);

                                                $selection_status = true;
                                                $selected_program[$applicant->id] = true;

                                                $count++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
           //}
        }

        if($selection_status){
            return redirect()->back()->with('message','Selection run successfully');
        }else{
            return redirect()->back()->with('error','Selection has not been successfully. Please try again.');
        }
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
        return view('dashboard.application.applicant-admission',$data)->withTitle('Applicant Registration');
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

        if(!$ac_year){
            return redirect()->back()->with('error','No active academic year');
        }

        $selection = ApplicantProgramSelection::with('campusProgram.program')->where('applicant_id',$request->get('applicant_id'))->where('status','SELECTED')->first();
        $reg_dates = SpecialDate::where('study_academic_year_id',$ac_year->id)->where('name','New Registration Period')->where('campus_id',$staff->campus_id)->first();
        
        $reg_date = null;
        if(count($reg_dates) > 0){
            foreach($reg_dates as $special_date){
                if(in_array($selection->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    $reg_date = $special_date->date;
                }
            }
        }

        if($reg_date ==  null){
            return redirect()->back()->with('error','Registration period has not been set');
        }
        $now = strtotime(date('Y-m-d'));
        $reg_date_time = strtotime($reg_date);
        $datediff = $reg_date_time - $now;

        $applicant = Applicant::with(['intake','campus','nextOfKin','country','region','district','ward','insurances','programLevel'])->find($request->get('applicant_id'));
        if(round($datediff / (60 * 60 * 24)) < 0 && round($datediff / (60 * 60 * 24)) < -7){
            return redirect()->back()->with('error','Applicant cannot be registered. Registration period is over');
        }

        if(empty($applicant->gender|| empty($applicant->disability_status_id))){
            return redirect()->back()->with('error','Sex of the applicant is required');
        }

        if(empty($applicant->disability_status_id)){
            return redirect()->back()->with('error','Disiability status of the applicant is required');
        }

        $applicant->results_check = $request->get('results_check')? 1 : 0;
        $applicant->insurance_check = $request->get('insurance_check')? 1 : 0;
        $applicant->personal_info_check = $request->get('personal_info_check')? 1 : 0;
        $applicant->medical_form_check = $request->get('medical_form_check')? 1 : 0;
        $applicant->registered_by_user_id = Auth::user()->id;
        $applicant->save();

        $studentship_status = ($applicant->has_postponed == 1)? StudentshipStatus::where('name','POSTPONED')->first() : StudentshipStatus::where('name','ACTIVE')->first();
        $academic_status = AcademicStatus::where('name','FRESHER')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        if(str_contains($semester->name,'2')){
            return redirect()->back()->with('error','Active semester must be set to first semester');
        }
        // $last_student = DB::table('students')->select(DB::raw('MAX(SUBSTRING(REVERSE(registration_number),1,7)) AS last_number'))->where('campus_program_id',$selection->campusProgram->id)->first();
        $last_student = DB::table('students')->select(DB::raw('MAX(REVERSE(SUBSTRING(REVERSE(registration_number),1,7))) AS last_number'))->where('campus_program_id',$selection->campusProgram->id)->first();

        //Student::where('campus_program_id',$selection->campusProgram->id)->max();
        if(!empty($last_student->last_number)){
        $code = sprintf('%04d', substr($last_student->last_number, 0, 4) + 1);

            // return $code;

        }else{
           $code = sprintf('%04d',1);
        }
        $year = substr($ac_year->begin_date,2,2);

        $prog_code = explode('.', $selection->campusProgram->code);

        $program_code = $prog_code[0].'.'.$prog_code[1];

        $stud_group = explode('.', $selection->campusProgram->code);

        if(str_contains($applicant->intake->name,'March')){

            if(str_contains($applicant->campus->name,'Kivukoni')){
				$program_code = $prog_code[0].'3.'.$prog_code[1];

                if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'3';

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'3';

                }

            } elseif (str_contains($applicant->campus->name,'Karume')) {

				$program_code = $prog_code[0].'3.'.$prog_code[1];

                // if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {

                //     if (str_contains($selection->campusProgram->program->name, 'Leadership') && str_contains($selection->campusProgram->program->name, 'Governance')) {

                //         $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                //     } elseif (str_contains($selection->campusProgram->program->name, 'Procurement') && str_contains($selection->campusProgram->program->name, 'Supply')) {

                //         $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                //     } else {

                //         $stud_group = substr($stud_group[0], 0, 2).'Z'.$stud_group[1];

                //     }

                // }else
                if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

					$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z3';

				} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

					$stud_group = 'C'.$stud_group[1].'Z3';

				}

            //    $program_code = $prog_code[0].'3.'.$prog_code[1];
            //    //$stud_group =  $applicant->program_level_id.$selection->campusProgram->id.$year;
            //    $stud_group =  $applicant->programLevel->code.'Z'.str_replace('.','',$selection->campusProgram->program->code);
            }  elseif (str_contains($applicant->campus->name,'Pemba')) {

                $program_code = $prog_code[0].'3.'.$prog_code[1];

                if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P3';

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'P3';

                }


            }

        }else{

            // september intake

            if(str_contains($applicant->campus->name,'Karume')){

                $program_code = $prog_code[0].'9.'.$prog_code[1];

                if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {
					$program_code = $prog_code[0].'.'.$prog_code[1];
                    if (str_contains($selection->campusProgram->program->name, 'Leadership') && str_contains($selection->campusProgram->program->name, 'Governance')) {

                        $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                    } elseif (str_contains($selection->campusProgram->program->name, 'Procurement') && str_contains($selection->campusProgram->program->name, 'Supply')) {

                        $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                    } else {

                        $stud_group =$stud_group[0].$stud_group[1];

                    }

                } else if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z9';

                } else if (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'Z9';

                }elseif(str_contains(strtolower($selection->campusProgram->program->name), 'master')){
                    $stud_group =$stud_group[0].$stud_group[1];

                }


            } elseif (str_contains($applicant->campus->name,'Kivukoni')) {
                $stud_group = $stud_group[0].$stud_group[1];
                if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {

                    if (str_contains(strtolower($selection->campusProgram->program->name), 'human') && str_contains(strtolower($selection->campusProgram->program->name), 'resource')) {

                        $stud_group = substr($stud_group[0], 0, 1).$stud_group[1];

                    }

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {
					$program_code = $prog_code[0].'9.'.$prog_code[1];
					$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'9';

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {
					$program_code = $prog_code[0].'9.'.$prog_code[1];
                    $stud_group = 'C'.$stud_group[1];

                }elseif(str_contains(strtolower($selection->campusProgram->program->name), 'master')){
                        $stud_group = substr($stud_group[0], 0, 1).$stud_group[1];
                    
                }

            } elseif (str_contains($applicant->campus->name,'Pemba')) {
				$program_code = $prog_code[0].'9.'.$prog_code[1];

                if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {
					$program_code = $prog_code[0].'.'.$prog_code[1];

                    $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'P';

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {
                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P9';

                } elseif (str_contains(strtolower($selection->campusProgram->program->name), 'basic') && str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {
                    $stud_group = 'C'.$stud_group[1].'P9';

                }

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
        $student->image = $applicant->passport_picture;
        $student->campus_program_id = $selection->campusProgram->id;
        $student->registration_number = 'MNMA/'.$program_code.'/'.$code.'/'.$year;
        $student->disability_status_id = $applicant->disability_status_id;
        $student->studentship_status_id = $studentship_status->id;
        $student->academic_status_id = $academic_status->id;

		$user = User::find($applicant->user_id);

		$student->user_id = $user->id;
        $student->save();

        $tuition_fee_loan = LoanAllocation::where('applicant_id',$applicant->id)->where('study_academic_year_id',$ac_year->id)
        ->where('campus_id',$staff->campus_id)->sum('tuition_fee');

        $loan_allocation = LoanAllocation::where('applicant_id',$applicant->id)->where('study_academic_year_id',$ac_year->id)
        ->where('campus_id',$staff->campus_id)->latest()->first();

		$invoices = Invoice::with('feeType')->where('payable_type','applicant')->where('payable_id',$applicant->id)->whereNotNull('gateway_payment_id')->get();

        $fee_payment_percent = $other_fee_payment_status = 0;
        if($tuition_fee_loan > 0){
            $usd_currency = Currency::where('code','USD')->first();
	        $program_fee =  ProgramFee::with('feeItem.feeType')
                                      ->where('study_academic_year_id',$ac_year->id)
                                      ->where('campus_program_id',$selection->campus_program_id)
                                      ->first();

            if(str_contains($applicant->nationality,'Tanzania')){
                $program_fee_amount = $program_fee->amount_in_tzs;
            }else{
                $program_fee_amount = round($program_fee->amount_in_usd * $usd_currency->factor);
            }

            if($tuition_fee_loan >= $program_fee_amount){
                $fee_payment_percent = 1;
            }

        }

		if($invoices){
			foreach($invoices as $invoice){
				if(str_contains($invoice->feeType->name,'Tuition Fee')){
					$paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
					$fee_payment_percent = $paid_amount/$invoice->amount;

					if($tuition_fee_loan > 0){
					   $fee_payment_percent = ($paid_amount+$tuition_fee_loan)/$program_fee_amount;
					}
				}

				if(str_contains($invoice->feeType->name,'Miscellaneous')){
					$paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
					$other_fee_payment_status = $paid_amount >= $invoice->amount? 1 : 0;
				}
			}
		}
		$payment_status = false;
		if($fee_payment_percent >= 0.6 && $other_fee_payment_status >= 1){
			$payment_status = true;
		}
		if($loan_allocation){

			if($applicant->has_postponed != 1){
				if($ac_year->nhif_enabled == 1){
					if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
						$registration = $reg;
					}else{
						$registration = new Registration;
					}

					$registration->study_academic_year_id = $ac_year->id;
					$registration->semester_id = $semester->id;
					$registration->student_id = $student->id;
					$registration->year_of_study = 1;
					$registration->registration_date = date('Y-m-d');
					$registration->registered_by_staff_id = $staff->id;
					$registration->status = $payment_status && $loan_allocation->has_signed == 1 && $applicant->insurance_check == 1? 'REGISTERED' : 'UNREGISTERED';
					$registration->save();
				}else{
					if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
						$registration = $reg;
					}else{
						$registration = new Registration;
					}

					$registration->study_academic_year_id = $ac_year->id;
					$registration->semester_id = $semester->id;
					$registration->student_id = $student->id;
					$registration->year_of_study = 1;
					$registration->registration_date = date('Y-m-d');
					$registration->registered_by_staff_id = $staff->id;
					$registration->status = $payment_status && $loan_allocation->has_signed == 1? 'REGISTERED' : 'UNREGISTERED';
					$registration->save();
				}
			}
		    $loan_allocation->registration_number = $student->registration_number;
		    $loan_allocation->student_id = $student->id;
		    $loan_allocation->save();
		}else{
			if($applicant->has_postponed != 1){
				if($ac_year->nhif_enabled == 1){
					if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
					  $registration = $reg;
					}else{
					  $registration = new Registration;
					}
					$registration->study_academic_year_id = $ac_year->id;
					$registration->semester_id = $semester->id;
					$registration->student_id = $student->id;
					$registration->year_of_study = 1;
					$registration->registration_date = date('Y-m-d');
					$registration->registered_by_staff_id = $staff->id;
					$registration->status = $payment_status && $applicant->insurance_check == 1? 'REGISTERED' : 'UNREGISTERED';
					$registration->save();
				}else{
					if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
					  $registration = $reg;
					}else{
					  $registration = new Registration;
					}
					$registration->study_academic_year_id = $ac_year->id;
					$registration->semester_id = $semester->id;
					$registration->student_id = $student->id;
					$registration->year_of_study = 1;
					$registration->registration_date = date('Y-m-d');
					$registration->registered_by_staff_id = $staff->id;
					$registration->status = $payment_status? 'REGISTERED' : 'UNREGISTERED';
					$registration->save();
				}
			}

		}

		$days = round($datediff / (60 * 60 * 24));

        if($days < 0 && $days > -7){
            $fee_amount = FeeAmount::whereHas('feeItem',function($query){$query->where('name','LIKE','%Late Registration%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->where('campus_id',$staff->campus_id)->first();

            $student = Student::with(['applicant.country'])->find($student->id);

            if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for late registration');
            }

            if(str_contains($student->applicant->nationality,'Tanzania')){
                $amount = $fee_amount->amount_in_tzs * $days * (-1);
                $currency = 'TZS';
            }else{
                $amount = $fee_amount->amount_in_usd * $days * (-1);
                $currency = 'USD';
            }

            $invoice = new Invoice;
            $invoice->reference_no = 'MNMA-LR-'.time();
            $invoice->amount = $amount;
            $invoice->actual_amount = $amount;
            $invoice->currency = $currency;
            $invoice->payable_id = $student->id;
            $invoice->applicable_id = $ac_year->id;
            $invoice->applicable_type = 'academic_year';
            $invoice->payable_type = 'student';
            $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
            $invoice->save();

            $payable = Invoice::find($invoice->id)->payable;
            $fee_type = $fee_amount->feeItem->feeType;

            $generated_by = 'SP';
            $approved_by = 'SP';
            $inst_id = Config::get('constants.SUBSPCODE');

            $email = $payable->email? $payable->email : 'application@mnma.ac.tz';

            $first_name = str_contains($payable->first_name,"'")? str_replace("'","",$payable->first_name) : $payable->first_name;
            $surname = str_contains($payable->surname,"'")? str_replace("'","",$payable->surname) : $payable->surname;

            $number_filter = preg_replace('/[^0-9]/','',$email);
            $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';

            $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_type->description,
                                    $fee_type->gfs_code,
                                    $fee_type->payment_option,
                                    $payable->id,
                                    $first_name.' '.$surname,
                                    $payable->phone,
                                    $payer_email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_type->duration,
                                    $invoice->currency);
        }

            $check_insurance = false;
            if(count($applicant->insurances) != 0){
                if($applicant->insurances[0]->verification_status != 'VERIFIED'){
                    $check_insurance = true;
                }
            }

            if($ac_year->nhif_enabled == 1){
                if($applicant->insurance_status == 0 || $check_insurance){
                    try{
                        $path = public_path().'/avatars/'.$student->image;
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

                        $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/StudentRegistration';
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

                        $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/SubmitCardApplications';
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
                        }catch(\Exception $e){
                            $record = new InsuranceRegistration;
                            $record->applicant_id = $applicant->id;
                            $record->student_id = $student->id;
                            $record->study_academic_year_id = $ac_year->id;
                            $record->is_success = 0;
                            $record->save();
                        }
                    }
            }

            // Angalia namna ya kutumia taarifa zilizokwisha chukuliwa mwanzo
            $tuition_invoice = Invoice::whereHas('feeType',function($query){
                $query->where('name','LIKE','%Tuition%');
            })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

            $misc_invoice = Invoice::whereHas('feeType',function($query){
                $query->where('name','LIKE','%Miscellaneous%');
            })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

            $usd_currency = Currency::where('code','USD')->first();

            $acpac = new ACPACService;
            $stud_name = $student->surname.', '.$student->first_name.' '.$student->middle_name;
            $stud_reg = substr($student->registration_number, 5);
            $stud_reg = str_replace('/', '', $stud_reg);

            $parts = explode('.', $stud_reg);
            if(str_contains($parts[0], 'BTC')){
                $stud_reg = 'C'.$parts[1];
            }else{
                $stud_reg = $parts[0].$parts[1];
            }
            $next_of_kin = $applicant->nextOfKin->surname.', '.$applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->middle_name;
            $gparts = explode('.', $program_code);

            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('B','CRDB','REC02','10','TF','MNMA002','TEST','INV002','100.0','B','10')");
            $next_of_kin_email = $applicant->nextOfKin->email? $applicant->nextOfKin->email : 'UNKNOWN';

            if ($tuition_invoice) {

                $acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,
                                        TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('".$stud_reg."','".$stud_group."','".$stud_name."','".$applicant->address."',
                                        '".$applicant->district->name."','".$applicant->ward->name."','".$applicant->street."','".$applicant->region->name."','".$applicant->country->name."',
                                        '".$applicant->address."','".$applicant->country->name."','".$next_of_kin."','".$applicant->phone."','".$applicant->nextOfKin->phone."','','STD','TSH',
                                        '".$applicant->email."','".$next_of_kin_email."')");
            }

        if ($tuition_invoice) {
            $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$tuition_invoice->control_no."',
            '".date('Y',strtotime($tuition_invoice->created_at))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','1','".$tuition_invoice->feeType->gl_code."',
            '".$tuition_invoice->feeType->name."','".$tuition_invoice->feeType->description."','".$tuition_invoice->amount."','0','".date('Ymd',strtotime(now()))."')");
        }

        if(str_contains($applicant->programLevel->name,'Bachelor')){
            $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                $query->where('name','LIKE','%TCU%');
            })->where('study_academic_year_id',$ac_year->id)->with(['feeItem.feeType'])->first();
        }else{
            $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                $query->where('name','LIKE','%NACTVET%');
            })->where('study_academic_year_id',$ac_year->id)->with(['feeItem.feeType'])->first();
        }

        $other_fees = FeeAmount::whereHas('feeItem',function($query){
                $query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->get();

        if(str_contains($applicant->nationality,'Tanzania')){
            $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
            '".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
            '".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."','".$quality_assurance_fee->feeItem->feeType->description."',
            '".$quality_assurance_fee->amount_in_tzs."','0','".date('Ymd',strtotime(now()))."')");

            foreach ($other_fees as $fee) {
                $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
                '".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$fee->feeItem->feeType->gl_code."',
                '".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."','".$fee->amount_in_tzs."','0','".date('Y',strtotime(now()))."')");
            }
        }else{
            $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
            '".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
            '".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."','".$quality_assurance_fee->feeItem->feeType->description."',
            '".($quality_assurance_fee->amount_in_usd*$usd_currency->factor)."','0','".date('Ymd',strtotime(now()))."')");

            foreach ($other_fees as $fee) {
                $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
                '".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$fee->feeItem->feeType->gl_code."',
                '".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."','".($fee->amount_in_usd*$usd_currency->factor)."','0','".date('Ymd',strtotime(now()))."')");
            }
        }



        if ($tuition_invoice) {
            $tuition_receipts = GatewayPayment::where('control_no',$tuition_invoice->control_no)->get();

            foreach($tuition_receipts as $receipt){
                if($receipt->psp_name == 'National Microfinance Bank'){
                    $bank_code = 619;
                    $bank_name = 'NMB';
                }else{
                    $bank_code = 615;
                    $bank_name = 'CRDB';
                }

                $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."',
                '".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$receipt->control_no."',
                '".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
            }
        }



        $misc_receipts = GatewayPayment::where('control_no',$misc_invoice->control_no)->get();

        foreach ($misc_receipts as $receipt) {
            if($receipt->psp_name == 'National Microfinance Bank'){
                $bank_code = 619;
                $bank_name = 'NMB';
            }else{
                $bank_code = 615;
                $bank_name = 'CRDB';
            }

            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."',
            '".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$misc_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$receipt->control_no."',
            '".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
        }

        $acpac->close();

        Invoice::whereHas('feeType',function($query){
               $query->where('name','LIKE','%Tuition%');
        })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)
          ->update(['payable_type'=>'student','payable_id'=>$student->id,'applicable_id'=>$ac_year->id,'applicable_type'=>'academic_year']);

        Invoice::whereHas('feeType',function($query){
               $query->where('name','LIKE','%Miscellaneous%');
        })->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)
          ->update(['payable_type'=>'student','payable_id'=>$student->id,'applicable_id'=>$ac_year->id,'applicable_type'=>'academic_year']);

		$transfered_status = false;

		if($fee_payment_percent >= 0.6 && $other_fee_payment_status == 1){
			try{
               $user = User::where('id', $applicant->user_id)->first();
               $user->email = $applicant->email;
               $user->save();
			   Mail::to($user)->send(new StudentAccountCreated($student, $selection->campusProgram->program->name,$ac_year->academicYear->year, $transfered_status));
			}catch(Exception $e){}
		}
        DB::commit();
        if($days < 0 && $days > -7){
          return redirect()->to('application/applicants-registration?application_window_id='.$applicant->application_window_id.'&program_level_id='.$applicant->program_level_id.'&registeredStudent=true')->with('error','Student successfully registered with registration number '.$student->registration_number.', but has a penalty of '.$amount.' '.$currency);
        }else{
          return redirect()->to('application/applicants-registration?application_window_id='.$applicant->application_window_id.'&program_level_id='.$applicant->program_level_id.'&registeredStudent=true')->with('message','Student registered successfully with registration number '.$student->registration_number);
        }
    }

    /**
     * Show failed insurance registrations
     */
    public function showFailedInsuranceRegistrations(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'records'=>InsuranceRegistration::with(['student:id,applicant_id,first_name,middle_name,surname,gender,phone,campus_program_id','applicant:id,index_number'])
                                           ->whereHas('applicant',function($query) use($staff){$query->select('id','index_number')->where('campus_id',$staff->campus_id);})
                                           ->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('is_success',0)->get(),
           'request'=>$request
        ];
        return view('dashboard.admission.failed-insurance-registrations',$data)->withTitle('Failed Insurance Registrations');
    }

    /**
     * Resubmit insurance registrations
     */
    public function resubmitInsuranceRegistrations(Request $request)
    { 
        ini_set('memory_limit', '-1');
        set_time_limit(120);
        $max_batch_no = InsuranceRegistration::where('study_academic_year_id',$request->study_academic_year_id)->max('batch_no');
        foreach($request->records as $ins){
                 try{
                     $rec = InsuranceRegistration::with(['student.campusProgram.program','applicant','studyAcademicYear.academicYear'])->findOrFail($ins);
                     $student = $rec->student;
                     $applicant = $rec->applicant;
                     $path = null;
                     if(!empty($student->image)){
                        $path = file_exists(public_path().'/avatars/'.$student->image)? public_path().'/avatars/'.$student->image : public_path().'/uploads/'.$student->image;
                     }else{
                        continue;
                     }

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
                          'ProgrammeOfStudy'=> $student->campusProgram->program->name,
                          'CourseDuration'=> $student->campusProgram->program->min_duration,
                          'MaritalStatus'=> "Single",
                          'DateJoiningEmployer'=> date('Y-m-d'),
                          'DateOfBirth'=> $applicant->birth_date,
                          'NationalID'=> $applicant->nin? $applicant->nin : '',
                          'Gender'=> $applicant->gender == 'M'? 'Male' : 'Female',
                          'PhotoImage'=>$base64
                      ];

                      $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/StudentRegistration';
                      $token = NHIFService::requestToken();

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

                    //   $applicants = $applicant;
                    //   $ac_year = $rec->studyAcademicYear->academicYear->year;
                      $data = [
                      'BatchNo'=>'8002217/'.$rec->studyAcademicYear->academicYear->year.'/'.sprintf("%03d",$max_batch_no + 1),
                      'Description'=>'Batch submitted on '.date('m d, Y'),
                      'CardApplications'=>[
                         array(
                          'CorrelationID'=>$applicant->index_number,
                            'MobileNo'=>'0'.substr($applicant->phone, 3),
                            'AcademicYear'=>$rec->studyAcademicYear->academicYear->year,
                            'YearOfStudy'=>1,
                            'CardNo'=>null,
                            'Category'=>1//$response->statusCode == 200? 1 : 2
                         )
                       ]
                     ];

                    $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/SubmitCardApplications';
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
                    $response1 = json_decode($response);
                    $StatusCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl_handle);

                    curl_close($curl_handle);

                        $record = InsuranceRegistration::find($ins);
                        $record->applicant_id = $applicant->id;
                        $record->student_id = $student->id;
                        $record->study_academic_year_id = $rec->studyAcademicYear->id;
                        $record->is_success = 1;
                        $record->batch_no = $max_batch_no + 1;
                        $record->save();
                        
                    }catch(\Exception $e){
                        // $record = InsuranceRegistration::find($ins);
                        // $record->applicant_id = $applicant->id;
                        // $record->student_id = $student->id;
                        // $record->study_academic_year_id = $rec->studyAcademicYear->id;
                        // $record->is_success = 0;
                        // $record->save();

                        return redirect()->back()->with('error','Something is wrong. Please check with the Administrator');
                    }
        }
        return redirect()->back()->with('message','Insurance registrations resubmited successfully');
    }

    /**
     * Selected applicant
     */
    public function applicantsRegistration(Request $request)
    {    $applicant_loan_status = null;
         $staff = User::find(Auth::user()->id)->staff;
         $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
         if(!$ac_year){
            return redirect()->back()->with('error','No active academic year');
         }
         $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status', 'ACTIVE')->whereYear('end_date',explode('/',$ac_year->academicYear->year)[0])->first();

         if(!$application_window){
             return redirect()->back()->with('error','No corresponding application window');
         }
         $applicants_loan_status = [];
         if ($request->get('program_level_id')) {

            $applicants = Applicant::doesntHave('student')->whereHas('selections',function($query) use($request){
                $query->where('status','SELECTED');
           })->with(['intake','selections' => function($request){$request->where('status','SELECTED');},'selections.campusProgram.program'])
            ->where('program_level_id', $request->get('program_level_id'))->where('application_window_id',$application_window->id)
            ->where(function($query){$query->where('confirmation_status', null)->orWhere('confirmation_status','!=','CANCELLED');})->where('status','ADMITTED')
            ->where(function($query){$query->where('tuition_payment_check',1)->orWhere('other_payment_check',1);})
            ->orderBy('tuition_payment_check','DESC')->orderBy('other_payment_check','DESC')->orderBy('documents_complete_status','DESC')->orderBy('updated_at','DESC')->get();

            if(count($applicants) == 0){
                $applicants = [];
                if(empty($request->get('registeredStudent'))){
                    return redirect('application/applicants-registration?application_window_id='.$application_window->id)->with('error','No applicant to register on this level');
                }
            }

            foreach($applicants as $applicant){
                // OLD CODE

                // $program_fee = ProgramFee::select('amount_in_tzs')->where('study_academic_year_id',$ac_year->id)
                // ->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();

                // $applicant_loan_status = LoanAllocation::select('applicant_id')->where('year_of_study',1)->where('applicant_id',$applicant->id)
                // ->where('study_academic_year_id',$ac_year->id)
                // ->where('campus_id',$application_window->campus_id)->where('tuition_fee','>=',$program_fee->amount_in_tzs)->first();

                // if($applicant_loan_status){
                //     $applicants_loan_status = [$applicant->id=>true];
                // }

                // NEW CODE

                $program_fee = ProgramFee::select('amount_in_tzs')->where('study_academic_year_id',$ac_year->id)
                ->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();

                $applicant_loan_status = LoanAllocation::select('index_number')->where('year_of_study',1)->where('index_number',$applicant->index_number)
                ->where('study_academic_year_id',$ac_year->id)
                ->where('tuition_fee','>=',$program_fee->amount_in_tzs)->first();

                if($applicant_loan_status){
                    $applicants_loan_status = [$applicant->id=>true];
                }

            }

         } else {
            $applicants = [];
         }

        //  if($request->get('query')){
        //     $applicants = Applicant::doesntHave('student')->whereHas('selections',function($query) use($request){
        //          $query->where('status','SELECTED');
        //     })->with(['intake','selections.campusProgram.program'])->where('campus_id',$staff->campus_id)->where(function($query) use($request){
        //            $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('index_number','LIKE','%'.$request->get('query').'%');
        //          })->where('application_window_id',$application_window->id)->where(function($query){
        //              $query->where('confirmation_status','!==','CANCELLED')->orWhere('confirmation_status','!==','TRANSFERED')->orWhereNull('confirmation_status');
        //            })->where(function($query){
        //              $query->where('admission_confirmation_status','!==','NOT CONFIRMED')->orWhereNull('admission_confirmation_status');
        //            })->where('status','ADMITTED')->get();
        //       if(count($applicants) == 0){
        //           return redirect()->back()->with('error','No applicant with searched name or index number or already registered');
        //       }
        //  }elseif($request->get('')){
        //     $applicants = Applicant::doesntHave('student')->whereDoesntHave('student')->whereHas('selections',function($query) use($request){
        //          $query->where('status','SELECTED');
        //     })->with(['intake','selections.campusProgram.program'])->where('index_number','LIKE','%'.$request->get('index_number').'%')->where('application_window_id',$application_window->id)->where(function($query){
        //              $query->where('confirmation_status','!==','CANCELLED')->orWhere('confirmation_status','!==','TRANSFERED')->orWhereNull('confirmation_status');
        //            })->where(function($query){
        //              $query->where('admission_confirmation_status','!==','NOT CONFIRMED')->orWhereNull('admission_confirmation_status');
        //            })->where('status','ADMITTED')->get();
        //     if(count($applicants) == 0){
        //           return redirect()->back()->with('error','No applicant with searched index number or already registered');
        //       }
        //  }else{
        //     $applicants = [];
        // //  }

         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'awards'=>Award::all(),
            'applicants'=>$applicants,
            'request'=>$request,
            'applicants_loan_status'=>$applicants_loan_status,
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
            'staff' => $staff,
            'campus_id' => $staff->campus_id,
            'attachments'=>AdmissionAttachment::with('campus')->where('campus_id', $staff->campus_id)->paginate(20),
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'campuses' => Campus::all(),
            'request'=>$request
         ];

         return view('dashboard.application.upload-attachments',$data)->withTitle('Upload Attachments');
    }

    /**
     * Show admission attachments
     */
    public function admissionPackage(Request $request)
    {
         $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->latest()->first();
         $student = Student::where('applicant_id', $applicant->id)->first();
         $award = Award::where('id', $applicant->program_level_id)->first();
         $admission_packages = AdmissionAttachment::where('campus_id',session('applicant_campus_id'))->get();
         $packages = [];

         foreach($admission_packages as $admission_package){
            if(in_array($award->name, unserialize($admission_package->applicable_levels))){
                $packages[] = $admission_package;
            }
         }

         if($applicant->confirmation_status == 'CANCELLED'){
            return redirect()->to('application/basic-information')->with('error','This action cannot be performed. Your admission has been cancelled');
            }

         $data = [
            'attachments'=>$packages,
            'applicant'=>$applicant,
            'request'=>$request
         ];

         if ($student) {
            return redirect()->back()->with('error', 'Unable to view page');
         } else {
            return view('dashboard.application.admission-package',$data)->withTitle('Admission Package');
         }
    }

    /**
     * Download admission letter
     */
    public function downloadAdmissionLetter(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
            'attachment'=>'required|mimes:pdf',
            'applicable_level'=>'required',
            'campus_id'=>'required'
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
            $attachment->applicable_levels = serialize($request->get('applicable_level'));
            $attachment->campus_id = $request->get('campus_id');
            $attachment->save();
        }

        return redirect()->back()->with('message','Attachment uploaded successfully');
    }

    /**
     * Download admission attachment
     */
    public function downloadAttachment(Request $request)
    {
        try{
           $attachment = AdmissionAttachment::find($request->get('id'));
           return response()->download(public_path().'/uploads/'.$attachment->file_name);
        }catch(\Exception $e){
            return redirect()->back()->with('error','Document could not be found');
        }
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

         $staff = User::find(Auth::user()->id)->staff;
        $applicants = [];
        if(Auth::user()->hasRole('admission-officer')){

            $applicants = Applicant::select('id','first_name','surname','email','campus_id','address','index_number','application_window_id','intake_id','nationality','region_id')
                            ->whereHas('selections',function($query)use($request){$query->where('status','SELECTED')->where('application_window_id',$request->get('application_window_id'));})
                            ->where('program_level_id',$request->get('program_level_id'))
                            ->where('status','SELECTED')
                            ->where('campus_id', $staff->campus_id)->where('application_window_id',$request->get('application_window_id'))
                            ->where(function($query){$query->where('multiple_admissions',0)->orWhere('multiple_admissions',null)->orWhere('confirmation_status','CONFIRMED');})
                            ->with([
                                'intake:id,name',
                                'selections'=>function($query){$query->select('id','status','campus_program_id','applicant_id')->where('status','SELECTED');},
                                'selections.campusProgram:id,program_id,campus_id',
                                'selections.campusProgram.program:id,name,award_id,min_duration',
                                'selections.campusProgram.program.award:id,name',
                                'campus:id,name',
                                'applicationWindow:id,end_date',
                                'region:id,name'
                                ])->get();

        }else{
            return redirect()->back()->with('error','Sorry, this task can only be done by a respective Admission Officer.');
        }

        if(count($applicants) == 0){
            return redirect()->back()->with('error','There is no applicant to admit at the moment');
        }
         $ac_year = date('Y',strtotime($applicants[0]->applicationWindow->end_date));
        $ac_year += 1;

        $study_academic_year = StudyAcademicYear::select('id','academic_year_id')->whereHas('academicYear',function($query) use($ac_year){$query->where('year','LIKE','%/'.$ac_year.'%');})
            ->with('academicYear:id,year')->first();

        if(!$study_academic_year){
            return redirect()->back()->with('error','Study academic year has not been created');
        }
       $orientation_date = null;
        $special_dates = SpecialDate::where('name','Orientation')
        ->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicants[0]->intake->name)->where('campus_id',$applicants[0]->campus_id)->get();

        $orientation_date = null;
        if(count($special_dates) == 0){
            return redirect()->back()->with('error','Orientation date has not been defined');
        }else{
            foreach($special_dates as $special_date){
                $specialDateFlag = false;
                if(!in_array($applicants[0]->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    $specialDateFlag = true;

                }else{
                    $orientation_date = $special_date->date;
                    break;
                }
            }
            if($specialDateFlag){
                return redirect()->back()->with('error','Orientation date for '.$applicants[0]->selections[0]->campusProgram->program->award->name.' has not been defined');
            }
        }

        // Checks for Masters
        if($request->get('program_level_id') == 5){
            $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
            return redirect()->back()->with('error','Medical insurance fee has not been defined');
            }

            $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

            if(!$students_union_fee){
            return redirect()->back()->with('error','Students union fee has not been defined');
            }

            $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
            return redirect()->back()->with('error','Caution money fee has not been defined');
            }

            $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%Master%')->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
            return redirect()->back()->with('error','Medical examination fee has not been defined');
            }

            $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
            return redirect()->back()->with('error','Registration fee has not been defined');
            }

            $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
            return redirect()->back()->with('error','ID card fee for new students has not been defined');
            }

            $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Late Registration%');})->first();

            if(!$late_registration_fee){
            return redirect()->back()->with('error','Late registration fee has not been defined');
            }

            $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergency%');})->first();

            if(!$welfare_emergence_fund){
            return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined");
            }

            $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                ->where('name','LIKE','%Master%')->where('name','LIKE','%TCU%');})->first();
            if(!$quality_assurance_fee){
                return redirect()->back()->with('error','TCU quality assurance fee has not been defined');
            }

        // Checks for Undergraduates
        }else{
            $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
            return redirect()->back()->with('error','Medical insurance fee has not been defined');
            }

            $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','NOT LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

            if(!$students_union_fee){
            return redirect()->back()->with('error','Students union fee has not been defined');
            }

            $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
            return redirect()->back()->with('error','Caution money fee has not been defined');
            }

            $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                    ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                    ->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
            return redirect()->back()->with('error','Medical examination fee has not been defined');
            }

            $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
            return redirect()->back()->with('error','Registration fee has not been defined');
            }

            $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                ->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
            return redirect()->back()->with('error','ID card fee for new students has not been defined');
            }

            $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Late Registration%');})->first();

            if(!$late_registration_fee){
            return redirect()->back()->with('error','Late registration fee has not been defined');
            }

            $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
            ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
            ->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergency%');})->first();

            if(!$welfare_emergence_fund){
            return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined");
            }

            if($request->get('program_level_id') == 4){
                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                        ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                        ->where('name','LIKE','%TCU%');})->first();
                $message = 'TCU quality assurance fee has not been defined';
             }else{
                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$staff->campus_id)
                                                        ->whereHas('feeItem',function($query) use($staff){$query->where('campus_id',$staff->campus_id)
                                                        ->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');})->first();
                $message = 'NACTVET qualtity assurance fee has not been defined';
             }

             if(!$quality_assurance_fee){
                 return redirect()->back()->with('error',$message);
             }
        }

        foreach($applicants as $applicant){
            $program_fee = ProgramFee::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('year_of_study',1)
                                    ->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

            if(!$program_fee){
                return redirect()->back()->with('error','Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name);
            }

            $teaching_practice = null;
            if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'bachelor') && str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'education')){
                $teaching_practice = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Teaching%')->where('name','LIKE','%Practice%'); })->first();

                if(!$teaching_practice){
                    return redirect()->back()->with('error','Teaching practice fee not defined');
                }
            }

            if ($teaching_practice) {
                $teaching_practice = str_contains($applicant->nationality, 'Tanzania') ? $teaching_practice->amount_in_tzs : $teaching_practice->amount_in_usd;
            }

                $practical_training_fee = null;
                if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'basic') || str_contains(strtolower($applicant->selections[0]->campusProgram->program->name),'diploma')){
                    $practical_training_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)
                    ->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Practical%')->where('name','LIKE','%Training%'); })->first();

                    if(!$practical_training_fee){
                        return redirect()->back()->with('error','Practical training fee not defined');
                    }
                }

                if ($practical_training_fee) {
                    $practical_training_fee = str_contains($applicant->nationality, 'Tanzania') ? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd;
                }

            $research_supervision_fee = null;
            if(str_contains(strtolower($applicant->selections[0]->campusProgram->program->award->name), 'master')){
                $research_supervision_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                            ->where('name','LIKE','%Master%')->where('name','LIKE','%Supervision%')->orWhere('name','LIKE','%Research Supervison%');})->first();

                if(!$research_supervision_fee){
                    return redirect()->back()->with('error','Research supervision fee has not been defined');
                }else{
                    $research_supervision_fee = str_contains($applicant->nationality, 'Tanzania') && $research_supervision_fee ? $research_supervision_fee->amount_in_tzs : $research_supervision_fee->amount_in_usd;

                }
            }
        }

        dispatch(new SendAdmissionLetterJob($request->get('program_level_id'), $request->get('application_window_id'), $request->get('reference_number')));

        return redirect()->back()->with('message','Admission package sent successfully');

    }


    /**
     * Show dashboard
     */
    public function showDashboard(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if(!Auth::user()->hasRole('administrator') || !Auth::user()->hasRole('arc') || (empty($request->get('application_window_id')) && (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')))){
           $application_window = ApplicationWindow::where('status','ACTIVE')->where('campus_id',$staff->campus_id)->first();
         }else{
           $application_window = ApplicationWindow::find($request->get('application_window_id'));
         }
         if(!$application_window){
             return redirect()->back()->with('error','No active application window has been set');
         }

         if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $progress_applications = Applicant::where('programs_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->count();
            $completed_applications = Applicant::where('programs_complete_status',1)->where('submission_complete_status',0)
                                        ->where('application_window_id',$request->get('application_window_id'))->count();
            $submitted_applications = Applicant::where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->count();
            $total_applications = Applicant::where('application_window_id',$request->get('application_window_id'))->count();
            $today_progress_applications = Applicant::where('programs_complete_status',0)
                                            ->where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_completed_applications = Applicant::where('programs_complete_status',1)->where('submission_complete_status',0)
                                            ->where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_submitted_applications = Applicant::where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))
                                            ->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_total_applications = Applicant::where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count();


          }else{
            $progress_applications = Applicant::where('programs_complete_status',0)
                                        ->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count();
            $completed_applications = Applicant::where('programs_complete_status',1)->where('submission_complete_status',0)
                                        ->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count();
            $submitted_applications = Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)
                                        ->where('campus_id',$application_window->campus_id)->count();
            $total_applications = Applicant::where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)->count();
            $today_progress_applications = Applicant::where('programs_complete_status',0)
                                            ->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)
                                            ->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_completed_applications = Applicant::where('programs_complete_status',1)->where('submission_complete_status',0)
                                            ->where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)
                                            ->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_submitted_applications = Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)
                                            ->where('campus_id',$application_window->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count();
            $today_total_applications = Applicant::where('application_window_id',$application_window->id)->where('campus_id',$application_window->campus_id)
                                        ->whereDate('created_at','=',now()->format('Y-m-d'))->count();

          }
         $data = [
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'campuses'=>Campus::all(),
            'progress_applications'=>$progress_applications,
            'completed_applications'=>$completed_applications,
            'submitted_applications'=>$submitted_applications,
            'total_applications'=>$total_applications,
            'today_progress_applications'=>$today_progress_applications,
            'today_completed_applications'=>$today_completed_applications,
            'today_submitted_applications'=>$today_submitted_applications,
            'today_total_applications'=>$today_total_applications,
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
	    $staff = User::find(Auth::user()->id)->staff;
        $applicant = Applicant::where('index_number',$request->get('index_number'))->where(function($query) use($staff){
			// $query->where('campus_id',$staff->campus_id)->orWhere('campus_id',0);
		})->latest()->first();
        if($request->get('index_number') && !$applicant){
            return redirect()->back()->with('error','Student does not exists');
        }
        $data = [
             'applicant'=>$applicant
        ];
        return view('dashboard.application.search-applicant',$data)->withTitle('Search For Applicant');
    }


    /**
     * Reset applicant's results
     */
    public function resetApplicantResults(Request $request)
    {   $staff = User::find(Auth::user()->id)->staff;
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        NectaResultDetail::where('applicant_id', $request->get('applicant_id'))->where('verified',1)->update(['applicant_id'=>0]);
        NectaResult::where('applicant_id', $request->get('applicant_id'))->update(['applicant_id'=>0]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $applicant = Applicant::find($request->get('applicant_id'));
        $applicant->results_complete_status = 0;
        $applicant->rank_points = null;
        $applicant->save();

        return redirect()->back()->with('message','Results reset successfully');
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

        $student_user_id = Student::select('user_id')->where('applicant_id',$applicant->id)->first();
        $user_id = !empty($student_user_id)? $student_user_id->user_id : $applicant->user_id;
        $user = User::find($user_id);
        $user->password = Hash::make($applicant->index_number);
        $user->save();

        return redirect()->to('application/application-dashboard')->with('message','Password reset successfully');
    }


        /**
     * Reset applicant's batch
     */
    public function resetApplicantApplicationBatch(Request $request)
    {
        $applicant = Applicant::find($request->get('applicant_id'));
        $current_batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
        $applicant->batch_id = $current_batch->id;
        $applicant->save();

        return redirect()->to('application/application-dashboard')->with('message','Application window batch reset successfully');
    }

    /**
     * Show insurance statuses
     */
    public function showInsuranceStatus(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('dean-of-students')) {

            $applicants = $request->get('query') ? Applicant::whereHas('insurances',function($query){
                $query->where('insurance_name','!=','NHIF');
                })->with('insurances')->where('campus_id', $staff->campus_id)->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->where(function($query) use($request){
                      $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%');
                })->get() : Applicant::whereHas('insurances',function($query){
                $query->where('insurance_name','!=','NHIF');
                })->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->get();

        } else {

            $applicants = $request->get('query') ? Applicant::whereHas('insurances',function($query){
                $query->where('insurance_name','!=','NHIF');
                })->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->where(function($query) use($request){
                      $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%');
                })->get() : Applicant::whereHas('insurances',function($query){
                $query->where('insurance_name','!=','NHIF');
                })->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->get();

        }

        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'awards'=>Award::all(),
           'applicants'=> $applicants,
           'request'=>$request
        ];
        return view('dashboard.application.insurance-statuses',$data)->withTitle('Applicant Insurance Status');
    }

    /**
     * Update insurance status
     */
    public function updateInsuranceStatus(Request $request)
    {
        $applicants = Applicant::whereHas('insurances',function($query){
            $query->where('insurance_name','!=','NHIF');
        })->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->get();

        foreach($applicants as $applicant){
            if($request->get('applicant_'.$applicant->id) == $applicant->id){
                $insurance = HealthInsurance::where('applicant_id',$applicant->id)->first();
                $insurance->verification_status = 'VERIFIED';
                $insurance->save();
            }else{
                $insurance = HealthInsurance::where('applicant_id',$applicant->id)->first();
                $insurance->verification_status = 'UNVERIFIED';
                $insurance->save();
            }
        }

        return redirect()->back()->with('message','Insurance status updated successfully');
    }


    public function previewInsuranceStatus(Request $request)
	{
        $applicant = User::find(Auth::user()->id)->applicants()->with(['insurances','programLevel'])->where('campus_id',session('applicant_campus_id'))->latest()->first();
        $student = Student::where('applicant_id', $applicant->id)->first();
		$insurance = HealthInsurance::where('applicant_id',$applicant->id)->first();

        $data = [
           'applicant'=>$applicant,
		   'insurance'=>$insurance
        ];

        if ($student) {
            return redirect()->back()->with('error', 'Unable to view page');
        } else {
            return view('dashboard.application.other-information',$data)->withTitle('Other Information');
        }
	}

	public function resetInsuranceStatus(Request $request)
	{
		HealthInsurance::where('applicant_id',$request->get('applicant_id'))->delete();
		$applicant = Applicant::where('id',$request->get('applicant_id'))->latest()->first();
		$applicant->insurance_status = null;
		$applicant->save();

        return redirect()->back()->with('message','Insurance status reset is successful');
	}
    /**
     * Update insurance status
     */
    public function updateHostelStatus(Request $request)
    {
        $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
			$query->where('hostel_status','!=',0)->orWhereNull('hostel_status');
		})->get();

        foreach($applicants as $applicant){
/*             if($request->get('app_'.$applicant->id) == $applicant->id){
				if($request->get('applicant_'.$applicant->id) == $applicant->id){
					$app = Applicant::find($applicant->id);
					$app->hostel_available_status = 1;
					$app->save();
				}
            }else{
                $app = Applicant::find($applicant->id);
                $app->hostel_available_status = 0;
                $app->save();
            } */
            if($request->get('app_'.$applicant->id) == $applicant->id){
				if($request->get('applicant_'.$applicant->id) == $applicant->id){
                    if($applicant->hostel_available_status == 1){
                        $app = Applicant::find($applicant->id);
                        $app->hostel_available_status = 0;
                        $app->save();
                    }else{
                        $app = Applicant::find($applicant->id);
                        $app->hostel_available_status = 1;
                        $app->save();
                    }
				}
            }elseif($request->get('applicant_'.$applicant->id) == $applicant->id){
                if($applicant->hostel_available_status == 1){
                    $app = Applicant::find($applicant->id);
                    $app->hostel_available_status = 0;
                    $app->save();
                }else{
                    $app = Applicant::find($applicant->id);
                    $app->hostel_available_status = 1;
                    $app->save();
                }
            }
        }

        return redirect()->back()->with('message','Insurance status updated successfully');
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

        $list = Applicant::whereHas('insurances',function($query){
            $query->where('insurance_name','!=','NHIF');
        })->with('insurances')->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->whereNotNull('insurance_status')->get();

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

        if (Auth::user()->hasRole('dean-of-students')) {
            $applicants = $request->get('query')? Applicant::whereHas('selections',function($query){
                $query->where('status','SELECTED')->where('campus_id', $staff->campus_id);
            })->with(['selections'=>function($query){
                $query->where('status','SELECTED');
            },'selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
             $query->where('hostel_status',1)->orWhere('hostel_status',2)->orWhere('hostel_status',3);
         })->where(function($query) use($request){
                   $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%');
             })->get() : Applicant::whereHas('selections',function($query){
                $query->where('status','SELECTED');
            })->with(['selections'=>function($query){
                $query->where('status','SELECTED');
            },'selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
             $query->where('hostel_status',1)->orWhere('hostel_status',2)->orWhere('hostel_status',3);
         })->get();

        } else {

            $applicants = $request->get('query')? Applicant::whereHas('selections',function($query){
                $query->where('status','SELECTED');
            })->with(['selections'=>function($query){
                $query->where('status','SELECTED');
            },'selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
             $query->where('hostel_status',1)->orWhere('hostel_status',2)->orWhere('hostel_status',3);
         })->where(function($query) use($request){
                   $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%');
             })->get() : Applicant::whereHas('selections',function($query){
                $query->where('status','SELECTED');
            })->with(['selections'=>function($query){
                $query->where('status','SELECTED');
            },'selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where(function($query){
             $query->where('hostel_status',1)->orWhere('hostel_status',2)->orWhere('hostel_status',3);
         })->get();
        }

        $data = [
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'awards'=>Award::all(),
           'applicants'=> $applicants,
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

        $list = Applicant::whereHas('selections',function($query){
               $query->where('status','SELECTED');
           })->with(['selections'=>function($query){
               $query->where('status','SELECTED');
           },'selections.campusProgram.program'])->where('application_window_id',$request->get('application_window_id'))->where('program_level_id',$request->get('program_level_id'))->where('hostel_status',1)->get();

        $callback = function() use ($list)
              {

                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['Index Number','First Name','Middle Name','Surname','Gender','Programme','Category','Status']);
                  foreach ($list as $row) {
				      if($row->hostel_available_status === 1){
                             $status =   'Allocated';
					  }elseif($row->hostel_available_status === 0){
								$status =    'Not Allocated';
					  }else{
								$status =	'Pending';
					  }

					  if($row->hostel_status === 1){
							 $category =  'On Campus';
					  }elseif($row->hostel_status === 2){
							   $category =     'Off Campus';
					  }elseif($row->hostel_status == 3){
							   $category =     'Any';
					  }else{
								$category =    'None';
					  }
                      fputcsv($file_handle, [$row->index_number,$row->first_name,$row->middle_name,$row->surname,$row->gender == 'M'? 'Male' : 'Female', $row->selections[0]->campusProgram->program->name,$category,$status]);
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
        $staff = User::find(Auth::user()->id)->staff;
        $batch_id = 0;

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))->where('program_level_id',4)->latest()->first();
        //$tcu_submission_status = ApplicantProgramSelection::where('batch_id',$batch->id)->where('status','APPROVING')->first();

        if($batch->batch_no > 1){

                    if(Applicant::whereHas('selections',function($query) use($request, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                        ->where('application_window_id',$request->get('application_window_id'))
                        ->where('batch_id',$batch->id);})
                        ->where('application_window_id', $request->get('application_window_id'))
                        ->where('program_level_id',$request->get('award_id'))->where('batch_id',$batch->id)->count() >  0){
                                $batch_id = $batch->id;

                            }else{
                            $previous_batch = null;

                            $previous_batch = ApplicationBatch::where('application_window_id',$request->get('application_window_id'))->where('program_level_id',4)->where('batch_no', $batch->batch_no - 1)->first();
                            $batch_id = $previous_batch->id;

            }
        }else{
            $batch_id = $batch->id;

        }

        if(ApplicantSubmissionLog::where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))
                                 ->where('batch_id',$batch_id)->count() == 0){
            return redirect()->back()->with('error','Applicants were not sent to TCU');
        }

        $tcu_username = $tcu_token = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/applicants/getStatus';

        $campus_program = CampusProgram::find($request->get('campus_program_id'));

        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <ProgrammeCode>'.$campus_program->regulator_code.'</ProgrammeCode>
                        </RequestParameters>
                        </Request>
                        ';

        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);
        $no_of_applicants = 0;
        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            foreach($array['Response']['ResponseParameters']['Applicant'] as $data){
                $applicant = Applicant::where('index_number',$data['f4indexno'])->where('application_window_id', $request->get('application_window_id'))
										->where('program_level_id',$request->get('program_level_id'))->latest()->first();
                if($applicant){
                   $applicant->multiple_admissions = $data['AdmissionStatusCode'] == 225 ? 1 : 0;
                   $applicant->save();

                   ApplicantProgramSelection::where('applicant_id',$applicant->id)->whereIn('status',['APPROVING','PENDING'])->update(['status'=>'SELECTED']);
                   $no_of_applicants++;
                }
            }
        }else{
            return redirect()->back()->with('error','No applicants retrieved from TCU');
        }

        ApplicantProgramSelection::whereHas('applicant',function($query) use($request){
             $query->where('program_level_id',$request->get('program_level_id'))->where('application_window_id',$request->get('application_window_id'))->where('status','SUBMITTED');
        })->where('batch_id', $batch_id)->where('campus_program_id',$request->get('campus_program_id'))->where('status','APPROVING')->update(['status'=>'PENDING']);

        return redirect()->back()->with('message',$no_of_applicants.' applicants retrieved successfully from TCU');
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

        $staff = User::find(Auth::user()->id)->staff;
        $tcu_username = $tcu_token = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/applicants/getConfirmed';
        $campus_program = CampusProgram::find($request->get('campus_program_id'));
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
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
            $applicant = Applicant::where('index_number',$data['f4indexno'])->where('campus_id',$staff->campus_id)->where('program_level_id',4)->latest()->first();
            if($applicant){
               if($data['ConfirmationStatusCode'] == 233)
                    $applicant->admission_confirmation_status = 'CONFIRMED';
               elseif($data['ConfirmationStatusCode'] == 234){
                    $applicant->admission_confirmation_status = 'CONFIRMED TO OTHER';
               }elseif($data['ConfirmationStatusCode'] == 236){
                    $applicant->admission_confirmation_status = 'NOT CONFIRMED ANYWHERE';
               }
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
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->latest()->first();

        $regulator_status = Applicant::where('program_level_id', $applicant->program_level_id)
        ->whereHas('selections', function ($query) {$query->where('status', 'SELECTED')
        ->orWhere('status', 'PENDING');})
        ->where('application_window_id', $applicant->application_window_id)
        ->where('intake_id', $applicant->intake_id)
        ->count();

        $regulator_selection = $regulator_status != 0 ? true : false;

        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'regulator_selection'=>$regulator_selection
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

        $tcu_username = $tcu_token = null;
        if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($applicant->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/admission/confirm';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ConfirmationCode>'.$request->get('confirmation_code').'</ConfirmationCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 212 || $array['Response']['ResponseParameters']['StatusCode'] == 214){
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

        $tcu_username = $tcu_token = null;
        if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($applicant->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/admission/unconfirm';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ConfirmationCode>'.$request->get('confirmation_code').'</ConfirmationCode>
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 218){
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

        $tcu_username = $tcu_token = null;
        if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($applicant->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/admission/requestConfirmationCode';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
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
            // if($array['Response']['ResponseParameters']['StatusCode'] == 215){
            //     $applicant->multiple_admissions = 0;
            //     $applicant->save();

            //     return redirect()->back()->with('message','Admission status changed successfully');
            // }else{
                return redirect()->back()->with('error','Unable to request confirmation code. '.$array['Response']['ResponseParameters']['StatusDescription']);
            //}
        }
    }

    /**
     * Request confirmation code
     */
    public function cancelAdmission(Request $request)
    {
        $applicant = Applicant::find($request->get('applicant_id'));

        $tcu_username = $tcu_token = null;
        if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($applicant->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $url = 'http://api.tcu.go.tz/admission/reject';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        </RequestParameters>
                        </Request>';

        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 200){
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

        $tcu_username = $tcu_token = null;
        if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($applicant->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        $admitted_program = null;
        foreach($applicant->selections as $selection){
            if($selection->status == 'SELECTED'){
                $admitted_program = $selection->campusProgram->regulator_code;
            }
        }

        $url = 'http://api.tcu.go.tz/admission/restoreCancelledAdmission';
        $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                        <Request>
                        <UsernameToken>
                        <Username>'.$tcu_username.'</Username>
                        <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                        <f4indexno>'.$applicant->index_number.'</f4indexno>
                        <ProgrammeCode>'.$admitted_program.'</ ProgrammeCode >
                        </RequestParameters>
                        </Request>';
        $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        $json = json_encode($xml_response);
        $array = json_decode($json,TRUE);

        if($array['Response']['ResponseParameters']['StatusCode'] == 230){
            $applicant->confirmation_status = 'RESTORED';
            $applicant->save();
            return redirect()->back()->with('message','Admission restored successfully');
        }else{
            return redirect()->back()->with('error','Unable to restore admission. '.$array['Response']['ResponseParameters']['StatusDescription']);
        }
    }

	/**
	 * Display internal transfers to admin
	 */
	 public function showInternalTransfersAdmin(Request $request)
	 {
        $staff = User::find(Auth::user()->id)->staff;
		 $data = [
		     'transfers'=>InternalTransfer::whereHas('student.applicant',function($query) use($staff){
                  $query->where('campus_id',$staff->campus_id);
            })->with(['student.applicant','previousProgram.program','currentProgram.program','user.staff'])->latest()->paginate(20)
		 ];
		 return view('dashboard.application.internal-transfers',$data)->withTitle('Internal Transfer');
	 }

    /**
     * Show internal transfer
     */
    public function showInternalTransfer(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $student = Student::whereHas('applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
                          ->whereHas('academicStatus',function($query){$query->where('name','FRESHER');})
                          ->whereHas('studentshipStatus', function($query){$query->where('name', 'ACTIVE');})
		->with(['applicant.selections'=>function($query){
              $query->where('status','SELECTED');
        },'applicant.selections.campusProgram.program'])->where('registration_number',$request->get('registration_number'))->first();

		$has_student_role = null;

		if($student){
			//(Auth::user()->hasRole('dean-of-students'))
			$has_student_role = User::find($student->user_id)->hasRole('student');
			if(!$has_student_role){
			   return redirect()->back()->with('error','Student account has not been activated');
			}

			if(InternalTransfer::where('student_id',$student->id)->count() != 0){
			   return redirect()->back()->with('error','Student already transfered');
		    }
		}

        if(!$student && $request->get('registration_number')){
            return redirect()->back()->with('error','Student  does not either belong to this campus or qualify for transfer');
        }
        // $programs = [];
        // $campus_programs = $student? CampusProgram::whereHas('program',function($query) use($student){
        //          $query->where('award_id',$student->applicant->program_level_id)->where('campus_id',$student->applicant->campus_id);
        //     })->with('program')->get() : [];

        if($student){

        $applicant = Student::find($student->id)->applicant()->with(['selections.campusProgram.program','selections'=>function($query){
                $query->where('status','SELECTED')->orderBy('order','asc');
            },'nectaResultDetails'=>function($query){
                 $query->where('verified',1);
            },'nacteResultDetails'=>function($query){
                 $query->where('verified',1);
            },'outResultDetails'=>function($query){
                 $query->where('verified',1);
            },'selections.campusProgram.campus','nectaResultDetails.results','nacteResultDetails.results','outResultDetails.results','programLevel','applicationWindow'])->first();

        $window = $applicant->applicationWindow;



        $parts=explode("/",$applicant->index_number);
        //create format from returned form four index format

        if(str_contains($applicant->index_number,'EQ')){
            $exam_year = explode('/',$applicant->index_number)[1];
            $index_no = $parts[0];
        }else{
            $exam_year = explode('/', $applicant->index_number)[2];
            $index_no = $parts[0]."-".$parts[1];
        }

        if($applicant->is_tamisemi == 1){
            $results_status = NectaResultDetail::where('applicant_id', $applicant->id)->where('exam_id', 1)
                                                ->where('verified', 1)->count();
            if($results_status == 0){
                if($det = NectaResultDetail::where('index_number', $applicant->index_number)->where('exam_id', 1)
                ->where('verified', 1)->first()){
                    $detail = new NectaResultDetail;
                    $detail->center_name = $det->center_name;
                    $detail->center_number = $det->center_number;
                    $detail->first_name = $det->first_name;
                    $detail->middle_name = $det->middle_name;
                    $detail->last_name = $det->last_name;
                    $detail->sex = $det->sex;
                    $detail->index_number = $det->index_number; //json_decode($response)->particulars->index_number;
                    $detail->division = $det->division;
                    $detail->points = $det->points;
                    $detail->exam_id = 1;
                    $detail->applicant_id = $applicant->id;
                    $detail->verified = 1;
                    $detail->save();

                    $result = NectaResult::where('necta_result_detail_id', $det->id)->get();
                    foreach($result as $res){
                        $newRes = new Nectaresult;
                        $newRes->subject_name = $res->subject_name;
                        $newRes->subject_code = $res->subject_code;
                        $newRes->grade = $res->grade;
                        $newRes->applicant_id = $applicant->id;
                        $newRes->necta_result_detail_id = $detail->id;
                        $newRes->save();
                    }

                }else{
                    $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                        'api_key'=>config('constants.NECTA_API_KEY'),
                        'exam_year'=>$exam_year,
                        'index_number'=>$index_no,
                        'exam_id'=>'1'
                    ]);

                    if(!isset(json_decode($response)->results)){
                        return redirect()->back()->with('error','Invalid Index number or year');
                    }

                    $detail = new NectaResultDetail;
                    $detail->center_name = json_decode($response)->particulars->center_name;
                    $detail->center_number = json_decode($response)->particulars->center_number;
                    $detail->first_name = json_decode($response)->particulars->first_name;
                    $detail->middle_name = json_decode($response)->particulars->middle_name;
                    $detail->last_name = json_decode($response)->particulars->last_name;
                    $detail->sex = json_decode($response)->particulars->sex;
                    $detail->index_number = $applicant->index_number; //json_decode($response)->particulars->index_number;
                    $detail->division = json_decode($response)->results->division;
                    $detail->points = json_decode($response)->results->points;
                    $detail->exam_id = 1;
                    $detail->applicant_id = $applicant->id;
                    $detail->verified = 1;
                    $detail->save();

                    foreach(json_decode($response)->subjects as $subject){
                        $res = new NectaResult;
                        $res->subject_name = $subject->subject_name;
                        $res->subject_code = $subject->subject_code;
                        $res->grade = $subject->grade;
                        $res->applicant_id = $applicant->id;
                        $res->necta_result_detail_id = $detail->id;
                        $res->save();
                    }
                }
            }

            sleep(5);
        }

        $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                   $query->where('award_id',$applicant->program_level_id);
           })->whereHas('entryRequirements',function($query) use($window){
                   $query->where('application_window_id',$window->id);
           })->with(['program','campus','entryRequirements'=>function($query) use($window){
                $query->where('application_window_id',$window->id);
           }])->where('campus_id',$applicant->campus_id)->get() : [];


        $award = $applicant->programLevel;
        $programs = [];

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        //$selected_program = array();

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
                $diploma_principle_pass_grade = 'E';
                $principle_pass_grade = 'E';
                $subsidiary_pass_grade = 'S';
            }else{
                $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
                $diploma_principle_pass_grade = 'D';
                $principle_pass_grade = 'D';
                $subsidiary_pass_grade = 'E';
            }

           // $selected_program[$applicant->id] = false;
           $subject_count = 0;
           
            foreach($campus_programs as $program){

                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                // Certificate
                if(str_contains($award->name,'Certificate')){
                    $o_level_pass_count = 0;
                    $o_level_other_pass_count = 0;
                    $o_level_must_pass_count = 0;
                    foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                        if($detail->exam_id == 1){
                        $other_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                            $applicant->rank_points += $o_level_grades[$result->grade];
                            $subject_count += 1;

                                // lupi changed
                                if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                }else{
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                    }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
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
                        if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects){

                            $programs[] = $program;
                        }
                    }
                }

                // Diploma
                if(str_contains($award->name,'Diploma')){

                    $o_level_pass_count = 0;
                    $o_level_other_pass_count = 0;
                    $o_level_must_pass_count = 0;
                    $a_level_principle_pass_count = 0;
                    $a_level_subsidiary_pass_count = 0;
                    $diploma_major_pass_count = 0;

                    foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                        if($detail->exam_id == 1){
                        $other_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $other_must_subject_ready = true;
                                    }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                    }else{
                                        if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                            $o_level_other_pass_count += 1;
                                        }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                            $o_level_other_pass_count += 1;
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

                        }elseif($detail->exam_id === 2){
                        $other_advance_must_subject_ready = false;
                        $other_advance_subsidiary_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){
                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;

                                // lupi changed this to properly check principle_pass_count | Tested
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_principle_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
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
                                }else{
                                    $a_level_principle_pass_count += 1;
                                }
                                }

                            if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

                                // lupi changed to properly count subsidiary points | tested
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
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
                                }else{
                                    $a_level_subsidiary_pass_count += 1;
                                }
                                }
                            }
                        }
                    }

                    if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && (($a_level_principle_pass_count > 0
                    && ($a_level_subsidiary_pass_count + $a_level_principle_pass_count >= 2)) || $a_level_principle_pass_count >= 2)){
                        $programs[] = $program;
                    }
                    $has_btc = false;

                    if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level == 4){

                        foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                            foreach($applicant->nacteResultDetails as $det){
                                if(str_contains(strtolower($det->programme),strtolower($sub)) && str_contains(strtolower($det->programme),'basic')){
                                    $has_btc = true;
                                }
                            }
                        }
                    } elseif (unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level == 5) {

                    }else{       // lupi added the else part to determine btc status when equivalent majors have not been defined
                        foreach($applicant->nacteResultDetails as $det){
                                if(str_contains(strtolower($det->programme),'basic')){
                                    $has_btc = true;
                                }
                            }
                    }

                    if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_btc){
                        $programs[] = $program;
                    } elseif (($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $applicant->veta_status == 1) {
                        $programs[] = $program;
                    }
                }

                // Bachelor
                if(str_contains($award->name,'Bachelor')){

                    $o_level_pass_count = 0;
                    $o_level_other_pass_count = 0;
                    $o_level_must_pass_count = 0;
                    $a_level_principle_pass_count = 0;
                    $a_level_principle_pass_points = 0;
                    $a_level_subsidiary_pass_count = 0;
                    $a_level_out_principle_pass_count = 0;
                    $a_level_out_principle_pass_points = 0;
                    $a_level_out_subsidiary_pass_count = 0;
                    $diploma_pass_count = 0;

                    foreach ($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 1){
                        $other_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                }else{
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                    }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
                                    }
                                }
                            }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;

                                }
                            }else{
                                $o_level_pass_count += 1;
                            }
                                // lupi changed
                                if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                }else{
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                    }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
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
                        $other_advance_must_subject_ready = false;
                        $other_advance_subsidiary_ready = false;
                        $other_out_advance_must_subject_ready = false;
                        $other_out_advance_subsidiary_ready = false;
                        foreach ($detail->results as $key => $result) {

                            if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_principle_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
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
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                }
                                }else{
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                }
                            }
                            if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce the sample


                            // lupi changed to properly count subsidiary points | tested
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
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
                                }else{
                                $a_level_subsidiary_pass_count += 1;
                                }
                            }

                            if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){

                                $applicant->rank_points += $a_level_grades[$result->grade];
                                $subject_count += 1;
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                        $a_level_out_principle_pass_count += 1;
                                        $other_out_advance_must_subject_ready = true;
                                        $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                    }
                                }
                                }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                }
                                }else{
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                }
                            }
                            if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce sample size

                            // lupi changed to properly count subsidiary points | tested
                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
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
                                }else{
                                $a_level_subsidiary_pass_count += 1;
                                }
                            }
                        }
                        }
                    }
//return $o_level_pass_count+$o_level_other_pass_count.' >= '.$program->entryRequirements[0]->pass_subjects.'; '.$a_level_principle_pass_count.' >= 2'.$a_level_principle_pass_points.' >= '.$program->entryRequirements[0]->principle_pass_points;

                    if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                        $programs[] = $program;
                    }
                    }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                        $programs[] = $program;

                    } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                        $programs[] = $program;
                    }

                    $has_major = false;
                    $equivalent_must_subjects_count = 0;
                    $nacte_gpa = null;
                    $out_gpa = null;
                    $has_nacte_results = false;

                    foreach($applicant->nacteResultDetails as $detail){
                            if(count($detail->results) == 0){
                            $has_nacte_results = true;
                            }
                        $nacte_gpa = $detail->diploma_gpa;
                    }

                    if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_nacte_results && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){

                        $programs[] = $program;
                    }

                    if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){

                        foreach($applicant->nacteResultDetails as $detail){
                            foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                                if(str_contains(strtolower($detail->programme),strtolower($sub))){

                                    $has_major = true;
                                }
                            }
                            $nacte_gpa = $detail->diploma_gpa;
                        }
                        if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                            foreach($applicant->nacteResultDetails as $detail){
                                foreach($detail->results as $result){
                                    foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                        if(str_contains(strtolower($result->subject),strtolower($sub))){
                                            $equivalent_must_subjects_count += 1;
                                        }
                                    }
                                }
                            }
                        }
                    }else{
                        if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                            foreach($applicant->nacteResultDetails as $detail){
                                foreach($detail->results as $result){
                                    foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                        if(str_contains(strtolower($result->subject),strtolower($sub))){
                                            $equivalent_must_subjects_count += 1;
                                        }
                                    }
                                }
                                $nacte_gpa = $detail->diploma_gpa;
                            }
                        }
                    }
                    if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){
                        if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_major && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){

                            $programs[] = $program;
                        }
                    }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                        if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

                            $programs[] = $program;
                        }
                    }

                    $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects); //['OFC 017','OFP 018','OFP 020'];
                    $out_pass_subjects_count = 0;

                    foreach($applicant->outResultDetails as $detail){
                        foreach($detail->results as $key => $result){
                            if(!Util::arrayIsContainedInKey($result->code, $exclude_out_subjects_codes)){
                                if($out_grades[$result->grade] >= $out_grades['C']){
                                    $out_pass_subjects_count += 1;
                                }
                            }
                        }
                        $out_gpa = $detail->gpa;

                    }

                    if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 && $a_level_out_principle_pass_count >= 1){
                            $programs[] = $program;
                    }

                    if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                        if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){
                                $programs[] = $program;
                        }
                    }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                        if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){
                                $programs[] = $program;
                        }
                    }

                    if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){
                            $programs[] = $program;
                    }
                }
                if($subject_count != 0){
                    $app = Applicant::find($applicant->id);
                    $app->rank_points = $applicant->rank_points / $subject_count;
                    $app->save();
                }
            }

            if(count($programs) == 0){
                return redirect()->back()->with('error','The student does not qualify to any programme');                   
            }elseif(count($programs) == 1 && $programs[0]->id == $student->campus_program_id){
                //if($programs->id == $student->campus_program_id){
                    return redirect()->back()->with('error','The student does not qualify to any other programme');
                //}
            }
		}
        $student = Student::whereHas('applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
		->whereHas('academicStatus',function($query){$query->where('name','FRESHER');})
		->whereHas('studentshipStatus', function($query){$query->where('name', 'ACTIVE');})
		->with(['applicant.selections'=>function($query){
              $query->where('status','SELECTED');
        },'applicant.selections.campusProgram.program'])->where('registration_number',$request->get('registration_number'))->first();

        $data = [
            'student'=>$student,
            'admitted_program_id'=>$student? $student->applicant->selections[0]->campusProgram->id : null,
            'campus_programs'=>$student? $programs : [],
            'transfers'=>InternalTransfer::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
                                    ->with(['student.applicant','previousProgram.program','currentProgram.program','user.staff'])->latest()->paginate(20),
            'staff'=>$staff,
            'transfered_campus_programs'=>InternalTransfer::distinct()
                                                          ->select('current_campus_program_id')
                                                          ->whereHas('student.applicant',function($query) use($staff){$query
                                                                                        ->where('campus_id',$staff->campus_id);})
                                                          ->with('currentProgram.program')
                                                          ->where('status','SUBMITTED')
                                                          ->get()
        ];

        return view('dashboard.registration.submit-internal-transfer',$data)->withTitle('Internal Transfer');
    }

	/**
	 * Register external transfer
	 */
	 public function registerExternalTransfer(Request $request)
	 {
		 $staff = User::find(Auth::user()->id)->staff;
		 $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','ACTIVE')->first();

		 $award = Award::where('name','LIKE','%Degree%')->first();
         if(!$award){

         }

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id',$application_window->id)
         ->where('program_level_id',$award->id)->latest()->first();

        $batch_id = null;
        if($batch->batch_no > 1){
            if(Applicant::whereHas('selections',function($query) use($application_window, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                        ->where('application_window_id',$application_window->id)
                        ->where('batch_id',$batch->id);})
                        ->where('application_window_id', $application_window->id)
                        ->where('program_level_id',$award->id)->where('batch_id',$batch->id)->count() >  0){
                $batch_id = $batch->id;

            }else{

                $previous_batch = null;
                if($batch->batch_no > 1){
                    $previous_batch = ApplicationBatch::where('application_window_id',$application_window->id)->where('program_level_id',$award->id)
                                            ->where('batch_no', $batch->batch_no - 1)->first();
                    $batch_id = $previous_batch->id;
                }
            }
        }else{
            $batch_id = $batch->id;
        }

        $appl = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$staff->campus_id)
                         ->where(function($query){$query->where('status','ADMITTED')->where('multiple_admissions',1)
                         ->where(function($query){$query->whereNull('admission_confirmation_status')->orWhere('admission_confirmation_status','NOT LIKE','%OTHER%');})
                         ->orWhere('is_transfered',1);})
                         ->where('application_window_id',$application_window->id)->count();

        if($appl >= 1){
            return redirect()->to('registration/external-transfer')->with('error','Applicant already admitted or transfered to this campus.');
        }   

        //Need to compare with year of application
        $appl = Applicant::where('index_number',$request->get('index_number'))->where('campus_id','!=',$staff->campus_id)->where('is_transfered',1)->first();

        if($appl){
            return redirect()->to('registration/external-transfer')->with('error','Admission has already been used for transfer in another campus.');
        }                 
        DB::beginTransaction();

		if($app = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$staff->campus_id)
                           ->where('application_window_id',$application_window->id)->latest()->first()){
            //Applicant::where('index_number',$request->get('index_number'))->where('campus_id','!=',$staff->campus_id)->update(['status'=>null]);
            $applicant = $app;
            $applicant->is_tcu_verified = null;
            $applicant->is_transfered = 1;
            $applicant->programs_complete_status = 0;
            $applicant->submission_complete_status = 0;
            $applicant->payment_complete_status = 0;
            $applicant->batch_id = $batch_id;
            $applicant->campus_id = $staff->campus_id;
            $applicant->entry_mode = $request->get('entry_mode');
            $applicant->program_level_id = $award->id;
            $applicant->intake_id = $application_window->intake_id;
            $applicant->application_window_id = $application_window->id;
            $applicant->status = null;

            $applicant->save();

            $user = User::where('username',$request->get('index_number'))->first();
            $user->password = Hash::make($request->get('index_number'));
            $user->save();

            ApplicantProgramSelection::where('applicant_id',$applicant->id)->update(['status'=>'ELIGIBLE']);
		}else{
			if($usr = User::where('username',$request->get('index_number'))->first()){
                $user = $usr;
                $user->password = Hash::make($request->get('index_number'));
                $user->save();
            }else{
                $user = new User;
                $user->username = $request->get('index_number');
                $user->password = Hash::make($request->get('index_number'));
                $user->save();
            }

            $role = Role::where('name','applicant')->first();
            $user->roles()->sync([$role->id]);

            // if($app = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$staff->campus_id)->latest()->first()){
            //     $applicant = $app;
            //     $applicant->entry_mode = $request->get('entry_mode');
            //     $applicant->program_level_id = $award->id;
            //     $applicant->batch_id = $batch_id;
            //     $applicant->payment_complete_status = 1;
            //     $applicant->is_transfered = 1;

            // }else{
            $applicant = new Applicant;
            $applicant->user_id = $user->id;
            $applicant->campus_id = $staff->campus_id;
            $applicant->index_number = strtoupper($request->get('index_number'));
            $applicant->entry_mode = $request->get('entry_mode');
            $applicant->program_level_id = $award->id;
            $applicant->intake_id = $application_window->intake_id;
            $applicant->application_window_id = $application_window->id;
            $applicant->batch_id = $batch_id;
            $applicant->payment_complete_status = 0;
            $applicant->is_transfered = 1;
            //}
            $applicant->save();
		}

		//$applicant = Applicant::with(['selections.campusProgram','nectaResultDetails','nacteResultDetails','applicationWindow'])->find($applicant->id);
        $applicant = Applicant::find($applicant->id);

        $selection = new ApplicantProgramSelection;
		$selection->applicant_id = $applicant->id;
		$selection->application_window_id = $application_window->id;
		$selection->campus_program_id = $request->get('campus_program_id');
        $selection->order = 5;
        $selection->status = 'PENDING';
        $selection->batch_id = $batch_id;
        $selection->save();

		$prog = CampusProgram::with('program')->find($request->get('campus_program_id'));
		// $admitted_program = $prog;
		// $admitted_program_code = $prog->code;

        $transfer = new ExternalTransfer;
        $transfer->applicant_id = $applicant->id;
        $transfer->new_campus_program_id = $prog->id;
        $transfer->previous_program = $request->get('program_code');
        $transfer->transfered_by_user_id = Auth::user()->id;
        $transfer->status = 'PENDING';
        $transfer->save();

        $applicant->confirmation_status = 'TRANSFERED';
        $applicant->save();

        // $applicant = Applicant::whereHas('selections',function($query) use($request){
        //      $query->where('status','PENDING');
        // })->with(['nextOfKin','intake','selections'=>function($query){
        //      $query->where('status','PENDING');
        // },'selections.campusProgram.program','applicationWindow','country','selections.campusProgram.campus'])->where('program_level_id',$applicant->program_level_id)->where('application_window_id',$applicant->application_window_id)->find($applicant->id);

        // Applicant::whereHas('intake.applicationWindows',function($query) use($request){
        //      $query->where('id',$request->application_window_id);
        // })->whereHas('selections',function($query) use($request){
        //      $query->where('status','APPROVING');
        // })->with(['nextOfKin','intake','selections'=>function($query){
        //      $query->where('status','APPROVING');
        // },'selections.campusProgram.program.award','applicationWindow','country'])->where('program_level_id',$request->program_level_id)->update(['admission_reference_no'=>$request->reference_number]);


      //          $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
      //          $ac_year += 1;
      //          $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
      //                 $query->where('year','LIKE','%/'.$ac_year.'%');
      //           })->with('academicYear')->first();
      //          if(!$study_academic_year){
      //              redirect()->back()->with('error','Admission study academic year not created');
      //          }

      //          $program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

      //          if(!$program_fee){
      //              redirect()->back()->with('error','Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name);
      //          }

      //          $medical_insurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');
      //          })->first();

      //          if(!$medical_insurance_fee){
      //              redirect()->back()->with('error','Medical insurance fee not defined');
      //          }

      //          if(str_contains($applicant->selections[0]->campusProgram->program->award->name,'Bachelor')){
      //             $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%TCU%');
      //             })->first();
      //          }else{
      //             $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%NACTE%');
      //             })->first();
      //          }


      //          if(!$nacte_quality_assurance_fee){
      //              redirect()->back()->with('error','NACTE fee not defined');
      //          }

      //          $practical_training_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Practical%');
      //          })->first();

      //          if(!$practical_training_fee){
      //              redirect()->back()->with('error','Practical training fee not defined');
      //          }

      //          $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%MNMASO%')->orWhere('name','LIKE','%Student Organization%')->orWhere('name','LIKE','%MASO%');
      //          })->first();

      //          if(!$students_union_fee){
      //              redirect()->back()->with('error','Students union fee not defined');
      //          }

      //          $caution_money_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Caution Money%');
      //          })->first();

      //          if(!$caution_money_fee){
      //              redirect()->back()->with('error','Caution money fee not defined');
      //          }

      //          $medical_examination_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Medical Examination%');
      //          })->first();

      //          if(!$medical_examination_fee){
      //              redirect()->back()->with('error','Medical examination fee not defined');
      //          }

      //          $registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Registration%');
      //          })->first();

      //          if(!$registration_fee){
      //              redirect()->back()->with('error','Registration fee not defined');
      //          }

      //          $identity_card_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Identity Card%');
      //          })->first();

      //          if(!$identity_card_fee){
      //              redirect()->back()->with('error','Identity card fee not defined');
      //          }

      //          $late_registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
      //              $query->where('name','LIKE','%Late Registration%');
      //          })->first();

      //          if(!$late_registration_fee){
      //              redirect()->back()->with('error','Late registration fee not defined');
      //          }

      //          $orientation_date = SpecialDate::where('name','Orientation')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)->first();

      //          if(!$orientation_date){
      //              return redirect()->back()->with('error','Orientation date not defined');
      //          }

      //          $numberToWords = new NumberToWords();
      //          $numberTransformer = $numberToWords->getNumberTransformer('en');

      //          $data = [
      //            'applicant'=>$applicant,
      //            'campus_name'=>$applicant->selections[0]->campusProgram->campus->name,
      //            'orientation_date'=>$orientation_date,
      //            'applicant_name'=>$applicant->first_name.' '.$applicant->surname,
      //            'reference_number'=>$applicant->admission_reference_no,
      //            'program_name'=>$applicant->selections[0]->campusProgram->program->name,
      //            'program_code_name'=>$applicant->selections[0]->campusProgram->program->award->name,
      //            'study_year'=>$study_academic_year->academicYear->year,
      //            'commencement_date'=>$study_academic_year->begin_date,
      //            'program_fee'=>str_contains($applicant->nationality,'Tanzania')? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
      //            'program_duration'=>$numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
      //            'program_fee_words'=>str_contains($applicant->nationality,'Tanzania')? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
      //            'currency'=>str_contains($applicant->nationality,'Tanzania')? 'Tsh' : 'Usd',
      //            'medical_insurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,
      //            'medical_examination_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,
      //            'registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,
      //            'late_registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,
      //            'practical_training_fee'=>str_contains($applicant->nationality,'Tanzania')? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd,
      //            'identity_card_fee'=>str_contains($applicant->nationality,'Tanzania')? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
      //            'caution_money_fee'=>str_contains($applicant->nationality,'Tanzania')? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
      //            'nacte_quality_assurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $nacte_quality_assurance_fee->amount_in_tzs : $nacte_quality_assurance_fee->amount_in_usd,
      //            'students_union_fee'=>str_contains($applicant->nationality,'Tanzania')? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
      //          ];

      //          $pdf = PDF::loadView('dashboard.application.reports.admission-letter',$data,[],[
      //              'margin_top'=>20,
      //              'margin_bottom'=>20,
      //              'margin_left'=>20,
      //              'margin_right'=>20
      //          ])->save(base_path('public/uploads').'/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');
      //          $user = new User;
      //          $user->email = $applicant->email;
      //          $user->username = $applicant->first_name.' '.$applicant->surname;
      //          Mail::to($user)->send(new AdmissionLetterCreated($applicant,$study_academic_year,$pdf));

			   // $app = Applicant::find($applicant->id);
      //          $app->status = 'ADMITTED';
			   // //$app->documents_complete_status = 0;
      //          $app->save();
               //redirect()->back()->with('message','Admission package sent successfully');
            DB::commit();
            return redirect()->to('registration/external-transfer')->with('message','Transfer rgistration completed successfully');


	 }

	 /**
	 * Update external transfer
	 */
	 public function updateExternalTransfer(Request $request)
	 { 
		$staff = User::find(Auth::user()->id)->staff;

        $applicant = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$staff->campus_id)->latest()->first();

        $applicant->index_number = strtoupper($request->get('index_number'));
        if($applicant->entry_mode != $request->get('entry_mode')){
            $applicant->result_complete_status = 0;
        }
        $applicant->entry_mode = $request->get('entry_mode');
		$applicant->is_transfered = 1;
        $applicant->save();

		ApplicantProgramSelection::where('applicant_id',$applicant->id)->update(['campus_program_id'=> $request->get('campus_program_id')]);

        // $selection = new ApplicantProgramSelection;
		// $selection->applicant_id = $applicant->id;
		// $selection->application_window_id = $applicant->application_window_id;
		// $selection->campus_program_id = $request->get('campus_program_id');
        // $selection->order = 1;
        // $selection->status = 'SELECTED';
        // $selection->batch_id = $batch_id;
        // $selection->save();

		$prog = CampusProgram::with('program')->find($request->get('campus_program_id'));

		$applicant = Applicant::whereHas('selections',function($query) use($applicant){$query->where('order',5)->where('batch_id',$applicant->batch_id);})
                              ->with(['nextOfKin','intake','selections','selections.campusProgram.program','applicationWindow','country','selections.campusProgram.campus'])
                              ->where('program_level_id',$applicant->program_level_id)->where('application_window_id',$applicant->application_window_id)->find($applicant->id);

        $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
        $ac_year += 1;
        $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){$query->where('year','LIKE','%/'.$ac_year.'%');})->with('academicYear')->first();

        if(!$study_academic_year){
            redirect()->back()->with('error','Study academic year not defined.');
        }

        $special_dates = SpecialDate::where('name','Orientation')
        ->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicant->intake->name)->where('campus_id',$applicant->campus_id)->get();

        $orientation_date = null;
        if(count($special_dates) == 0){
            return redirect()->back()->with('error','Orientation date has not been defined');
        }else{
            foreach($special_dates as $special_date){
                $specialDateFlag = false;
                if(!in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    $specialDateFlag = true;

                }else{
                    $orientation_date = $special_date->date;
                    break;
                }
            }
            if($specialDateFlag){
                return redirect()->back()->with('error','Orientation date for '.$applicant->selections[0]->campusProgram->program->award->name.' has not been defined');
            }
        }

        $transfer = ExternalTransfer::select('status')->where('applicant_id',$applicant->id)->first();
        if($transfer->status == 'NOT ELIGIBLE'){
            ApplicantProgramSelection::where('applicant_id',$applicant->id)->update(['status'=>'SELECTED']);
            $admission_references = AdmissionReferenceNumber::where('study_academic_year_id', $study_academic_year->id)->where('intake', $applicant->intake->name)
            ->where('campus_id', $applicant->campus_id)->get();
            
            $reference_number = null;
            $reference_status = false;
            foreach($admission_references as $reference){
                if(in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($reference->applicable_levels))){
                    $reference_number = $reference->name;
                    $reference_status = true;
                    break;
                }
            }

            if(!$reference_status){
                return redirect()->back()->with('error','Reference number for '.$applicant->selections[0]->campusProgram->program->award->name.'\'s admission letters not defined.');  
            }  

            $medical_insurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
                return redirect()->back()->with('error','Medical insurance fee has not been defined');
            }

            $students_union_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','NOT LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%Union%')->orWhere('name','LIKE','%MASO%');})->first();

            if(!$students_union_fee){
                return redirect()->back()->with('error','Students union fee has not been defined');
            }

            $caution_money_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
                return redirect()->back()->with('error','Caution money fee has not been defined');
            }

            $medical_examination_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
                return redirect()->back()->with('error','Medical examination fee has not been defined');
            }

            $registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
                return redirect()->back()->with('error','Registration fee has not been defined');
            }

            $identity_card_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
                return redirect()->back()->with('error','ID card fee for new students has not been defined');
            }

            $late_registration_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Late Registration%');})->first();

            if(!$late_registration_fee){
                return redirect()->back()->with('error','Late registration fee has not been defined');
            }

            $welfare_emergence_fund = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Welfare%')->where('name','LIKE','%Fund%')->orWhere('name','LIKE','%Emergency%');})->first();

            if(!$welfare_emergence_fund){
                return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined");
            }

            $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                        ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                        ->where('name','LIKE','%TCU%');})->first();

            if(!$quality_assurance_fee){
                return redirect()->back()->with('error','TCU quality assurance fee has not been defined');
            }

            $program_fee = ProgramFee::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('year_of_study',1)
            ->where('campus_program_id',$request->get('campus_program_id'))->first();

            if(!$program_fee){
                return redirect()->back()->with('error','Programme fee not defined for '.$prog->program->name);
            }

            $teaching_practice = null;
            if(str_contains(strtolower($prog->program->name),'bachelor') && str_contains(strtolower($prog->program->name),'education')){
                $teaching_practice = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Teaching%')->where('name','LIKE','%Practice%'); })->first();

                if(!$teaching_practice){
                    return redirect()->back()->with('error','Teaching practice fee not defined');
                }
            }

            if ($teaching_practice) {
                $teaching_practice = str_contains($applicant->nationality, 'Tanzania') ? $teaching_practice->amount_in_tzs : $teaching_practice->amount_in_usd;
            }

            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');

            $data = [
                'applicant' => $applicant,
                'campus_name' => $applicant->selections[0]->campusProgram->campus->name,
                'applicant_name' => $applicant->first_name . ' ' . $applicant->surname,
                'reference_number' => $reference_number,
                'program_name' => $prog->program->name,
                'program_code_name' => $applicant->selections[0]->campusProgram->program->award->name,
                'study_year' => $study_academic_year->academicYear->year,
                'program_duration_no' => $applicant->selections[0]->campusProgram->program->min_duration,
                'orientation_date' => $orientation_date,
                'program_fee' => str_contains($applicant->nationality, 'Tanzania') ? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
                'program_duration' => $numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
                'program_fee_words' => str_contains($applicant->nationality, 'Tanzania') ? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
                'annual_program_fee_words' => str_contains($applicant->nationality, 'Tanzania') ? $numberTransformer->toWords(($program_fee->amount_in_tzs)/2) : $numberTransformer->toWords(($program_fee->amount_in_usd)/2),
                'research_supervision_fee'=> null,
                'currency' => str_contains($applicant->nationality, 'Tanzania') ? 'Tsh' : 'Usd',
                'medical_insurance_fee' => str_contains($applicant->nationality, 'Tanzania') ? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,
                'medical_examination_fee' => str_contains($applicant->nationality, 'Tanzania') ? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,
                'registration_fee' => str_contains($applicant->nationality, 'Tanzania') ? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,
                'late_registration_fee' => str_contains($applicant->nationality, 'Tanzania') ? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,
                'practical_training_fee' => null,
                'teaching_practice' => $teaching_practice,
                'identity_card_fee' => str_contains($applicant->nationality, 'Tanzania') ? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
                'caution_money_fee' => str_contains($applicant->nationality, 'Tanzania') ? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
                'nacte_quality_assurance_fee' => str_contains($applicant->nationality, 'Tanzania') ? $quality_assurance_fee->amount_in_tzs : $quality_assurance_fee->amount_in_usd,
                'students_union_fee' => str_contains($applicant->nationality, 'Tanzania') ? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
                'welfare_emergence_fund' => str_contains($applicant->nationality, 'Tanzania') ? $welfare_emergence_fund->amount_in_tzs : $welfare_emergence_fund->amount_in_usd,
            ];
        



            //    $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
            //    $ac_year += 1;
            //    $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
            //           $query->where('year','LIKE','%/'.$ac_year.'%');
            //     })->with('academicYear')->first();
            //    if(!$study_academic_year){
            //        redirect()->back()->with('error','Admission study academic year not created');
            //    }

            //    $program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campusProgram->id)->first();

            //    if(!$program_fee){
            //        redirect()->back()->with('error','Programme fee not defined for '.$applicant->selections[0]->campusProgram->program->name);
            //    }

            //    $medical_insurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%NHIF%')->orWhere('name','LIKE','%Medical Care%');
            //    })->first();

            //    if(!$medical_insurance_fee){
            //        redirect()->back()->with('error','Medical insurance fee not defined');
            //    }

            //    if(str_contains($applicant->selections[0]->campusProgram->program->award->name,'Bachelor')){
            //       $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%TCU%');
            //       })->first();
            //    }else{
            //       $nacte_quality_assurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');})->first();
            //    }


            //    if(!$nacte_quality_assurance_fee){
            //        redirect()->back()->with('error','NACTVET Quality Assurance fee not defined');
            //    }

            //    $practical_training_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%Practical%');
            //    })->first();

            //    if(!$practical_training_fee){
            //        redirect()->back()->with('error','Practical training fee not defined');
            //    }

            //    $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%MNMASO%')->orWhere('name','LIKE','%Student Organization%')->orWhere('name','LIKE','%MASO%')
            //        ->orWhere('name','LIKE','%Students Union%');})->first();

            //    if(!$students_union_fee){
            //        redirect()->back()->with('error','Students union fee not defined');
            //    }

            //    $caution_money_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%Caution Money%');
            //    })->first();

            //    if(!$caution_money_fee){
            //        redirect()->back()->with('error','Caution money fee not defined');
            //    }

            //    $medical_examination_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%Medical Examination%');
            //    })->first();

            //    if(!$medical_examination_fee){
            //        redirect()->back()->with('error','Medical examination fee not defined');
            //    }

            //    $registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%Registration%');
            //    })->first();

            //    if(!$registration_fee){
            //        redirect()->back()->with('error','Registration fee not defined');
            //    }

            //    $identity_card_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%New ID Card%');
            //    })->first();

            //    if(!$identity_card_fee){
            //        redirect()->back()->with('error','ID card fee for new students not defined');
            //    }

            //    $late_registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->whereHas('feeItem',function($query){
            //        $query->where('name','LIKE','%Late Registration%');
            //    })->first();

            //    if(!$late_registration_fee){
            //        redirect()->back()->with('error','Late registration fee not defined');
            //    }

            //    $orientation_date = SpecialDate::where('name','Orientation')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)->first();

            //    if(!$orientation_date){
            //        return redirect()->back()->with('error','Orientation date not defined');
            //    }

            //    $numberToWords = new NumberToWords();
            //    $numberTransformer = $numberToWords->getNumberTransformer('en');

            //    $data = [
            //      'applicant'=>$applicant,
            //      'campus_name'=>$applicant->selections[0]->campusProgram->campus->name,
            //      'orientation_date'=>$orientation_date,
            //      'applicant_name'=>$applicant->first_name.' '.$applicant->surname,
            //      'reference_number'=>$applicant->admission_reference_no,
            //      'program_name'=>$applicant->selections[0]->campusProgram->program->name,
            //      'program_code_name'=>$applicant->selections[0]->campusProgram->program->award->name,
            //      'study_year'=>$study_academic_year->academicYear->year,
            //      'commencement_date'=>$study_academic_year->begin_date,
            //      'program_fee'=>str_contains($applicant->nationality,'Tanzania')? $program_fee->amount_in_tzs : $program_fee->amount_in_usd,
            //      'program_duration'=>$numberTransformer->toWords($applicant->selections[0]->campusProgram->program->min_duration),
            //      'program_fee_words'=>str_contains($applicant->nationality,'Tanzania')? $numberTransformer->toWords($program_fee->amount_in_tzs) : $numberTransformer->toWords($program_fee->amount_in_usd),
            //      'currency'=>str_contains($applicant->nationality,'Tanzania')? 'Tsh' : 'Usd',
            //      'medical_insurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_insurance_fee->amount_in_tzs : $medical_insurance_fee->amount_in_usd,
            //      'medical_examination_fee'=>str_contains($applicant->nationality,'Tanzania')? $medical_examination_fee->amount_in_tzs : $medical_examination_fee->amount_in_usd,
            //      'registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $registration_fee->amount_in_tzs : $registration_fee->amount_in_usd,
            //      'late_registration_fee'=>str_contains($applicant->nationality,'Tanzania')? $late_registration_fee->amount_in_tzs : $late_registration_fee->amount_in_usd,
            //      'practical_training_fee'=>str_contains($applicant->nationality,'Tanzania')? $practical_training_fee->amount_in_tzs : $practical_training_fee->amount_in_usd,
            //      'identity_card_fee'=>str_contains($applicant->nationality,'Tanzania')? $identity_card_fee->amount_in_tzs : $identity_card_fee->amount_in_usd,
            //      'caution_money_fee'=>str_contains($applicant->nationality,'Tanzania')? $caution_money_fee->amount_in_tzs : $caution_money_fee->amount_in_usd,
            //      'nacte_quality_assurance_fee'=>str_contains($applicant->nationality,'Tanzania')? $nacte_quality_assurance_fee->amount_in_tzs : $nacte_quality_assurance_fee->amount_in_usd,
            //      'students_union_fee'=>str_contains($applicant->nationality,'Tanzania')? $students_union_fee->amount_in_tzs : $students_union_fee->amount_in_usd,
            //    ];

               $pdf = PDF::loadView('dashboard.application.reports.admission-letter',$data,[],[
                   'margin_top'=>20,
                   'margin_bottom'=>20,
                   'margin_left'=>20,
                   'margin_right'=>20
               ])->save(base_path('public/uploads').'/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf');

                $applicant = Applicant::find($applicant->id);
                $applicant->status = 'ADMITTED';
                $applicant->confirmation_status = 'TRANSFERED';
                $applicant->save();

                $selection =ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('batch_id',$applicant->batch_id)->where('order',5)->first();
                $selection->campus_program_id = $request->get('campus_program_id');
                $selection->status = 'SELECTED';
                $selection->save();

                $transfer = ExternalTransfer::where('applicant_id',$applicant->id)->first();
                $transfer->status = 'ELIGIBLE';
                $transfer->save();
                
                $user = new User;
                $user->email = $applicant->email;
                $user->username = $applicant->first_name.' '.$applicant->surname;
                Mail::to($user)->send(new AdmissionLetterCreated($applicant,$study_academic_year,$pdf));
        }
			   //$app = Applicant::find($applicant->id);
               //$app->status = 'ADMITTED';
			   //$app->documents_complete_status = 0;
               //s$app->save();
               //redirect()->back()->with('message','Admission package sent successfully');
            return redirect()->to('registration/external-transfer')->with('message','Transfer updated successfully');
	 }

    /**
     * Show internal transfer
     */
    public function showExternalTransfer(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

		$window = ApplicationWindow::where('status','ACTIVE')->where('campus_id',$staff->campus_id)->first();

		$campus_programs = $window? $window->campusPrograms()->whereHas('program.award',function($query){
                   $query->where('name','LIKE','%Degree%');
           })->whereHas('entryRequirements',function($query) use($window){
                   $query->where('application_window_id',$window->id);
           })->with(['program','campus','entryRequirements'=>function($query) use($window){
                $query->where('application_window_id',$window->id);
           }])->where('campus_id',$staff->campus_id)->get() : [];

        $data = [
            'transfers'=>ExternalTransfer::whereHas('applicant',function($query) use($staff){
                  $query->where('campus_id',$staff->campus_id);
            })->with(['applicant.user','newProgram.program','user.staff'])->latest()->get(),
			'campus_programs'=>$campus_programs,
            'staff'=>$staff,
            'transfered_campus_programs'=>ExternalTransfer::distinct()
            ->select('new_campus_program_id')
            ->whereHas('applicant',function($query) use($staff){$query
                                          ->where('campus_id',$staff->campus_id);})
            ->with('newProgram.program')
            ->where('status','SUBMITTED')
            ->get()
        ];
        return view('dashboard.registration.submit-external-transfer',$data)->withTitle('External Transfer');
    }

	/**
     * Edit external transfer
     */
    public function editExternalTransfer(Request $request, $id)
    {
        $staff = User::find(Auth::user()->id)->staff;

		$transfer = ExternalTransfer::with(['applicant.user','newProgram.program','user.staff'])->find($id);

		$applicant = Applicant::with(['selections.campusProgram.program','nectaResultDetails'=>function($query){
                 $query->where('verified',1);
            },'nacteResultDetails'=>function($query){
                 $query->where('verified',1);
            },'outResultDetails'=>function($query){
                 $query->where('verified',1);
            },'selections.campusProgram.campus','nectaResultDetails.results','nacteResultDetails.results','outResultDetails.results','programLevel','applicationWindow'])->find($transfer->applicant_id);

        // $window = $applicant->applicationWindow;

		// $campus_programs = $window? $window->campusPrograms()->whereHas('program.award',function($query){
        //            $query->where('name','LIKE','%Degree%');
        //    })->whereHas('entryRequirements',function($query) use($window){
        //            $query->where('application_window_id',$window->id);
        //    })->with(['program','campus','entryRequirements'=>function($query) use($window){
        //         $query->where('application_window_id',$window->id);
        //    }])->where('campus_id',$staff->campus_id)->get() : [];


        // $award = $applicant->programLevel;
        // $programs = [];

        $campus_progs = $available_progs = $all_programs = [];

        $window = $applicant->applicationWindow;
        $campus_programs = $window? $window->campusPrograms()
                                            ->whereHas('program',function($query) use($applicant){$query->where('award_id',$applicant->program_level_id);})
                                            ->with(['program','campus','entryRequirements'=>function($query) use($window){$query->where('application_window_id',$window->id);}])
                                            ->where('campus_id',$staff->campus_id)->get() : [];

        $entry_requirements = null;
        // foreach($campus_programs as $prog){
        //     $entry_requirements[] = EntryRequirement::select('id','campus_program_id','max_capacity')->where('application_window_id', $window->id)->where('campus_program_id',$prog->id)
        //                                             ->with('campusProgram:id,code')->first();
        //     $all_programs[] = $prog;
        // }

        // foreach($campus_programs as $prog){

        //     $count_applicants_per_program = ApplicantProgramSelection::where('campus_program_id', $prog->id)
        //                                         ->where(function($query) {
        //                                             $query->where('applicant_program_selections.status', 'SELECTED')
        //                                                 ->orWhere('applicant_program_selections.status', 'APPROVING');
        //                                         })
        //                                         ->count();

        //     if ($count_applicants_per_program >= $prog->entryRequirements[0]->max_capacity) {
                
        //         $campus_progs[] = $prog;
        //     }else if($count_applicants_per_program < $prog->entryRequirements[0]->max_capacity){
        //         $available_progs[] = $prog;
        //     }
        // }

        // // dd( $campus_progs);

        // $campus_programs = $available_progs;

        $programs = [];

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $index_number = $applicant->index_number;

        if(str_contains($index_number,'EQ')){
            $exam_year = explode('/',$index_number)[1];
        }else{
            $exam_year = explode('/', $index_number)[2];
        }

        foreach($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 2 && $detail->verified == 1){
                $index_number = $detail->index_number;
                if(str_contains($index_number,'EQ')){
                    $exam_year = explode('/',$index_number)[1];
                }else{
                    $exam_year = explode('/', $index_number)[2];
                }
            }
        }
  
        if($exam_year < 2014 || $exam_year > 2015){
            $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'E';
            $diploma_subsidiary_pass_grade = 'S';
            $principle_pass_grade = 'E';
            $subsidiary_pass_grade = 'S';
        }else{
            $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'D';
            $diploma_subsidiary_pass_grade = 'E';
            $principle_pass_grade = 'D';
            $subsidiary_pass_grade = 'E';
        }

           $o_level_points = $a_level_points = $diploma_gpa = null;
           $subject_count = 0;
           foreach($campus_programs as $program){
                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements, please check with the Admission Office');
                }

                $o_level_points = $a_level_points = $diploma_gpa = null;
                $o_level_pass_count = 0;
                $o_level_other_pass_count = 0;
                $a_level_principle_pass_count = 0;
                $a_level_principle_pass_points = 0;
                $a_level_subsidiary_pass_count = 0;
                $a_level_out_principle_pass_count = 0;
                $a_level_out_principle_pass_points = 0;
                $a_level_out_subsidiary_pass_count = 0;

                foreach ($applicant->nectaResultDetails as $detail) {
                if($detail->exam_id == 1 && $detail->verified == 1){
                    $other_must_subject_ready = false;
                    foreach ($detail->results as $key => $result) {

                        if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                            $applicant->rank_points += $o_level_grades[$result->grade];
                            $subject_count += 1;

                            if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                    $other_must_subject_ready = true;
                                }

                                }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }
                                }
                            }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }

                            if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                    $other_must_subject_ready = true;
                                }

                                }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                    $o_level_other_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }
                                }
                            }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }
                        }
                    }
                }elseif($detail->exam_id == 2 && $detail->verified == 1){
                    $other_advance_must_subject_ready = $other_out_advance_must_subject_ready = false;

                    foreach ($detail->results as $result) {
                        if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                            if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_principle_pass_count += 1;
                                    $other_advance_must_subject_ready = true;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }else{
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }
                            }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                            }
                            }else{
                                $a_level_principle_pass_count += 1;
                                $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }
                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce the sample
                            if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                            if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                    $other_advance_must_subject_ready = true;
                                }

                            }else{
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }
                            }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                            }
                            }else{
                            $a_level_subsidiary_pass_count += 1;
                            $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){

                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){

                            if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                    $a_level_out_principle_pass_count += 1;
                                    $other_out_advance_must_subject_ready = true;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }else{
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }else{
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                            }
                            }
                            }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                            }
                            }else{
                                $a_level_out_principle_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_subsidiary_pass_grade]){

                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                            if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $other_out_advance_must_subject_ready = true;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }else{
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }else{
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                            }
                            }
                            }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                            }
                            }else{
                                $a_level_out_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){

                            if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                            if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }

                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                    $other_advance_must_subject_ready = true;
                                }

                            }else{
                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }
                            }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                            }
                            }else{
                            $a_level_subsidiary_pass_count += 1;
                            $a_level_points += $a_level_grades[$result->grade];
                            }
                        }
                    }
                }
                }
  
                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){
  
                          $programs[] = $program;

                    }
                 }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){
  
                    $programs[] = $program;
  
                 } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                    $programs[] = $program;
                 }
  
                 $has_major = false;
                 $equivalent_must_subjects_count = 0;
                 $diploma_gpa = $out_gpa = null;
                 $has_nacte_results = false;
  
                //  foreach($applicant->nacteResultDetails as $detail){
                //     if(count($detail->results) == 0 && $detail->verified == 1){
                //        $has_nacte_results = true;
                //        $diploma_gpa = $detail->diploma_gpa;
                //     }
                //  }
  
                //  if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_nacte_results && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa){
  
                //        $programs[] = $program;
                //  }



  
                 if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                    foreach($applicant->nacteResultDetails as $detail){
                       if($detail->verified == 1){
                          foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                             if(str_contains(strtolower($detail->programme),strtolower($sub))){
  
                                $has_major = true;
                             }
                          }
                          $diploma_gpa = $detail->diploma_gpa;
                       }
                    }
                    if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                        foreach($applicant->nacteResultDetails as $detail){
                           if($detail->verified == 1){
                              foreach($detail->results as $result){
                                 foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                     if(str_contains(strtolower($result->subject),strtolower($sub))){
                                         $equivalent_must_subjects_count += 1;
                                     }
                                 }
                              }
                           }
                        }
                    }
  
                 }else{
                    if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                       foreach($applicant->nacteResultDetails as $detail){
                          if($detail->verified == 1){
                             foreach($detail->results as $result){
                                foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                    if(str_contains(strtolower($result->subject),strtolower($sub))){
                                        $equivalent_must_subjects_count += 1;
                                    }
                                }
                             }
                             $diploma_gpa = $detail->diploma_gpa;
                          }
                       }
                    }
                 }
  
                if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && unserialize($program->entryRequirements[0]->equivalent_must_subjects) == ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_major && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa){

                        $programs[] = $program;

                    }
                }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                    if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects &&
                            $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) &&
                            $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects &&
                            $applicant->avn_no_results === 1 && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

                        $programs[] = $program;

                    }
                }

                $out_pass_subjects_count = 0;
                if(unserialize($program->entryRequirements[0]->open_exclude_subjects) != '') //['OFC 017','OFP 018','OFP 020'];
                {
                    $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects);

                    foreach($applicant->outResultDetails as $detail){
                        if($detail->verified == 1){
                            foreach($detail->results as $key => $result){
                                if(!Util::arrayIsContainedInKey($result->subject_code, $exclude_out_subjects_codes)){
                                if($out_grades[$result->grade] >= $out_grades['C']){
                                    $out_pass_subjects_count += 1;
                                }
                                }
                            }
                            $out_gpa = $detail->gpa;
                        }
                    }
                }else{
                    foreach($applicant->outResultDetails as $detail){
                        if($detail->verified == 1){
                            foreach($detail->results as $key => $result){
                                if($out_grades[$result->grade] >= $out_grades['C']){
                                $out_pass_subjects_count += 1;
                                }
                            }
                            $out_gpa = $detail->gpa;
                        }
                    }
                }

                if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 &&
                    $a_level_out_principle_pass_count >= 1){

                    $programs[] = $program;

                }

                // OUT with diploma of 2.0 and above
                if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                    if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                        $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) &&
                        $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects &&
                        $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){

                        $programs[] = $program;

                    }
                }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major &&
                        $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){

                        $programs[] = $program;

                    }
                }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) == ''){
                    if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa &&
                            $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){

                        $programs[] = $program;

                    }
                }

                if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){

                    $programs[] = $program;

                }
          }










        // $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        // $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        // $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        // $selected_program = array();

        //    $index_number = $applicant->index_number;
        //    $exam_year = explode('/', $index_number)[2];

        //    foreach($applicant->nectaResultDetails as $detail) {
        //       if($detail->exam_id == 2){
        //           $index_number = $detail->index_number;
        //           $exam_year = explode('/', $index_number)[2];
        //       }
        //    }

        //    if($exam_year < 2014 || $exam_year > 2015){
        //      $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
        //      $diploma_principle_pass_grade = 'E';
        //      $diploma_subsidiary_pass_grade = 'S';
        //      $principle_pass_grade = 'D';
        //      $subsidiary_pass_grade = 'S';
        //    }else{
        //      $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
        //      $diploma_principle_pass_grade = 'D';
        //      $diploma_subsidiary_pass_grade = 'E';
        //      $principle_pass_grade = 'C';
        //      $subsidiary_pass_grade = 'E';
        //    }

        //    $subject_count = 0;
		//    $has_capacity = true;
        //       foreach($campus_programs as $program){

        //         if(count($program->entryRequirements) == 0){
        //             return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
        //         }

        //         $o_level_pass_count = 0;
        //         $a_level_principle_pass_count = 0;
        //         $a_level_principle_pass_points = 0;
        //         $a_level_subsidiary_pass_count = 0;
        //         $a_level_out_principle_pass_count = 0;
        //         $a_level_out_principle_pass_points = 0;
        //         $a_level_out_subsidiary_pass_count = 0;

        //         foreach ($applicant->nectaResultDetails as $detail) {
        //             if($detail->exam_id == 1){
        //                 $other_must_subject_ready = false;
        //                 foreach ($detail->results as $result) {
        //                     if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

        //                         $subject_count += 1;

        //                         if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
        //                                     $o_level_pass_count += 1;
        //                                 }
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
        //                                     $o_level_pass_count += 1;
        //                                     $other_must_subject_ready = true;
        //                                 }
        //                             }else{
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
        //                                     $o_level_pass_count += 1;
        //                                 }
        //                             }
        //                         }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
        //                                     $o_level_pass_count += 1;
        //                             }
        //                         }else{
        //                                 $o_level_pass_count += 1;
        //                         }
        //                     }
        //                 }
        //             }elseif($detail->exam_id == 2){
        //                 $other_advance_must_subject_ready = false;
        //                 $other_out_advance_must_subject_ready = false;

        //                 foreach ($detail->results as $key => $result) {

        //                     if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

        //                         $subject_count += 1;
        //                         if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                     $a_level_principle_pass_count += 1;
        //                                     $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }

        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
        //                                     $a_level_principle_pass_count += 1;
        //                                     $other_advance_must_subject_ready = true;
        //                                     $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }
        //                             }else{
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                     $a_level_principle_pass_count += 1;
        //                                     $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }
        //                             }
        //                         }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                     $a_level_principle_pass_count += 1;
        //                                     $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                             }
        //                         }else{
        //                             $a_level_principle_pass_count += 1;
        //                             $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                         }
        //                     }
        //                     if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

        //                         if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
        //                             if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
        //                                 $a_level_subsidiary_pass_count += 1;
        //                             }
        //                         }
        //                     }

        //                     if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){

        //                         $subject_count += 1;
        //                         if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                     $a_level_out_principle_pass_count += 1;
        //                                     $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }

        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
        //                                     $a_level_out_principle_pass_count += 1;
        //                                     $other_out_advance_must_subject_ready = true;
        //                                     $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }
        //                             }else{
        //                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                     $a_level_out_principle_pass_count += 1;
        //                                     $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
        //                                 }
        //                             }
        //                         }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                     $a_level_out_principle_pass_count += 1;
        //                                     $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
        //                             }
        //                         }else{
        //                             $a_level_out_principle_pass_count += 1;
        //                             $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
        //                         }
        //                     }
        //                     if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_subsidiary_pass_grade]){
        //                         if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
        //                             if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
        //                                 $a_level_out_subsidiary_pass_count += 1;
        //                             }
        //                         }
        //                     }
        //                 }
        //             }
        //         }

        //         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

        //             $programs[] = $program;
        //         }

        //         $has_major = false;
        //         $equivalent_must_subjects_count = 0;
        //         $nacte_gpa = null;
        //         $out_gpa = null;

        //         if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
        //             foreach($applicant->nacteResultDetails as $detail){
        //                 foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){

        //                 if(str_contains($detail->programme,$sub)){
        //                     $has_major = true;
        //                 }
        //                 }
        //                 $nacte_gpa = $detail->diploma_gpa;
        //             }
        //         }else{
        //             if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
        //                 foreach($applicant->nacteResultDetails as $detail){
        //                     foreach($detail->results as $result){
        //                         foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
        //                             if(str_contains($result->subject,$sub)){
        //                                 $equivalent_must_subjects_count += 1;
        //                             }
        //                         }
        //                     }
        //                     $nacte_gpa = $detail->diploma_gpa;
        //                 }
        //             }
        //         }
        //         if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
        //             if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_major && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){

        //                 $programs[] = $program;
        //             }
        //         }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
        //             if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

        //                 $programs[] = $program;
        //             }
        //         }

        //         $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects); //['OFC 017','OFP 018','OFP 020'];
        //         $out_pass_subjects_count = 0;

        //         foreach($applicant->outResultDetails as $detail){
        //             foreach($detail->results as $key => $result){
        //                 if(!in_array($result->code, $exclude_out_subjects_codes)){
        //                     if($out_grades[$result->grade] >= $out_grades['C']){
        //                         $out_pass_subjects_count += 1;
        //                     }
        //                 }
        //             }
        //             $out_gpa = $detail->gpa;
        //         }

        //         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 && $a_level_out_principle_pass_count >= 1){
        //                 $programs[] = $program;
        //         }

        //         if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
        //             if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){
        //                     $programs[] = $program;
        //             }
        //         }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
        //             if($out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){
        //                     $programs[] = $program;
        //             }
        //         }

        //         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){
        //                 $programs[] = $program;
        //         }
        //     }

        $data = [
            'transfer'=>$transfer,
			'campus_programs'=>$programs,
            'staff'=>$staff
        ];
        return view('dashboard.registration.edit-external-transfer',$data)->withTitle('External Transfer');
    }

    /**
     * Submit internal transfer
     */
    public function submitInternalTransfer(Request $request)
    {
		DB::beginTransaction();
        $student = Student::with(['applicant.selections.campusProgram','applicant.nectaResultDetails.results','applicant.nacteResultDetails.results','applicant.programLevel','applicant.campus','applicant.nextOfKin','applicant.intake'])->find($request->get('student_id'));

		if(InternalTransfer::where('student_id',$student->id)->count() != 0){
			return redirect()->back()->with('error','Student already transfered');
		}

        $award = $student->applicant->programLevel;
        $applicant = $student->applicant;

		$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
		$semester = Semester::where('status','ACTIVE')->first();

		$registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first();
        if(!$registration){
			return redirect()->back()->with('error','Student has not been registered yet');
		}

        $intake = $applicant->intake_id == 1? 'September' : 'March';

		$dates = SpecialDate::where('name','New Registration Period')->where('study_academic_year_id',$ac_year->id)->where('campus_id',$applicant->campus_id)->where('intake',$intake)->get();

        $reg_date = null;
        foreach($dates as $date){
            if(in_array($award->name, unserialize($date->applicable_levels))){
                $reg_date = $date->date;
                break;
            }
        }

        if(empty($reg_date)){
            return redirect()->back()->with('error','Something is wrong with registration date');
        }
		if(Carbon::parse($reg_date)->addDays(7)->format('Y-m-d') < date('Y-m-d')){
			return redirect()->back()->with('error','Registration period has already passed');
		}
        $transfer_program = CampusProgram::with(['entryRequirements'=>function($query) use($applicant){
             $query->where('application_window_id',$applicant->application_window_id);
        },'program'])->find($request->get('campus_program_id'));





        $qualifies = false;

        $programs = [];

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $index_number = $applicant->index_number;
        if(str_contains($index_number,'EQ')){
        $exam_year = explode('/',$index_number)[1];
        }else{
        $exam_year = explode('/', $index_number)[2];
        }

        foreach($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 2 && $detail->verified == 1){
            $index_number = $detail->index_number;
            if(str_contains($index_number,'EQ')){
                $exam_year = explode('/',$index_number)[1];
            }else{
                $exam_year = explode('/', $index_number)[2];
            }
            }
        }

        if($exam_year < 2014 || $exam_year > 2015){
            $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'E';
            $diploma_subsidiary_pass_grade = 'S';
            $principle_pass_grade = 'E';
            $subsidiary_pass_grade = 'S';
        }else{
            $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'D';
            $diploma_subsidiary_pass_grade = 'E';
            $principle_pass_grade = 'D';
            $subsidiary_pass_grade = 'E';
        }
        // $selected_program[$applicant->id] = false;
        $o_level_points = $a_level_points = $diploma_gpa = null;
        $subject_count = 0;

        if(count($transfer_program->entryRequirements) == 0){
           return redirect()->back()->with('error',$transfer_program->program->name.' does not have entry requirements, please check with the Admission Office');
        }

        // Certificate
        if(str_contains($award->name,'Certificate')){
            $o_level_pass_count = $o_level_points = 0;
            $o_level_other_pass_count = 0;

            foreach ($applicant->nectaResultDetails as $detail) {
                if($detail->exam_id == 1 && $detail->verified == 1){
                $other_must_subject_ready = false;
                foreach ($detail->results as $key => $result) {

                    if($o_level_grades[$result->grade] >= $o_level_grades[$transfer_program->entryRequirements[0]->pass_grade]){

                        $applicant->rank_points += $o_level_grades[$result->grade];
                        $subject_count += 1;

                        if(unserialize($transfer_program->entryRequirements[0]->must_subjects) != ''){

                            if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != ''){
                            if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }

                            if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                $o_level_pass_count += 1;
                                $other_must_subject_ready = true;
                                $o_level_points += $o_level_grades[$result->grade];
                            }

                            }elseif(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                            $o_level_pass_count += 1;
                            $o_level_points += $o_level_grades[$result->grade];
                            }else{
                            if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) + count(unserialize($transfer_program->entryRequirements[0]->other_must_subjects))) < $transfer_program->entryRequirements[0]->pass_subjects){
                                $o_level_other_pass_count += 1;
                            }elseif(count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) < $transfer_program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($transfer_program->entryRequirements[0]->pass_subjects - count(unserialize($transfer_program->entryRequirements[0]->must_subjects))))){
                                $o_level_other_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }
                            }
                        }elseif(unserialize($transfer_program->entryRequirements[0]->exclude_subjects) != ''){
                            if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->exclude_subjects))){
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];

                            }
                        }else{
                            $o_level_pass_count += 1;
                            $o_level_points += $o_level_grades[$result->grade];
                        }
                    }
                }
                }

                if(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects){
                    $qualifies = true;
                }
            }
        }

        // Diploma
        if(str_contains($award->name,'Diploma')){
            $o_level_pass_count = $o_level_points = $a_level_points = $diploma_gpa = 0;
            $o_level_other_pass_count = $a_level_principle_pass_count = $a_level_subsidiary_pass_count = 0;

            foreach ($applicant->nectaResultDetails as $detail) {
                if($detail->exam_id == 1 && $detail->verified == 1){
                    $other_must_subject_ready = false;
                    foreach ($detail->results as $key => $result) {

                        if($o_level_grades[$result->grade] >= $o_level_grades[$transfer_program->entryRequirements[0]->pass_grade]){

                            $applicant->rank_points += $o_level_grades[$result->grade];
                            $subject_count += 1;

                            if(unserialize($transfer_program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                }else{
                                    if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) + count(unserialize($transfer_program->entryRequirements[0]->other_must_subjects))) < $transfer_program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                                    }elseif(count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) < $transfer_program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($transfer_program->entryRequirements[0]->pass_subjects - count(unserialize($transfer_program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->exclude_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }
                        }
                    }

                }elseif($detail->exam_id === 2 && $detail->verified == 1){
                    $other_advance_must_subject_ready = false;
                    foreach ($detail->results as $result) {

                        if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){
                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;

                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                                }
                            }else{
                                $a_level_principle_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                                }
                            }else{
                                $a_level_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }
                    }
                }

            }

            if(($o_level_pass_count + $o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && (($a_level_principle_pass_count > 0
            && ($a_level_subsidiary_pass_count + $a_level_principle_pass_count >= 2)) || $a_level_principle_pass_count >= 2)){
                $qualifies = true;

            }

            $has_btc = $has_diploma = $pass_diploma = false;

            if(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) != '' && $transfer_program->entryRequirements[0]->nta_level <= 4){
                foreach(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) as $sub){
                    foreach($applicant->nacteResultDetails as $det){
                        if(str_contains(strtolower($det->programme),strtolower($sub)) && str_contains(strtolower($det->programme),'basic') && $det->verified == 1){
                            $has_btc = true;
                            $diploma_gpa = $det->diploma_gpa;
                        }elseif(str_contains(strtolower($det->programme),'diploma') && $det->verified == 1){
                            $has_diploma = true;
                            if($det->diploma_gpa >= 2){
                            $pass_diploma = true;
                            $diploma_gpa = $det->diploma_gpa;
                            }
                        }
                    }
                }
            } elseif (unserialize($transfer_program->entryRequirements[0]->equivalent_majors) != '' && $transfer_program->entryRequirements[0]->nta_level == 5) {
            // salim added elseif part to check nta level 5 for diploma students

            }else{       // lupi added the else part to determine btc status when equivalent majors have not been defined
                foreach($applicant->nacteResultDetails as $det){
                        if(str_contains(strtolower($det->programme),'basic') && $det->verified == 1){
                            $has_btc = true;
                            $diploma_gpa = $det->diploma_gpa;
                        }elseif(str_contains(strtolower($det->programme),'diploma') && $det->verified == 1){
                            $has_diploma = true;
                            if($det->diploma_gpa >= 2){
                            $pass_diploma = true;
                            $diploma_gpa = $det->diploma_gpa;
                            }
                        }
                }
            }

            if(($o_level_pass_count + $o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $has_btc && !$has_diploma){
                $qualifies = true;

                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;

            } elseif (($o_level_pass_count + $o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $applicant->veta_status == 1) {
                $qualifies = true;

                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
            }
        }

        // Bachelor
        if(str_contains($award->name,'Bachelor')){
            $o_level_points = $a_level_points = $diploma_gpa = null;
            $o_level_pass_count = 0;
            $o_level_other_pass_count = 0;
            $o_level_must_pass_count = 0;
            $a_level_principle_pass_count = 0;
            $a_level_principle_pass_points = 0;
            $a_level_subsidiary_pass_count = 0;
            $a_level_out_principle_pass_count = 0;
            $a_level_out_principle_pass_points = 0;
            $a_level_out_subsidiary_pass_count = 0;
            $diploma_pass_count = 0;

            foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                if($detail->exam_id == 1 && $detail->verified == 1){
                    $other_must_subject_ready = false;
                    foreach ($detail->results as $key => $result) {

                        if($o_level_grades[$result->grade] >= $o_level_grades[$transfer_program->entryRequirements[0]->pass_grade]){

                            $applicant->rank_points += $o_level_grades[$result->grade];
                            $subject_count += 1;

                            if(unserialize($transfer_program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                    if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) + count(unserialize($transfer_program->entryRequirements[0]->other_must_subjects))) < $transfer_program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                    }elseif(count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) < $transfer_program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($transfer_program->entryRequirements[0]->pass_subjects - count(unserialize($transfer_program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->exclude_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }

                            if(unserialize($transfer_program->entryRequirements[0]->must_subjects) != ''){

                                if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                        $other_must_subject_ready = true;
                                    }

                                }elseif(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->must_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                }else{
                                    if(unserialize($transfer_program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) + count(unserialize($transfer_program->entryRequirements[0]->other_must_subjects))) < $transfer_program->entryRequirements[0]->pass_subjects){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                    }elseif(count(unserialize($transfer_program->entryRequirements[0]->must_subjects)) < $transfer_program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($transfer_program->entryRequirements[0]->pass_subjects - count(unserialize($transfer_program->entryRequirements[0]->must_subjects))))){
                                        $o_level_other_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->exclude_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                }
                            }else{
                                $o_level_pass_count += 1;
                                $o_level_points += $o_level_grades[$result->grade];
                            }
                        }
                    }
                }elseif($detail->exam_id == 2 && $detail->verified == 1){
                    $other_advance_must_subject_ready = false;
                    $other_out_advance_must_subject_ready = false;

                    foreach ($detail->results as $result) {
                        if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){
                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_principle_pass_count += 1;
                                        $other_advance_must_subject_ready = true;
                                        $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }else{
                                $a_level_principle_pass_count += 1;
                                $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){  
                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                }
                            }else{
                                $a_level_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){
                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                        $a_level_out_principle_pass_count += 1;
                                        $other_out_advance_must_subject_ready = true;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }else{
                                        $a_level_out_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                                }
                            }else{
                                $a_level_out_principle_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_subsidiary_pass_grade]){

                            $applicant->rank_points += $a_level_grades[$result->grade];
                            $subject_count += 1;
                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $other_out_advance_must_subject_ready = true;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }else{
                                        $a_level_out_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                                }
                            }else{
                                $a_level_out_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){
                            if(unserialize($transfer_program->entryRequirements[0]->advance_must_subjects) != ''){
                                if(unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                        $other_advance_must_subject_ready = true;
                                    }

                                }else{
                                    if(in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_must_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];
                                    }
                                }
                            }elseif(unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                if(!in_array($result->subject_name, unserialize($transfer_program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                                }
                            }else{
                                $a_level_subsidiary_pass_count += 1;
                                $a_level_points += $a_level_grades[$result->grade];
                            }
                        }
                    }
                }
            }

            if(unserialize($transfer_program->entryRequirements[0]->must_subjects) != ''){
                if(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $transfer_program->entryRequirements[0]->principle_pass_points){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $a_level_selection_points[$transfer_program->id] = $a_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                }
            }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $transfer_program->entryRequirements[0]->principle_pass_points){

                $qualifies = true;
                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $a_level_selection_points[$transfer_program->id] = $a_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;

            } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                $qualifies = true;
                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $a_level_selection_points[$transfer_program->id] = $a_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
            }

            $has_major = false;
            $equivalent_must_subjects_count = 0;
            $diploma_gpa = null;
            $out_gpa = null;
            $has_nacte_results = false;

            foreach($applicant->nacteResultDetails as $detail){
                if(count($detail->results) == 0 && $detail->verified == 1){
                    $has_nacte_results = true;
                    $diploma_gpa = $detail->diploma_gpa;
                }
            }

            if(($o_level_pass_count + $o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $has_nacte_results && $diploma_gpa >= $transfer_program->entryRequirements[0]->equivalent_gpa){

                $qualifies = true;
                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
            }

            if(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) != ''){
                foreach($applicant->nacteResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) as $sub){
                            if(str_contains(strtolower($detail->programme),strtolower($sub))){

                                $has_major = true;
                            }
                        }
                        $diploma_gpa = $detail->diploma_gpa;
                    }
                }
                if(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                    foreach($applicant->nacteResultDetails as $detail){
                       if($detail->verified == 1){
                          foreach($detail->results as $result){
                             foreach(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                 if(str_contains(strtolower($result->subject),strtolower($sub))){
                                     $equivalent_must_subjects_count += 1;
                                 }
                             }
                          }
                       }
                    }
                }

            }else{
                if(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                    foreach($applicant->nacteResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach($detail->results as $result){
                            foreach(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                if(str_contains(strtolower($result->subject),strtolower($sub))){
                                    $equivalent_must_subjects_count += 1;
                                }
                            }
                        }
                        $diploma_gpa = $detail->diploma_gpa;
                    }
                    }
                }
            }

            if(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){
                if(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $has_major && $diploma_gpa >= $transfer_program->entryRequirements[0]->equivalent_gpa){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;

                }
            }elseif(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                if((($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects &&
                    $equivalent_must_subjects_count >= count(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects)) &&
                    $diploma_gpa >= $transfer_program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $transfer_program->entryRequirements[0]->pass_subjects &&
                    $applicant->avn_no_results === 1 && $diploma_gpa >= $transfer_program->entryRequirements[0]->equivalent_gpa)){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                }
            }

            $out_pass_subjects_count = 0;
            if(unserialize($transfer_program->entryRequirements[0]->open_exclude_subjects) != '') //['OFC 017','OFP 018','OFP 020'];
            {
                $exclude_out_subjects_codes = unserialize($transfer_program->entryRequirements[0]->open_exclude_subjects);

                foreach($applicant->outResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach($detail->results as $result){
                            if(!Util::arrayIsContainedInKey($result->subject_code, $exclude_out_subjects_codes)){
                                if($out_grades[$result->grade] >= $out_grades['C']){
                                    $out_pass_subjects_count += 1;
                                }
                            }
                        }
                        $out_gpa = $detail->gpa;
                    }
                }
            }else{
                foreach($applicant->outResultDetails as $detail){
                    if($detail->verified == 1){
                        foreach($detail->results as $key => $result){
                            if($out_grades[$result->grade] >= $out_grades['C']){
                                $out_pass_subjects_count += 1;
                            }
                        }
                        $out_gpa = $detail->gpa;
                    }
                }
            }

            if(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 &&
                    $a_level_out_principle_pass_count >= 1){

                $qualifies = true;
                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $a_level_selection_points[$transfer_program->id] = $a_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                $open_selection_grade[$transfer_program->id] = $out_gpa;
            }

            // OUT with diploma of 2.0 and above
            if(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects) != ''){
                if((($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($transfer_program->entryRequirements[0]->equivalent_must_subjects)) &&
                $diploma_gpa >= $transfer_program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $transfer_program->entryRequirements[0]->pass_subjects &&
                $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa)){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $a_level_selection_points[$transfer_program->id] = $a_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                    $open_selection_grade[$transfer_program->id] = $out_gpa;

                }
            }elseif(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) != ''){
                if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa && $has_major &&
                    $diploma_gpa >= $transfer_program->entryRequirements[0]->min_equivalent_gpa){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $a_level_selection_points[$transfer_program->id] = $a_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                    $open_selection_grade[$transfer_program->id] = $out_gpa;
                }
            }elseif(unserialize($transfer_program->entryRequirements[0]->equivalent_majors) == ''){
                if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa &&
                    $diploma_gpa >= $transfer_program->entryRequirements[0]->min_equivalent_gpa){

                    $qualifies = true;
                    $o_level_selection_points[$transfer_program->id] = $o_level_points;
                    $a_level_selection_points[$transfer_program->id] = $a_level_points;
                    $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                    $open_selection_grade[$transfer_program->id] = $out_gpa;
                }
            }

            if(($o_level_pass_count+$o_level_other_pass_count) >= $transfer_program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                    $out_gpa >= $transfer_program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){

                $qualifies = true;
                $o_level_selection_points[$transfer_program->id] = $o_level_points;
                $a_level_selection_points[$transfer_program->id] = $a_level_points;
                $diploma_selection_grade[$transfer_program->id] = $diploma_gpa;
                $open_selection_grade[$transfer_program->id] = $out_gpa;
            }
        }

        if(!$qualifies){
            return redirect()->back()->with('error','Applicant does not qualify for transfer');            
        }














        // $transfer_program_code = $transfer_program->regulator_code;

        // $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        // $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];


        //    $index_number = $applicant->index_number;
        //    $exam_year = explode('/', $index_number)[2];

        //    foreach($applicant->nectaResultDetails as $detail) {
        //       if($detail->exam_id == 2){
        //           $index_number = $detail->index_number;
        //           $exam_year = explode('/', $index_number)[2];
        //       }
        //    }

        //    if($exam_year < 2014 || $exam_year > 2015){
        //      $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
        //    }else{
        //      $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'S'=>0.5,'F'=>0];
        //    }
        //    $subject_count = 0;
        //    $program = $transfer_program;
        //    foreach($applicant->selections as $selection){

        //         if($program->id == $selection->campus_program_id){

        //           if(count($program->entryRequirements) == 0){
        //             return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
        //           }

        //           if($program->entryRequirements[0]->max_capacity == null){
        //             return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
        //           }

        //            // Certificate
        //            if(str_contains($award->name,'Certificate')){
        //                $o_level_pass_count = 0;
        //                foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
        //                  if($detail->exam_id == 1){
        //                    foreach ($detail->results as $key => $result) {

        //                       if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

        //                         $applicant->rank_points += $o_level_grades[$result->grade];
        //                         $subject_count += 1;

        //                          if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
        //                                  $o_level_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
        //                                  $o_level_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
        //                                  $o_level_pass_count += 1;
        //                             }
        //                          }else{
        //                             $o_level_pass_count += 1;
        //                          }
        //                       }
        //                    }
        //                  }
        //                  if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){

        //                  }else{
        //                     return redirect()->back()->with('error','Applicant does not qualify for transfer');
        //                  }
        //                }
        //            }

        //            // Diploma
        //            if(str_contains(strtoupper($award->name),'diploma')){	// added strtoupper lupi
        //                $o_level_pass_count = 0;
        //                $a_level_principle_pass_count = 0;
        //                $a_level_subsidiary_pass_count = 0;
        //                foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
        //                  if($detail->exam_id == 1){
        //                    foreach ($detail->results as $key => $result) {

        //                       if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

        //                         $applicant->rank_points += $o_level_grades[$result->grade];
        //                         $subject_count += 1;


        //                          if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) ||
		// 								  in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){ // This may result in logical error in case a specific number of must subject is required
		// 								$o_level_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
        //                                  $o_level_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
        //                                  $o_level_pass_count += 1;
        //                             }
        //                          }else{
        //                              $o_level_pass_count += 1;
        //                          }
        //                       }
        //                    }
        //                  }elseif($detail->exam_id == 2){
        //                    foreach ($detail->results as $key => $result) {

        //                       if($a_level_grades[$result->grade] >= $a_level_grades['E']){

        //                          $applicant->rank_points += $a_level_grades[$result->grade];
        //                          $subject_count += 1;
        //                          if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) ||
		// 								  in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                             }
        //                          }
        //                       }
        //                       if($a_level_grades[$result->grade] == $a_level_grades['S']){

        //                          if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) ||
		// 							      in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                             }
        //                          }
        //                       }
        //                    }
        //                  }

        //                  if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){

        //                  }else{
        //                     return redirect()->back()->with('error','Applicant does not qualify for transfer');
        //                  }


        //                }
        //                $has_btc = false;
        //                foreach($applicant->nacteResultDetails as $detailKey=>$detail){
        //                   if(str_contains(strtoupper($detail->programme),'BASIC TECHNICIAN CERTIFICATE')){   // added strtolower lupi
        //                       $has_btc = true;
        //                   }
        //                }

        //                if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){

        //                }else{
        //                   return redirect()->back()->with('error','Applicant does not qualify for transfer');
        //                }
        //            }

        //            // Bachelor
        //            if(str_contains($award->name,'Bachelor')){
        //                $o_level_pass_count = 0;
        //                $a_level_principle_pass_count = 0;
        //                $a_level_principle_pass_points = 0;
        //                $a_level_subsidiary_pass_count = 0;
        //                $diploma_pass_count = 0;

        //                foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
        //                  if($detail->exam_id == 1){
        //                    foreach ($detail->results as $key => $result) {

        //                       if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

        //                          $applicant->rank_points += $o_level_grades[$result->grade];
        //                          $subject_count += 1;

        //                          if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
        //                                  $o_level_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
        //                                  $o_level_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
        //                                  $o_level_pass_count += 1;
        //                             }
        //                          }else{
        //                               $o_level_pass_count += 1;
        //                          }
        //                       }
        //                    }
        //                  }elseif($detail->exam_id == 2){
        //                    foreach ($detail->results as $key => $result) {

        //                       if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){	// lupi modified to accomodate both 'E' and 'S'
        //                       // if($a_level_grades[$result->grade] >= $a_level_grades['E']){		original
        //                          $applicant->rank_points += $a_level_grades[$result->grade];
        //                          $subject_count += 1;
        //                          if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                                  $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                                  $a_level_principle_pass_points += $a_level_grades[$result->grade];
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                  $a_level_principle_pass_count += 1;
        //                             }
        //                          }
        //                       }
        //                       if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){	// lupi addopded condition from 6614 to account for 'E' subsidiary
		// 						//if($a_level_grades[$result->grade] >= $a_level_grades['S'])	original
        //                          if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
        //                             if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                                }
        //                             }else{
        //                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                                }
        //                             }
        //                          }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
        //                             if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
        //                                  $a_level_subsidiary_pass_count += 1;
        //                             }
        //                          }
        //                       }
        //                    }
        //                  }

        //                  if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2){ 					// lupi changed to skip the need for principle_pass_points
        //                  // if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){		original

        //                  }else{

        //                  }
        //                }

        //                foreach ($applicant->nacteResultDetails as $detailKey=>$detail) {
        //                  foreach ($detail->results as $key => $result) {
        //                       if($diploma_grades[$result->grade] >= $diploma_grades[$program->entryRequirements[0]->equivalent_average_grade]){
        //                          $diploma_pass_count += 1;
        //                       }
        //                    }
        //                  if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($diploma_pass_count >= $program->entryRequirements[0]->equivalent_pass_subjects || $detail->diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

        //                  }else{
        //                     return redirect()->back()->with('error','Applicant does not qualify for transfer');
        //                  }
        //                }
        //            }
        //         }
        //     }

        $admitted_program = null;
        foreach($applicant->selections as $selection){
            if($selection->status == 'SELECTED'){
                $admitted_program = $selection->campusProgram;
            }
        }

        $batch_id = 0;

        $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $applicant->application_window_id)
                                    ->where('program_level_id',$applicant->program_level_id)->latest()->first();
        if($batch->batch_no > 1){
            if(Applicant::whereHas('selections',function($query) use($applicant, $batch){$query->whereNotIn('status',['SELECTED','PENDING','APPROVING'])
                ->where('application_window_id',$applicant->application_window_id)
                ->where('batch_id',$batch->id);})
                ->where('application_window_id', $applicant->application_window_id)
                ->where('program_level_id',$applicant->program_level_id)->where('batch_id',$batch->id)->count() >  0){
                        $batch_id = $batch->id;

                    }else{

                $previous_batch = null;
                if($batch->batch_no > 1){
                    $previous_batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)
                                                        ->where('batch_no', $batch->batch_no - 1)->first();
                    $batch_id = $previous_batch->id;
                }
            }
        }else{
            $batch_id = $batch->id;
        }

        ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','SELECTED')->update(['status'=>'ELIGIBLE']);

        $select = new ApplicantProgramSelection;
        $select->applicant_id = $applicant->id;
        $select->campus_program_id = $transfer_program->id;
        $select->application_window_id = $applicant->application_window_id;
        $select->order = 5;
        $select->batch_id = $batch_id;
        $select->status = 'SELECTED';
        $select->save();

        $selection = ApplicantProgramSelection::with(['campusProgram.program','campusProgram.entryRequirements'=>function($query){$query->orderBy('id','desc');}])
                                              ->where('applicant_id',$applicant->id)->where('status','SELECTED')->first();

        $semester = Semester::where('status','ACTIVE')->first();

        $reg_count = Registration::whereHas('student',function($query) use($transfer_program){$query->where('campus_program_id',$transfer_program->id);})
                                 ->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->where('year_of_study',1)->count();

        // if($selection->campusProgram->entryRequirements[0]->max_capacity < $reg_count){
        //     DB::rollback();
        //     return redirect()->back()->with('error','Programme does not have capacity to accomodate the transfer');
        // }

        $last_student = DB::table('students')->select(DB::raw('MAX(REVERSE(SUBSTRING(REVERSE(registration_number),1,7))) AS last_number'))->where('campus_program_id',$transfer_program->id)->first();
            
        if(!empty($last_student->last_number)){
            $code = sprintf('%04d', substr($last_student->last_number, 0, 4) + 1);

        }else{
            $code = sprintf('%04d',1);
        }

        $year = substr(date('Y'), 2);
        $prog_code = $stud_group = explode('.', $transfer_program->code);
        $program_code = $prog_code[0].'.'.$prog_code[1];
        
        // if(str_contains($applicant->intake->name,'March')){
        //     if(!str_contains($applicant->campus->name,'Kivukoni')){
        //         $program_code = $prog_code[0].'Z3.'.$prog_code[1];
        //         $stud_group =  $applicant->program_level_id.'Z'.$transfer_program->id.$year;
        //     }else{
        //         $program_code = $prog_code[0].'3.'.$prog_code[1];
        //         $stud_group =  $applicant->program_level_id.$transfer_program->id.$year;
        //     }
        // }else{
        //     if(!str_contains($applicant->campus->name,'Kivukoni')){
        //         $program_code = $prog_code[0].'Z.'.$prog_code[1];
        //         $stud_group =  $applicant->program_level_id.'Z'.$transfer_program->id.$year;
        //     }else{
        //         $program_code = $prog_code[0].'.'.$prog_code[1];
        //         $stud_group =  $applicant->program_level_id.$transfer_program->id.$year;
        //     }
        // }




        if(str_contains($applicant->intake->name,'March')){

            if(str_contains($applicant->campus->name,'Kivukoni')){
				$program_code = $prog_code[0].'3.'.$prog_code[1];

                if (str_contains(strtolower($transfer_program->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'3';

                } elseif (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'3';

                }

            } elseif (str_contains($applicant->campus->name,'Karume')) {

				$program_code = $prog_code[0].'3.'.$prog_code[1];

                if (str_contains(strtolower($transfer_program->program->name), 'diploma')) {

					$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z3';

				} elseif (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {

					$stud_group = 'C'.$stud_group[1].'Z3';

				}
            }  elseif (str_contains($applicant->campus->name,'Pemba')) {

                $program_code = $prog_code[0].'3.'.$prog_code[1];

                if (str_contains(strtolower($transfer_program->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P3';

                } elseif (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'P3';

                }
            }

        }else{
            // september intake
            if(str_contains($applicant->campus->name,'Karume')){

                $program_code = $prog_code[0].'9.'.$prog_code[1];

                if (str_contains(strtolower($transfer_program->program->name), 'bachelor')) {
					$program_code = $prog_code[0].'.'.$prog_code[1];
                    if (str_contains($transfer_program->program->name, 'Leadership') && str_contains($transfer_program->program->name, 'Governance')) {

                        $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                    } elseif (str_contains($transfer_program->program->name, 'Procurement') && str_contains($transfer_program->program->name, 'Supply')) {

                        $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'Z';

                    } else {

                        $stud_group =$stud_group[0].$stud_group[1];

                    }

                } else if (str_contains(strtolower($transfer_program->program->name), 'diploma')) {

                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z9';

                } else if (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {

                    $stud_group = 'C'.$stud_group[1].'Z9';

                }
            } elseif (str_contains($applicant->campus->name,'Kivukoni')) {
                $stud_group = $stud_group[0].$stud_group[1];
                if (str_contains(strtolower($transfer_program->program->name), 'bachelor')) {

                    if (str_contains(strtolower($transfer_program->program->name), 'human') && str_contains(strtolower($transfer_program->program->name), 'resource')) {

                        $stud_group = substr($stud_group[0], 0, 1).$stud_group[1];

                    }

                } elseif (str_contains(strtolower($transfer_program->program->name), 'diploma')) {
					$program_code = $prog_code[0].'9.'.$prog_code[1];
					$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'9';

                } elseif (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {
					$program_code = $prog_code[0].'9.'.$prog_code[1];
                    $stud_group = 'C'.$stud_group[1];
                }

            } elseif (str_contains($applicant->campus->name,'Pemba')) {
				$program_code = $prog_code[0].'9.'.$prog_code[1];

                if (str_contains(strtolower($transfer_program->program->name), 'bachelor')) {
					$program_code = $prog_code[0].'.'.$prog_code[1];

                    $stud_group = substr($stud_group[0], 0, 2).$stud_group[1].'P';

                } elseif (str_contains(strtolower($transfer_program->program->name), 'diploma')) {
                    $stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P9';

                } elseif (str_contains(strtolower($transfer_program->program->name), 'basic') && str_contains(strtolower($transfer_program->program->name), 'technician')) {
                    $stud_group = 'C'.$stud_group[1].'P9';

                }

            }
        }

        $password = User::find($applicant->user_id)->password;

        $transfer = new InternalTransfer;
        $transfer->student_id = $student->id;
        $transfer->previous_campus_program_id = $admitted_program->id;
        $transfer->current_campus_program_id = $transfer_program->id;
        $transfer->transfered_by_user_id = Auth::user()->id;

        $student = Student::find($student->id);
        $student->registration_number = 'MNMA/'.$program_code.'/'.$code.'/'.$year;
        $student->campus_program_id = $transfer_program->id;

        $user = new User;
        $user->username = $student->registration_number;
        $user->email = $student->email;
        $user->password = $password;
        $user->save();

        $last_user = User::find($applicant->user_id);
        $last_user->status = 'INACTIVE';
        $last_user->save();

        $role = Role::where('name','student')->first();
        $user->roles()->sync([$role->id]);


        $student->user_id = $user->id;
        $student->save();
        $transfer->save();

        $old_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->where('campus_program_id',$admitted_program->id)->first();
        if(!$old_program_fee){
            return redirect()->back()->with('error','Tution fee for previous programme not defined.');
        }
        $new_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->where('campus_program_id',$transfer_program->id)->first();
        if(!$new_program_fee){
            return redirect()->back()->with('error','Tuition fee for new programme not defined.');
        }
        
        $usd_currency = Currency::where('code','USD')->first();

        $fee_diff = $new_program_fee->amount_in_tzs - $old_program_fee->amount_in_tzs;

        if($fee_diff > 0){
            if(str_contains($student->applicant->nationality,'Tanzania')){
                $fee_amount = $new_program_fee->amount_in_tzs;
            }else{
                $fee_amount = $new_program_fee->amount_in_usd*$usd_currency->factor;
            }

            $invoice = new Invoice;
            $invoice->reference_no = 'MNMA-TF-'.time();
            $invoice->actual_amount = $fee_amount;
            $invoice->amount = $fee_diff;
            $invoice->currency = 'TZS';
            $invoice->payable_id = $student->id;
            $invoice->payable_type = 'student';
            $invoice->applicable_id = $ac_year->id;
            $invoice->applicable_type = 'academic_year';
            $invoice->fee_type_id = $new_program_fee->feeItem->feeType->id;
            $invoice->save();


            $generated_by = 'SP';
            $approved_by = 'SP';
            $inst_id = config('constants.SUBSPCODE');

            $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
            $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

            $number_filter = preg_replace('/[^0-9]/','',$student->email);
            $payer_email = empty($number_filter)? $student->email : 'admission@mnma.ac.tz';

            $result = $this->requestControlNumber($request,
                                        $invoice->reference_no,
                                        $inst_id,
                                        $invoice->amount,
                                        $new_program_fee->feeItem->feeType->description,
                                        $new_program_fee->feeItem->feeType->gfs_code,
                                        $new_program_fee->feeItem->feeType->payment_option,
                                        $student->id,
                                        $first_name.' '.$surname,
                                        $student->phone,
                                        $payer_email,
                                        $generated_by,
                                        $approved_by,
                                        $new_program_fee->feeItem->feeType->duration,
                                        $invoice->currency);
        }

        if(!str_contains(strtolower($admitted_program->program->name),'education') && str_contains(strtolower($selection->campusProgram->program->name),'education')){
            $teaching_practice = FeeAmount::where('study_academic_year_id',$ac_year->id)->where('campus_id',$applicant->campus_id)
                                          ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                          ->where('name','LIKE','%Teaching%')->where('name','LIKE','%Practice%');})
                                          ->with(['feeItem.feeType'])->first();

            if(str_contains($student->applicant->nationality,'Tanzania')){
                $amount = $teaching_practice->amount_in_tzs;
            }else{
                $amount = $teaching_practice->amount_in_usd*$usd_currency->factor;
            }

            $invoice = new Invoice;
            $invoice->reference_no = 'MNMA-TP-'.time();
            $invoice->actual_amount = $amount;
            $invoice->amount = $amount;
            $invoice->currency = 'TZS';
            $invoice->payable_id = $student->id;
            $invoice->payable_type = 'student';
            $invoice->applicable_id = $ac_year->id;
            $invoice->applicable_type = 'academic_year';
            $invoice->fee_type_id = $teaching_practice->feeItem->feeType->id;
            $invoice->save();

            $generated_by = 'SP';
            $approved_by = 'SP';
            $inst_id = config('constants.SUBSPCODE');

            $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
            $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

            $number_filter = preg_replace('/[^0-9]/','',$student->email);
            $email = empty($number_filter)? $student->email : 'admission@mnma.ac.tz';
            $result = $this->requestControlNumber($request,
                                        $invoice->reference_no,
                                        $inst_id,
                                        $invoice->amount,
                                        $teaching_practice->feeItem->feeType->description,
                                        $teaching_practice->feeItem->feeType->gfs_code,
                                        $teaching_practice->feeItem->feeType->payment_option,
                                        $student->id,
                                        $first_name.' '.$surname,
                                        $student->phone,
                                        $email,
                                        $generated_by,
                                        $approved_by,
                                        $teaching_practice->feeItem->feeType->duration,
                                        $invoice->currency);
        
        }

        $reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->where('year_of_study',1)->first();
        $stream = Stream::where('campus_program_id',$selection->campusProgram->id)->where('study_academic_year_id',$ac_year->id)->first();
        if($stream){
            $reg->stream_id = $stream->id;
            $group = Group::where('stream_id',$stream->id)->first();
            if($group){
                $reg->group_id = $group->id;
            }else{
                $reg->group_id = 0;
            }
        }else{
            $reg->stream_id = 0;
        }
        $reg->save();

        // $tuition_invoice = Invoice::whereHas('feeType',function($query){ $query->where('name','LIKE','%Tuition%');})
        //                           ->with(['gatewayPayment','feeType'])->where('payable_type','student')->where('payable_id',$student->id)->first();

        // $misc_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Miscellaneous%');})
        //                        ->with(['gatewayPayment','feeType'])->where('payable_type','student')->where('payable_id',$student->id)->first();

        $student_invoices = Invoice::with(['feeType'])->where('payable_type','student')->where('payable_id',$student->id)->get();

        $usd_currency = Currency::where('code','USD')->first();

        $acpac = new ACPACService;
        $stud_name = $student->surname.', '.$student->first_name.' '.$student->middle_name;

        $stud_reg = substr($student->registration_number, 5);
        $stud_reg = str_replace('/', '', $stud_reg);

        $parts = explode('.', $stud_reg);
        if(str_contains($parts[0], 'BTC')){
            $stud_reg = 'C'.$parts[1];
        }else{
            $stud_reg = $parts[0].$parts[1];
        }

        $next_of_kin = $applicant->nextOfKin->surname.', '.$applicant->nextOfKin->first_name.' '.$applicant->nextOfKin->middle_name;
        $next_of_kin_email = $applicant->nextOfKin->email? $applicant->nextOfKin->email : 'UNKNOWN';

        $acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,
                                            CODECURN,EMAIL1,EMAIL2) VALUES ('".$stud_reg."','".$stud_group."','".$stud_name."','".$applicant->address."','".$applicant->district->name."',
                                            '".$applicant->ward->name."','".$applicant->street."','".$applicant->region->name."','".$applicant->country->name."','".$applicant->address."',
                                            '".$applicant->country->name."','".$next_of_kin."','".$applicant->phone."','".$applicant->nextOfKin->phone."','".$program_code."','STD','TSH',
                                            '".$applicant->email."','".$next_of_kin_email."')");
        $bill_ids = [];
        foreach($student_invoices as $invoice){
            $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$invoice->control_no."',
                                                '".date('Y',strtotime($invoice->created_at))."','".$invoice->feeType->description."','".$stud_reg."','".$stud_name."','1',
                                                '".$invoice->feeType->gl_code."','".$invoice->feeType->name."','".$invoice->feeType->description."','".$invoice->amount."','0',
                                                '".date('Y',strtotime(now()))."')");

            $bill_ids[] = $invoice->reference_no;
        }
        


        // if(str_contains($applicant->programLevel->name,'Bachelor')){
        //     $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){$query->where('name','LIKE','%TCU%');})
        //                                       ->where('study_academic_year_id',$ac_year->id)
        //                                       ->with(['feeItem.feeType'])->first();
        // }else{
        //     $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){$query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');})
        //                                       ->where('study_academic_year_id',$ac_year->id)
        //                                       ->with(['feeItem.feeType'])->first();
        // }

        // $other_fees = FeeAmount::whereHas('feeItem',function($query){
        //         $query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','LIKE','%Quality%')->where('name','NOT LIKE','%TCU%');
        //     })->with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->get();

        // if(str_contains($applicant->nationality,'Tanzania')){
        //     $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
        //                                         '".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
        //                                         '".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."',
        //                                         '".$quality_assurance_fee->feeItem->feeType->description."','".$quality_assurance_fee->amount_in_tzs."','0','".date('Y',strtotime(now()))."')");

        //     foreach ($other_fees as $fee) {
        //         $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
        //                                             '".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
        //                                             '".$fee->feeItem->feeType->gl_code."','".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."','".$fee->amount_in_tzs."','0',
        //                                             '".date('Y',strtotime(now()))."')");
        //     }
        // }else{
        //     $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
        //                                         '".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
        //                                         '".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."',
        //                                         '".$quality_assurance_fee->feeItem->feeType->description."','".($quality_assurance_fee->amount_in_usd*$usd_currency->factor)."','0','".date('Y',strtotime(now()))."')");

        //     foreach ($other_fees as $fee) {
        //         $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."',
        //                                             '".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1',
        //                                             '".$fee->feeItem->feeType->gl_code."','".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."',
        //                                             '".($fee->amount_in_usd*$usd_currency->factor)."','0','".date('Y',strtotime(now()))."')");
        //     }
        // }

        $student_receipts = GatewayPayment::whereIn('bill_id',$bill_ids)->get();

        foreach($student_receipts as $receipt){
            if($receipt->psp_name == 'National Microfinance Bank'){
                $bank_code = 619;
                $bank_name = 'NMB';
            }else{
                $bank_code = 615;
                $bank_name = 'CRDB';
            }

            $invoice = Invoice::with(['feeType'])->where('reference_no',$receipt->bill_id)->first();

            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."',
                                                '".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$invoice->feeType->description."','".$stud_reg."',
                                                '".$stud_name."','".$receipt->control_no."','".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
        }
        
        // $misc_receipts = GatewayPayment::where('control_no',$misc_invoice->control_no)->get();

        // foreach ($misc_receipts as $receipt) {
        //     if($receipt->psp_name == 'National Microfinance Bank'){
        //         $bank_code = 619;
        //         $bank_name = 'NMB';
        //     }else{
        //         $bank_code = 615;
        //         $bank_name = 'CRDB';
        //     }

        //     $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."',
        //                                         '".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$misc_invoice->feeType->description."','".$stud_reg."','".$stud_name."',
        //                                         '".$receipt->control_no."','".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
        // }

        $acpac->close();
        $transfered_status = true;
        try{
            Mail::to($user)->send(new StudentAccountCreated($student, $selection->campusProgram->program->name,$ac_year->academicYear->year, $transfered_status));
            DB::commit();
        }catch(\Exception $e){}
        return redirect()->to('registration/internal-transfer')->with('message','Transfer completed successfully');
    }

	/**
	 * Internal transfers submission_complete_status
	 */
	 public function internalTransfersSubmission(Request $request)
	 {
        $staff = User::find(Auth::user()->id)->staff;
		$transfers = InternalTransfer::whereHas('student.applicant.programLevel',function($query){$query->where('name','LIKE','%Degree%');})
                                      ->whereHas('student.applicant',function($query)use($staff){$query->where('campus_id',$staff->campus_id);})
                                      ->with(['student.applicant.selections.campusProgram.program',
                                              'previousProgram',
                                              'student.applicant.nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                              'student.applicant.nacteResultDetails'=>function($query){$query->select('id','applicant_id','programme','avn')->where('verified',1);},
                                              'currentProgram'])
                                      ->where('status','PENDING')->get();
                                      
        $tcu_username = $tcu_token = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        if(count($transfers) > 0){
            foreach($transfers as $transfer){
                $f6indexno = null;
                foreach($transfer->student->applicant->nectaResultDetails as $detail) {
                    if($detail->exam_id == 2){
                        $f6indexno = $detail->index_number;
                        break;
                    }
                }

                foreach($transfer->student->applicant->nacteResultDetails as $detail){
                    if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                        $f6indexno = $detail->avn;
                        break;
                    }
                }
    
                $url = 'http://api.tcu.go.tz/admission/submitInternalTransfers';
                $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                                <Request>
                                <UsernameToken>
                                    <Username>'.$tcu_username.'</Username>
                                    <SessionToken>'.$tcu_token.'</SessionToken>
                                </UsernameToken>
                                <RequestParameters>
                                <f4indexno>'.$transfer->student->applicant->index_number.'</f4indexno>
                                <f6indexno>'.$f6indexno.'</f6indexno>
                                <Gender>'.$transfer->student->applicant->gender.'</ Gender >
                                <CurrentProgrammeCode>'.$transfer->currentProgram->regulator_code.'</CurrentProgrammeCode>
                                <PreviousProgrammeCode>'.$transfer->previousProgram->regulator_code.'</PreviousProgrammeCode>
                                </RequestParameters>
                                </Request>';

                $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                $json = json_encode($xml_response);
                $array = json_decode($json,TRUE);
    
                if($array['Response']['ResponseParameters']['StatusCode'] == 200){
                    $trans = InternalTransfer::find($transfer->id);
                    $trans->status = 'SUBMITTED';
                    $trans->save();
                }else{
                    $error_log = new ApplicantFeedBackCorrection;
                    $error_log->applicant_id = $transfer->student->applicant->id;
                    $error_log->application_window_id = $transfer->student->applicant->application_window_id;
                    $error_log->programme_id = null;
                    $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                    $error_log->remarks = $array['Response']['ResponseParameters']['StatusDescription'];
                    $error_log->save();
                }
            }
            return redirect()->back()->with('message','Transfers submitted successfully');
        }else{
            return redirect()->back()->with('error','No Bachelor Degree transfers to be submitted.');
        }
	 }

    /**
     * Submit external transfer
     */
    public function submitExternalTransfer(Request $request)
    { 
        $staff = User::find(Auth::user()->id)->staff;
		$transfers = ExternalTransfer::whereHas('applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})->where('status','ELIGIBLE')->get();
        
        $tcu_username = $tcu_token = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');

        }

        foreach($transfers as $trans){
			if($request->get('transfer_'.$trans->id) == $trans->id){
                $applicant = Applicant::select('id','index_number','gender','application_window_id')
                                        ->with(['selections'=>function($query){$query->select('id','applicant_id','campus_program_id')->where('status','SELECTED');},
                                                'selections.campusProgram:id,code,regulator_code',
                                                'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                                'nacteResultDetails'=>function($query){$query->select('id','applicant_id','programme','avn')->where('verified',1);}])
                                        ->where('campus_id',$staff->campus_id)->find($trans->applicant_id);

                $f6indexno = null;
                foreach($applicant->nectaResultDetails as $detail) {
                    if($detail->exam_id == 2){
                        $f6indexno = $detail->index_number;
                        break;
                    }
                }

                foreach($applicant->nacteResultDetails as $detail){
                    if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                        $f6indexno = $detail->avn;
                        break;
                    }
                }

                $url = 'http://api.tcu.go.tz/admission/submitInterInstitutionalTransfers';
                $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                                <Request>
                                <UsernameToken>
                                    <Username>'.$tcu_username.'</Username>
                                    <SessionToken>'.$tcu_token.'</SessionToken>
                                </UsernameToken>
                                <RequestParameters>
                                <f4indexno>'.$applicant->index_number.'</f4indexno>
                                <f6indexno>'.$f6indexno.'</f6indexno>
                                <Gender>'.$applicant->gender.'</ Gender >
                                <CurrentProgrammeCode>'.$applicant->selections[0]->campusProgram->regulator_code.'</CurrentProgrammeCode>
                                <PreviousProgrammeCode>'.$trans->previous_program.'</PreviousProgrammeCode>
                                </RequestParameters>
                                </Request>';

                $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                $json = json_encode($xml_response);
                $array = json_decode($json,TRUE);

                if($array['Response']['ResponseParameters']['StatusCode'] == 200){

                    $applicant->confirmation_status = 'SUBMITTED';
                    $applicant->save();

                    $transfer = ExternalTransfer::find($trans->id);
                    $transfer->status = 'SUBMITTED';
                    $transfer->save();

                }else{
                    $error_log = new ApplicantFeedBackCorrection;
                    $error_log->applicant_id = $applicant->id;
                    $error_log->application_window_id = $applicant->application_window_id;
                    $error_log->programme_id = null;
                    $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                    $error_log->remarks = $array['Response']['ResponseParameters']['StatusDescription'];
                    $error_log->save();
                }
            }
		}
		return redirect()->back()->with('message','External transfers submitted successfully');
    }

    /**
     * Get verified students from NACTE
     */
    public function getVerifiedApplicantsNACTVET(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

        $campus_programs = CampusProgram::whereHas('program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));
        })->get();
        $intake = ApplicationWindow::find($request->get('application_window_id'))->intake;

        $verification_key = null;
        if($staff->campus_id==1){
            $verification_key = config('constants.NACTVET_VERIFICATION_KEY_KIVUKONI');

        }elseif($staff->campus_id==2){
            $verification_key = config('constants.NACTVET_VERIFICATION_KEY_KARUME');

        }elseif($staff->campus_id==3){
            $verification_key = config('constants.NACTVET_VERIFICATION_KEY_PEMBA');

        }else{
            return redirect()->back()->with('message','campus key is unknown');
        }
        foreach($campus_programs as $program){
            $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/verificationresults/'.$program->regulator_code.'-'.date('Y').'-'.$intake->name.'/'.$verification_key);
            if($result['code'] == 200){
                $no_of_applicants = 0;
                foreach ($result['params'] as $res) {
                    //if(str_contains(strtolower($res['verification_status'].'approved')){
                if(Applicant::where('index_number',$res['username'])
                            ->whereHas('selections', function($query){ $query->whereIn('status',['APPROVING','PENDING']);})
                            ->where('application_window_id', $request->get('application_window_id'))
                            ->where('program_level_id',$request->get('program_level_id'))
                            ->where('status', 'SUBMITTED')->count() > 0) {

                                $applicant = Applicant::where('index_number',$res['username'])
                                        ->whereHas('selections', function($query){ $query->whereIn('status',['APPROVING','PENDING']);})
                                        ->where('application_window_id', $request->get('application_window_id'))
                                        ->where('program_level_id',$request->get('program_level_id'))
                                        ->where('status', 'SUBMITTED')->latest()->first();

                                if($applicant){
                                $applicant->multiple_admissions = $res['multiple_selection'] == 'no multiple'? 0 : 1;
                                $applicant->save();

                                ApplicantProgramSelection::where('applicant_id',$applicant->id)->whereIn('status',['APPROVING','PENDING'])->update(['status'=>'SELECTED']);
                                $no_of_applicants++;
                                }
                            }else {
                                continue;
                            }

                }
            }else{
                return redirect()->back()->with('message','No applicants submitted to nactvet');
            }
        }
            return redirect()->back()->with('message','Verified applicants retrieved successfully from NACTVET');

    }

    public function showRegulatorFailedCase(Request $request){
        $staff = User::find(Auth::user()->id)->staff;
        $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status', 'ACTIVE')->get();
        $data = [
            'applicants'=>[],
            'errors_status' => 0,
            'request' => $request,
            'windows' => $app_window,
            'failed_cases' => ['NACTVET FAILED CASES', 'TCU FAILED CASES'],
            'flag' => 'initial'
            ];
         return view('dashboard.application.nactvet-failed-submissions',$data)->withTitle('Regulator Failed Cases');
    }

    public function getSelectedRegulatorFailedCase(Request $request){
        if($request->get('regulator_case') == 'NACTVET FAILED CASES'){
            return redirect()->to('application/nactvet-error-cases?application_window_id='.session("active_window_id").'&campus_id='.session("staff_campus_id"));
        }elseif($request->get('regulator_case') == 'TCU FAILED CASES'){
            return redirect()->to('application/tcu-failed-cases?application_window_id='.session("active_window_id").'&campus_id='.session("staff_campus_id"))->with('case', 'tcu');
        }
    }

    public function showTCUFeedbackCorrectionList(Request $request){
        
        $errors = ApplicantFeedBackCorrection::where('application_window_id',$request->get('application_window_id'))->where('status',null)->count();
        return $errors;
        $staff = User::find(Auth::user()->id)->staff;
        $applicants =  DB::table('applicants as a')->select(DB::raw('a.id,first_name,middle_name,surname,index_number,gender,phone,a.program_level_id'))
                           ->where('a.campus_id',$staff->campus_id)
                           ->where('a.application_window_id', $request->get('application_window_id'))->where('is_edited', 1)->get();
        $data = [
        'applicants'=>$applicants,
        'errors_status' => $errors,
        'awards' => Award::all(),
        'request' => $request,
        'flag' => 'TCU'
        ];
        return view('dashboard.application.nactvet-failed-submissions',$data)->withTitle('TCU Correction List');
    }

    public function resubmitTCUCorrectionList(Request $request){

        $staff = User::find(Auth::user()->id)->staff;
        $tcu_username = $tcu_token = $nactvet_authorization_key = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');
        }

        $countApp = 0;
        foreach($request->get('applicant_ids') as $app_id){
            $applicant = Applicant::find($app_id);

                $submission_log = ApplicantSubmissionLog::where('applicant_id',$applicant->id)->where('program_level_id',$applicant->program_level_id)
                            ->where('application_window_id',$applicant->application_window_id)->where('batch_id',$applicant->batch_id)->first();
                if(!empty($submission_log)){

                    $url='http://api.tcu.go.tz/applicants/resubmit';

                    $selected_programs = array();
                    $approving_selection = null;

                    foreach($applicant->selections as $selection){
                        $selected_programs[] = $selection->campusProgram->regulator_code;
                        if($selection->status == 'APPROVING' || $selection->status == 'SELECTED'){
                            $approving_selection = $selection;
                        }
                    }

                    $f6indexno = null;
                    $otherf4indexno = $otherf6indexno = [];
                    foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 2){
                            if($f6indexno != null && $f6indexno != $detail->index_number){
                                $otherf6indexno[] = $detail->index_number;
                            }else{
                                $f6indexno = $detail->index_number;
                            }
                        }
                    }

                    foreach($applicant->nacteResultDetails as $detail){
                        if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                            $f6indexno = $detail->avn;
                            break;
                        }
                    }

                    foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 1 && $detail->index_number != $applicant->index_number){
                            $otherf4indexno[]= $detail->index_number;
                        }
                    }

                    if(is_array($selected_programs)){
                        $selected_programs=implode(', ',$selected_programs);
                    }

                    if(is_array($otherf4indexno)){
                        $otherf4indexno=implode(', ',$otherf4indexno);
                    }

                    if(is_array($otherf6indexno)){
                        $otherf6indexno=implode(', ',$otherf6indexno);
                    }

                    $category = null;
                    if($applicant->entry_mode == 'DIRECT'){
                        $category = 'A';

                    }else{
                        // Open university
                        if($applicant->outResultDetails){
                            $category = 'F';

                        }

                        // Diploma holders
                        if($applicant->nacteResultDetails){
                            $category = 'D';

                        }
                    }

                    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                    <Request>
                    <UsernameToken>
                    <Username>'.$tcu_username.'</Username>
                    <SessionToken>'.$tcu_token.'</SessionToken>
                    </UsernameToken>
                    <RequestParameters>
                    <f4indexno>'.$applicant->index_number.'</f4indexno>
                    <f6indexno>'.$f6indexno.'</f6indexno>
                    <Gender>'.$applicant->gender.'</Gender>
                    <SelectedProgrammes>'.$selected_programs.'</SelectedProgrammes>
                    <MobileNumber>'.str_replace('255', '0', $applicant->phone).'</MobileNumber>
                    <OtherMobileNumber></OtherMobileNumber>
                    <EmailAddress>'.$applicant->email.'</EmailAddress>
                    <AdmissionStatus>Provisional admission</AdmissionStatus>
                    <ProgrammeAdmitted>'.$approving_selection->campusProgram->regulator_code.'</ProgrammeAdmitted>
                    <Category>'.$category.'</Category>
                    <Reason>eligible</Reason>
                    <Nationality>'.$applicant->nationality.'</Nationality>
                    <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                    <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                    <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
                    <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
                    </RequestParameters>
                    </Request>';

                    //return $xml_request;
                    $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                    $json = json_encode($xml_response);
                    $array = json_decode($json,TRUE);

                    if($array['Response']['ResponseParameters']['StatusCode'] == 200){
                    Applicant::where('id',$applicant->id)->update(['status'=>'SUBMITTED']);

                    $submission_log->submitted = 2;
                    $submission_log->save();

                    $applicant->is_edited = 2;
                    $applicant->save();

                    $countApp++;

                    }else{
                    $error_log = new ApplicantFeedBackCorrection;
                    $error_log->applicant_id = $applicant->id;
                    $error_log->application_window_id = $applicant->application_window_id;
                    $error_log->programme_id = $approving_selection? $approving_selection->campusProgram->regulator_code : 'BD';
                    $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                    $error_log->remarks = $array['Response']['ResponseParameters']['StatusDescription'];
                    $error_log->save();
                    }
                }
        }

        return redirect()->back()->with('message', $countApp.'have been successfully resubmitted to TCU');
    }


    /**
     * Get applicants submitted to NACTVET with errors
     */
    public function showNACTVETFeedbackCorrectionList(Request $request)
    {
        $errors = ApplicantFeedBackCorrection::where('application_window_id',$request->get('application_window_id'))->where('status',null)->whereNotNull('verification_id')->count();
        $staff = User::find(Auth::user()->id)->staff;
        $applicants =  DB::table('applicants as a')->select(DB::raw('a.id,first_name,middle_name,surname,index_number,gender,phone,a.program_level_id,b.verification_id,b.remarks,b.status as submission_status'))
                            ->join('applicant_nacte_feedback_corrections as b','a.id','=','b.applicant_id')->whereNotNull('verification_id')->where('a.campus_id',$staff->campus_id)
                           ->where('b.application_window_id', $request->get('application_window_id'))->orderBy('b.status','ASC')->get();

        $data = [
        'applicants'=>$applicants,
        'errors_status' => $errors,
        'awards' => Award::all(),
        'campus_programs' => CampusProgram::where('campus_id',$request->get('campus_id'))->get(),
        'request' => $request,
        'flag' => 'NACTVET'
        ];
        return view('dashboard.application.nactvet-failed-submissions',$data)->withTitle('NACTVET Failed Cases');
    }



    public function getNACTVETFeedbackCorrectionList(Request $request){

        $campus_program = CampusProgram::select('id','regulator_code')->where('id',$request->get('campus_program_id'))->first();

        $intake = ApplicationWindow::find(session('active_window_id'))->intake;

        $nacte_get_feedbackcorrection_key=null;
        if(session('staff_campus_id') == 1){
            //get verification results for kivukoni campus
            $nacte_get_feedbackcorrection_key='cd30b814dfefeba5.280d4beb0ab4d4a76523fbf8fad180ec77101b0d5a07584b7ca955763104e652.d3c2a28064d439e42cf0e03bb8531caf0fda5c38';
        }elseif(session('staff_campus_id') == 2){
            //get verification results for karume campus
            $nacte_get_feedbackcorrection_key='bca6e756c0e90f43.1ce421f7a7c9ceabbd7f7afbeba8670b94e69293ed7e63e677c277a94155f889.b0b23a2d56127336598a601b7950190f264452e9';
        }elseif(session('staff_campus_id') == 3){
            //get verification results for pemba campus
            $nacte_get_feedbackcorrection_key='EFa0412170f86b54.e644abed80536c1219d8149010448eecd00b45f78ff70d7f52060adfe126f626.0c2a7b9c30ce3fc82a22cbb144c09e651d2d41e4';
        }else{
            return redirect()->back()->with('message','campus key is unknown');
        }

        $result = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/feedbackcorrection/'.$campus_program->regulator_code.'-'.date('Y').'-'.$intake->name.'/'.$nacte_get_feedbackcorrection_key);

        if($result['code'] == 200){
            foreach ($result['params'] as $res) {
                $applicant = Applicant::select('id')->where('index_number',$res['form_four_indexnumber'])->where('campus_id',session('staff_campus_id'))
                                                    ->where('application_window_id',session('active_window_id'))->latest()->first();
                //save pushed list
                $applicantFeedBackCorrections = ApplicantFeedBackCorrection::where('applicant_id',$applicant->id)->first();
                if(!$applicantFeedBackCorrections){
                    $applicantFeedBackCorrections = new ApplicantFeedBackCorrection;
                    $applicantFeedBackCorrections->applicant_id = $applicant->id;
                    $applicantFeedBackCorrections->application_window_id = session('active_window_id');
                    $applicantFeedBackCorrections->verification_id = $res['student_verification_id'];
                    $applicantFeedBackCorrections->programme_id = $res['programme_id'];
                    $applicantFeedBackCorrections->remarks = substr($res['remarks'],0,-9);
                    $applicantFeedBackCorrections->save();

                }else{
                    $applicantFeedBackCorrections->verification_id = $res['student_verification_id'];
                    $applicantFeedBackCorrections->programme_id = $res['programme_id'];
                    $applicantFeedBackCorrections->remarks = substr($res['remarks'],0,-9);
                    $applicantFeedBackCorrections->save();

                }
            }
        }elseif($result['code'] == 404){
            return redirect()->back()->with('message','No data found from NACTVET');

        }else{
            return redirect()->back()->with('error','Error occured when sending request to NACTVET');
        }

        return redirect()->back()->with('message','Verified applicants retrieved successfully from NACTVET');

    }


    public function resubmitNACTVETCorrectionList(Request $request){
        $staff = User::find(Auth::user()->id)->staff;

        $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','email','intake_id','application_window_id', 'program_level_id')
                ->whereHas('selections',function($query){$query->where('status','APPROVING');})->whereIn('id',$request->get('applicant_ids'))->whereNotIn('status',['SUBMITTED','ADMITTED'])
                ->with(['selections:id,status,campus_program_id,applicant_id',
                        'selections.campusProgram:id,regulator_code,program_id','selections.campusProgram.program:id,nta_level_id',
                        'selections.campusProgram.program.ntaLevel:id,name',
                        'nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                        'nacteResultDetails'=>function($query){$query->select('id','applicant_id','registration_number','diploma_graduation_year','programme')
                        ->where('verified',1);},
                        'intake:id,name',
                        'outResultDetails'=>function($query){$query->select('id','applicant_id')->where('verified',1);}])
                        ->where('campus_id',$staff->campus_id)->get();

                $errors = ApplicantFeedBackCorrection::select('applicant_id','verification_id')->whereNotNull('verification_id')->whereNull('status')->get();

        $nactvet_authorization_key = null;

        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KIVUKONI');

        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KARUME');

        }elseif($staff->campus_id == 3){
            $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_PEMBA');
        }

        foreach($applicants as $applicant){

                $f6indexno = null;
                foreach ($applicant->nectaResultDetails as $detail) {
                    if($detail->exam_id == 2 && $detail->verified == 1){
                    $f6indexno = $detail->index_number;
                    }
                }

                $has_level5 = null;
                $nta4_reg_no = $nta4_graduation_year = $nta5_reg_no = $nta5_graduation_year = null;
                foreach($applicant->nacteResultDetails as $detail){
                    if(str_contains(strtolower($detail->programme),'basic')){
                        $nta4_reg_no = $detail->registration_number;
                        $nta4_graduation_year = $detail->diploma_graduation_year;

                    }elseif(str_contains(strtolower($detail->programme),'diploma')){
                        $nta5_reg_no = $detail->registration_number;
                        $nta5_graduation_year = $detail->diploma_graduation_year;

                        if($detail->diploma_gpa >= 2){
                            $has_level5 = true;
                        }
                    }
                }
                $selected_programs = array();
                $approving_selection = [];
                $regulator_programme_id = null;
                foreach($applicant->selections as $selection){
                    $selected_programs[] = $selection->campusProgram->regulator_code;
                    if($selection->status == 'APPROVING'){
                        $approving_selection = $selection;
                        $regulator_programme_id = $selection->campusProgram->regulator_code;
                        break;
                    }
                }
                // dd($approving_selection);
                $level = null;
                $string = $approving_selection->campusProgram->program->ntaLevel->name;
                if($has_level5 || $applicant->program_level_id == 1){
                    $last_character = (strlen($string) - 1);
                    $level = substr($string, $last_character);
                }else {
                    $last_character = (strlen($string) - 1);
                    $level = substr($string, $last_character) - 1;
                }


                $f4indexno = $f4_exam_year = null;
                if(str_contains(strtolower($applicant->index_number),'eq')){
                    $f4_exam_year = explode('/',$applicant->index_number)[1];
                    $f4indexno = explode('/',$applicant->index_number)[0];
                }else{
                    $f4_exam_year = explode('/', $applicant->index_number)[2];
                    $f4indexno = explode('/',$applicant->index_number)[0].'/'.explode('/',$applicant->index_number)[1];
                }

                $f6_exam_year = null;
                if(!empty($f6indexno)){
                    if(str_contains(strtolower($f6indexno),'eq')){
                        $f6_exam_year = explode('/',$f6indexno)[1];
                        $f6indexno = explode('/',$f6indexno)[0];
                    }else{
                        $f6_exam_year = explode('/', $f6indexno)[2];
                        $f6indexno = explode('/',$f6indexno)[0].'/'.explode('/',$f6indexno)[1];
                    }
                }

                $verification_id = null;
                foreach($errors as $error){
                    if($error->applicant_id == $applicant->id){
                        $verification_id = $error->verification_id;
                        break;
                    }
                }

            $data = array(
                'heading' => array(
                    'authorization' => $nactvet_authorization_key,
                    'intake' => strtoupper($applicant->intake->name),
                    'programme_id' => $regulator_programme_id,
                    'academic_year' => date('Y'),
                    'level' => $level,
                ),
                'students' => array(
                    ['student' => array(
                        'student_verification_id' => $verification_id,
                        'firstname' => $applicant->first_name,
                        'secondname' => $applicant->middle_name != null? $applicant->middle_name : '',
                        'surname' => $applicant->surname,
                        'mobile_number' => '0'.substr($applicant->phone,3),
                        'email_address' => $applicant->email,
                        'form_four_indexnumber' => $f4indexno,
                        'form_four_year' => $f4_exam_year,
                        'form_six_indexnumber' => $f6indexno? $f6indexno : '',
                        'form_six_year' => $f6indexno? $f6_exam_year : '',
                        'NTA4_reg' => !empty($nta4_reg_no)? $nta4_reg_no : '',
                        'NTA4_grad_year' => !empty($nta4_graduation_year)? explode('/',$nta4_graduation_year)[1] : '',
                        'NTA5_reg' => !empty($nta5_reg_no)? $nta5_reg_no : '',
                        'NTA5_grad_year' => !empty($nta5_graduation_year)? explode('/',$nta5_graduation_year)[1] : '',

                    )
                    ],

                )
            );

               $url = 'https://www.nacte.go.tz/nacteapi/index.php/api/addcorrection';
               $ch = curl_init($url);

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
                $results = json_decode($result, true);
                $results ['code'];
                if($results['code']==200){
                    ApplicantFeedBackCorrection::where('applicant_id',$applicant->id)->update(['status'=>'RESUBMITTED']);
                }

            }
    }
    /**
     * Show Tamisemi applicants
     */
    public function tamisemiApplicants(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(240);


        $staff = User::find(Auth::user()->id)->staff;

        $applicants = [];

        $programs = [];

        $application_window = ApplicationWindow::select('id','intake_id','campus_id')->with('intake:name,id')->find($request->get('application_window_id'));


        if($request->get('campus_program_id') && $request->nacteFlag == 'retrieved'){

                $campus_program = CampusProgram::select('id','regulator_code', 'program_id','campus_id')->with(['program:name,id','entryRequirements' => function($query) use($request) {
                    $query->select('id','pass_grade','must_subjects','other_must_subjects','campus_program_id','max_capacity')->where('campus_program_id', $request->get('campus_program_id'));
                }])->find($request->get('campus_program_id'));
                $program = $campus_program;

                $has_must_subjects = false;

                if(unserialize($campus_program->entryRequirements[0]->must_subjects) != null){
                    $has_must_subjects = true;
                }

                $check_selected_tamisemiApplicants = null;
                if($has_must_subjects){
                        $check_selected_tamisemiApplicants = Applicant::whereHas('selections', function($query) use($request){
                            $query->where('campus_program_id', $request->get('campus_program_id'))->where('application_window_id',$request->get('application_window_id'))->where('status', 'SELECTED');
                        })->where('is_tamisemi', 1)->first();
                }

                if(!$check_selected_tamisemiApplicants){
                    DB::beginTransaction();
                    $applicants = Applicant::select('id','index_number')->where('application_window_id',$application_window->id)
                                    ->whereHas('selections', function($query) use($campus_program,$application_window) {
                                    $query->where('campus_program_id', $campus_program->id)->where('application_window_id',$application_window->id)
                                    ->where('status', 'ELIGIBLE');})
                                    ->with(['selections' => function($query){ $query->where('status', 'ELIGIBLE');}])
                                    ->where('is_tamisemi',1)->whereNull('status')->get();

                    if($has_must_subjects){

                        foreach($applicants as $applicant){

                            $parts=explode("/",$applicant->index_number);
                            //create format from returned form four index format

                            if(str_contains($applicant->index_number,'EQ')){
                                $exam_year = explode('/',$applicant->index_number)[1];
                                $index_no = $parts[0];
                            }else{
                                $exam_year = explode('/', $applicant->index_number)[2];
                                $index_no = $parts[0]."-".$parts[1];
                            }
                            // $exam_year = $parts[2];
                            if($det = NectaResultDetail::where('index_number', $applicant->index_number)->where('exam_id', 1)
                                    ->where('verified', 1)->first()){
                                $detail = new NectaResultDetail;
                                $detail->center_name = $det->center_name;
                                $detail->center_number = $det->center_number;
                                $detail->first_name = $det->first_name;
                                $detail->middle_name = $det->middle_name;
                                $detail->last_name = $det->last_name;
                                $detail->sex = $det->sex;
                                $detail->index_number = $det->index_number; //json_decode($response)->particulars->index_number;
                                $detail->division = $det->division;
                                $detail->points = $det->points;
                                $detail->exam_id = 1;
                                $detail->applicant_id = $applicant->id;
                                $detail->verified = 1;
                                $detail->save();

                                $applicant->first_name = $det->first_name;
                                $applicant->middle_name = $det->middle_name;
                                $applicant->surname = $det->last_name;
                                $applicant->gender = $det->sex;
                                $applicant->save();

                                $result = NectaResult::where('necta_result_detail_id', $det->id)->get();

                                foreach($result as $res){
                                    $newRes = new Nectaresult;
                                    $newRes->subject_name = $res->subject_name;
                                    $newRes->subject_code = $res->subject_code;
                                    $newRes->grade = $res->grade;
                                    $newRes->applicant_id = $applicant->id;
                                    $newRes->necta_result_detail_id = $detail->id;
                                    $newRes->save();
                                }

                            } else{
                                $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                                    'api_key'=>config('constants.NECTA_API_KEY'),
                                    'exam_year'=>$exam_year,
                                    'index_number'=>$index_no,
                                    'exam_id'=>'1'
                                ]);

                                if(!isset(json_decode($response)->results)){
                                    return redirect()->back()->with('error','Invalid Index number or year');
                                }

                                    $detail = new NectaResultDetail;
                                    $detail->center_name = json_decode($response)->particulars->center_name;
                                    $detail->center_number = json_decode($response)->particulars->center_number;
                                    $detail->first_name = json_decode($response)->particulars->first_name;
                                    $detail->middle_name = json_decode($response)->particulars->middle_name;
                                    $detail->last_name = json_decode($response)->particulars->last_name;
                                    $detail->sex = json_decode($response)->particulars->sex;
                                    $detail->index_number = $applicant->index_number; //json_decode($response)->particulars->index_number;
                                    $detail->division = json_decode($response)->results->division;
                                    $detail->points = json_decode($response)->results->points;
                                    $detail->exam_id = 1;
                                    $detail->applicant_id = $applicant->id;
                                    $detail->verified = 1;
                                    $detail->save();

                                $applicant->first_name = json_decode($response)->particulars->first_name;
                                $applicant->middle_name = json_decode($response)->particulars->middle_name;
                                $applicant->surname = json_decode($response)->particulars->last_name;
                                $applicant->gender = json_decode($response)->particulars->sex;
                                $applicant->save();


                                foreach(json_decode($response)->subjects as $subject){
                                    $res = new NectaResult;
                                    $res->subject_name = $subject->subject_name;
                                    $res->subject_code = $subject->subject_code;
                                    $res->grade = $subject->grade;
                                    $res->applicant_id = $applicant->id;
                                    $res->necta_result_detail_id = $detail->id;
                                    $res->save();
                                }

                            }
                        }

                       $applicants = Applicant::select('id','index_number','rank_points','status')->with([
                                        'nectaResultDetails' =>function($query){
                                            $query->select('id','exam_id','applicant_id')->where('verified',1)->where('exam_id', 1);
                                        },'nectaResultDetails.results:id,grade,subject_name'])->where('is_tamisemi',1)->whereHas('selections', function($query) use($campus_program) {
                                            $query->select('id')->where('campus_program_id', $campus_program->id)->where('status', 'ELIGIBLE');
                                        })->whereNull('status')->where('application_window_id',$application_window->id)->get();

                        foreach($applicants as $applicant){


                        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];


                        $index_number = $applicant->index_number;
                        if(str_contains($applicant->index_number,'EQ')){
                                $exam_year = explode('/',$applicant->index_number)[1];
                            }else{
                                $exam_year = explode('/', $applicant->index_number)[2];
                            }

                        $subject_count = 0;


                        if(count($campus_program->entryRequirements) == 0){
                            return redirect()->back()->with('error',$campus_program->program->name.' does not have entry requirements');
                        }

                                //NEW
                                // Certificate
                        $must_subject_count = 0;
                        $counted_must_subjects = 0;
                        $counted_other_must_subjects = 0;
                        $o_level_pass_count = $o_level_points = 0;
                                $o_level_other_pass_count = 0;
                                foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                                    $other_must_subject_ready = false;
                                    foreach ($detail->results as $key => $result) {

                                        if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                            $applicant->rank_points += $o_level_grades[$result->grade];
                                            $subject_count += 1;

                                                $must_subject_count = count(unserialize($program->entryRequirements[0]->must_subjects));


                                                if($counted_must_subjects == $must_subject_count && unserialize($program->entryRequirements[0]->other_must_subjects) == ''){
                                                    $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                                    $select->status = 'SELECTED';
                                                    $select->status_changed_at = now();
                                                    $select->save();

                                                    $applicant->status = 'SELECTED';
                                                    $applicant->save();
                                                    break;
                                                }elseif($counted_must_subjects == $must_subject_count && $counted_other_must_subjects > 0){
                                                    $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                                    $select->status = 'SELECTED';
                                                    $select->status_changed_at = now();
                                                    $select->save();

                                                    $applicant->status = 'SELECTED';
                                                    $applicant->save();
                                                    break;
                                                }

                                            if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                                    $counted_must_subjects++;

                                                }
                                            }else if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                                if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                                    $counted_other_must_subjects++;
                                                }
                                            }else {
                                                continue;
                                            }

                                        }
                                    }


                                    if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                                        $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                        $select->status = 'SELECTED';
                                        $select->status_changed_at = now();
                                        $select->save();

                                        $applicant->status = 'SELECTED';
                                        $applicant->save();
                                    }
                                }

                                if($counted_must_subjects != $must_subject_count && unserialize($program->entryRequirements[0]->other_must_subjects) == ''){
                                    $applicant->status = 'NOT SELECTED';
                                    $applicant->save();
                                }elseif($counted_must_subjects == $must_subject_count && $counted_other_must_subjects == 0 && unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    $applicant->status = 'NOT SELECTED';
                                    $applicant->save();
                                }

                        }


                        }else {

                            foreach($applicants as $applicant){
                                $select = ApplicantProgramSelection::find($applicant->selections[0]->id);
                                $select->status = 'SELECTED';
                                $select->status_changed_at = now();
                                $select->save();

                                $applicant->status = 'SELECTED';
                                $applicant->save();
                            }
                    }

                    DB::commit();
                }
                $applicants = [];

                if($request->get('status') == 'unqualified'){
                    if($has_must_subjects){
                        $applicants = Applicant::whereHas('selections',function($query) use($request){
                            $query->where('campus_program_id',$request->get('campus_program_id'))->where('status','!=','SELECTED');
                        })->with(['selections.campusProgram.program','campus','selections'=>function($query){
                            $query->where('status','SELECTED');
                        }])->where('application_window_id',$request->get('application_window_id'))->where('is_tamisemi',1)->get();
                    }
                    if(count($applicants) ==0){
                        return redirect()->back()->with('message', 'No failed applicants in '.$campus_program->program->name);
                    }
                }else{
                    $applicants = Applicant::whereHas('selections',function($query) use($request){
                        $query->where('campus_program_id',$request->get('campus_program_id'))->where('status','SELECTED');
                    })->with(['selections.campusProgram.program','campus','selections'=>function($query){
                        $query->where('status','SELECTED');
                    }])->where('application_window_id',$request->get('application_window_id'))->where('is_tamisemi',1)->get();
                    if(count($applicants) ==0){
                        return redirect()->back()->with('message', 'No applicants in '.$campus_program->program->name);
                    }
                }

        }

        $programs = [];
        foreach($application_window->campusPrograms as $program){
            $campusProg = CampusProgram::whereHas('program', function($query){
                $query->where('name', 'LIKE', '%Basic%');
            })->where('id', $program->pivot->campus_program_id)->first();

            if(!$campusProg){
                continue;
            }
            $programs[] = $campusProg;
        }


        $data = [
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'campus_programs'=>$programs,
			'applicants'=>count($applicants) > 0 ? $applicants : Applicant::whereHas('selections', function($query) use($request){
                $query->where('campus_program_id', $request->get('campus_prgram_id'));
            })->whereDoesntHave('student')->where('application_window_id', $request->get('application_window_id'))->where('is_tamisemi', 1)->get(),
            'request'=>$request
        ];
        return view('dashboard.application.tamisemi-applicants',$data)->withTitle('TAMISEMI Applicants');
    }

    /**
     * Download TAMISEMI applicants
     */
    public function downloadTamisemiApplicants(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(480);

		if($request->get('action') == 'Search Qualified'){
			return redirect()->to('application/tamisemi-applicants?application_window_id='.$request->get('application_window_id').'&campus_program_id='.$request->get('campus_program_id').'&status=qualified');
		}
		if($request->get('action') == 'Search Unqualified'){
			return redirect()->to('application/tamisemi-applicants?application_window_id='.$request->get('application_window_id').'&campus_program_id='.$request->get('campus_program_id').'&status=unqualified');
		}

        $countApplicants = 0;
        $ac_year = StudyAcademicYear::with('academicYear:id,year')->where('status','ACTIVE')->first();
        // explode('/', $ac_year->academicYear->year)[0];
        $applyr = explode('/', $ac_year->academicYear->year)[0];
        $application_window = ApplicationWindow::select('id','intake_id','campus_id')->with('intake:name,id')->find($request->get('application_window_id'));

        $campus_program = CampusProgram::select('id','regulator_code', 'program_id','campus_id')->with(['program:name,id','entryRequirements' => function($query) use($request) {
            $query->select('id','pass_grade','must_subjects','other_must_subjects','campus_program_id','max_capacity')->where('campus_program_id', $request->get('campus_program_id'));
        }])->find($request->get('campus_program_id'));
        $program = $campus_program;

        if(!TamisemiStudent::where('programme_id', $campus_program->regulator_code)->where('intake', $application_window->intake->name)->where('year', $applyr)->first()){

            DB::beginTransaction();

            if(count($program->entryRequirements) === 0){
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
            if($application_window->campus_id == 1){
                $token = config('constants.NACTE_API_KEY_KIVUKONI');
            }elseif($application_window->campus_id == 2){
                $token = config('constants.NACTE_API_KEY_KARUME');
            }elseif($application_window->campus_id == 3){
                $token = config('constants.NACTE_API_KEY_PEMBA');
            }
// dd($nactecode."-".$applyr."-".$intake."/".$token);
            $url="https://www.nacte.go.tz/nacteapi/index.php/api/tamisemiconfirmedlist/".$nactecode."-".$applyr."-".$intake."/".$token;
            // dd($url);
            $returnedObject = null;
            try{
            $arrContextOptions=array(
                "ssl"=>array(
                  "verify_peer"=> false,
                  "verify_peer_name"=> false,
                ),
              );
            // dd(filesize(file_get_contents($url,false, stream_context_create($arrContextOptions))));
            //   $jsondata = file_get_contents($url,false, stream_context_create($arrContextOptions));

              $curl = curl_init();
              curl_setopt($curl, CURLOPT_URL, $url);
              curl_setopt($curl, CURLOPT_HEADER, false);
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($curl, CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
              curl_setopt($curl, CURLOPT_POST, true);
            //   curl_setopt($curl, CURLOPT_POSTFIELDS, $jsondata);
              curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            //   curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);

              $jsondata= curl_exec($curl);
              $err = curl_error($curl);
                curl_close($curl);

                 $returnedObject = json_decode($jsondata);

                 }catch(\Exception $e){}
                //  dd($returnedObject);
                //  if(!isset($returnedObject->params)){
                //     return redirect()->back()->with('error','No students to retrieve from TAMISEMI for selected programme');
                //  }


                 if(!$returnedObject){
                    return redirect()->back()->with('error','Something is wrong, Please try again.');
                 }

                 if($returnedObject->code == 404){
                    return redirect()->back()->with('error','No students to retrieve from TAMISEMI for selected programme');
                 }


              //echo $returnedObject->params[0]->student_verification_id."-dsdsdsdsds-<br />";
              // check for parse errors json_last_error() == JSON_ERROR_NONE
              $countApplicants = count($returnedObject->params);
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
                    $student = null;
                    if(!TamisemiStudent::where('f4indexno',$form4index)->first()){
                        $student = new TamisemiStudent;
                        $student->f4indexno = $form4index;
                        $student->year = $applyr;
                        $student->fullname = $returnedObject->params[$i]->fullname == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->fullname);
                        $student->year = $returnedObject->params[$i]->application_year;
                        $student->programme_id = $nactecode;
                        $student->programme_name = $returnedObject->params[$i]->programe_name;
                        $student->campus = $returnedObject->params[$i]->institution_name;
                        $student->gender = $returnedObject->params[$i]->sex;
                        $student->date_of_birth = $returnedObject->params[$i]->date_of_birth == '' ? null : DateMaker::toDBDate($returnedObject->params[$i]->date_of_birth);
                        $student->phone_number = $returnedObject->params[$i]->phone_number;
                        $student->email = $returnedObject->params[$i]->email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->email);
                        $student->address = $returnedObject->params[$i]->address == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->address);
                        $student->district = $returnedObject->params[$i]->district == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->district);
                        $student->region = $returnedObject->params[$i]->region == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->region);
                        $student->next_of_kin_fullname = $returnedObject->params[$i]->Next_of_kin_fullname == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_fullname);
                        $student->next_of_kin_phone_number = $returnedObject->params[$i]->Next_of_kin_phone_number;
                        $student->next_of_kin_email = $returnedObject->params[$i]->Next_of_kin_email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_email);
                        $student->next_of_kin_address = $returnedObject->params[$i]->Next_of_kin_address == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_address);
                        $student->next_of_kin_region = $returnedObject->params[$i]->Next_of_kin_region == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->Next_of_kin_region);
                        $student->relationship = $returnedObject->params[$i]->relationship;
                        $student->appacyr = $appacyr;
                        $student->intake = $intake;
                        $student->receiveDate = now();
                        $student->save();

                    //    $surname = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1]);

                       if($us = User::where('username',$form4index)->first()){
                           $user = $us;
                       }else{
                           $user = new User;
                       }
                       $user->username = $form4index;
                       $user->email = $returnedObject->params[$i]->email == '' ? '' : str_replace("'","\'",$returnedObject->params[$i]->email);;
                       $user->password = Hash::make($form4index);
                       $user->save();

                       $role = Role::select('id')->where('name','applicant')->first();
                       $user->roles()->sync([$role->id]);

                       $program_level = Award::select('id')->where('name','LIKE','%Basic%')->first();
                       $current_batch = ApplicationBatch::select('batch_no')->where('program_level_id', $program_level->id)->where('application_window_id', $application_window->id)->latest()->first();
                       if($current_batch->batch_no > 1){
                        $prev_batch = ApplicationBatch::select('id')->where('application_window_id',$application_window->id)->where('program_level_id',$program_level->id)
                                        ->where('batch_no', $current_batch->batch_no - 1)->first();
                       }else{
                        $prev_batch = $current_batch;
                       }

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


                       if(Applicant::where('index_number',$form4index)->where('campus_id',$campus_program->campus_id)
                           ->where('application_window_id',$application_window->id)->where('is_tamisemi',1)->first()){
                          continue;
                       }else{
                          $applicant = new Applicant;

                       $applicant->first_name = $student->fullname == '' ? '' : explode(' ', $student->fullname)[0];
                       $applicant->middle_name = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ',$student->fullname)[1] : null);
                       $applicant->surname = $student->fullname == '' ? '' : (count(explode(' ', $student->fullname)) == 3? explode(' ', $student->fullname)[2] : explode(' ',$student->fullname)[1]);
                       $applicant->phone = $student->phone_number == '' ? '' : '225'.substr($student->phone_number,1);
                       $applicant->email = $student->email;
                       $applicant->address = $student->address;
                       $applicant->gender = substr($student->gender, 0,1);
                       $applicant->campus_id = $campus_program->campus_id;
                       $applicant->program_level_id = $program_level->id;
                       // $applicant->next_of_kin_id = $next_of_kin->id;
                       $applicant->application_window_id = $application_window->id;
                       $applicant->batch_id = $prev_batch->id;
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

                       if($has_must_subjects){
                        $selection = new ApplicantProgramSelection;
                        $selection->campus_program_id = $campus_program->id;
                        $selection->applicant_id = $applicant->id;
                        $selection->batch_id = $prev_batch->id;
                        $selection->application_window_id = $application_window->id;
                        $selection->order = 1;
                        $selection->status = 'ELIGIBLE';
                        $selection->save();
                       }else{

                        $applicant->status = 'SELECTED';
                        $applicant->save();

                        $selection = new ApplicantProgramSelection;
                        $selection->campus_program_id = $campus_program->id;
                        $selection->applicant_id = $applicant->id;
                        $selection->batch_id = $prev_batch->id;
                        $selection->application_window_id = $application_window->id;
                        $selection->order = 1;
                        $selection->status = 'SELECTED';
                        $selection->save();
                       }


                    }

                    //    try{
                    //        Mail::to($user)->queue(new TamisemiApplicantCreated($student,$applicant,$campus_program->program->name));
                    //    }catch(\Exception $e){}
                    }

                }
              }
            }//end

            DB::commit();

        }else {
            $countApplicants = TamisemiStudent::where('programme_id', $campus_program->regulator_code)->where('intake', $application_window->intake->name)->where('year', $applyr)->count();
        }


        // dispatch(new GetNacteResultDetails($request->get('application_window_id'), $request->get('campus_program_id')));

        return redirect()->to('application/tamisemi-applicants?application_window_id='.$request->get('application_window_id').'&campus_program_id='.$request->get('campus_program_id').'&nacteFlag=retrieved')->with('message', $countApplicants.' TAMISEMI applicants retrieved successfully');
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
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('batch_id',$applicant->batch_id)->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment');
        }

        $applicant->teacher_certificate_status = $request->get('teacher_certificate_status');
        $applicant->save();

        return redirect()->back()->with('message','Teacher certificate status updated successfully');
    }

    /**
     * Update veta certificate status
     */

    public function updateVetaCertificate(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'veta_certificate_status'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $applicant = Applicant::where('id',$request->get('applicant_id'))->with('programLevel')->first();

        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('batch_id',$applicant->batch_id)->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment');
        }

        $o_level_result_count = NectaResultDetail::where('applicant_id',$request->get('applicant_id'))->where('exam_id',1)->where('verified',1)->count();

        $applicant->veta_status = $request->get('veta_certificate_status');
		if(str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'EQUIVALENT' && $o_level_result_count != 0
			&& $request->get('veta_certificate_status') == 1){
            $applicant->results_complete_status = 1;

        }else{
            $applicant->results_complete_status = 0;

		}
        $applicant->save();

        return redirect()->back()->with('message','Veta certificate status updated successfully');
    }

    /**
     * Manual registration
     */
    public function specialRegister(Request $request)
    {

        $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();

		if(!$ac_year){
			return redirect()->back()->with('error', 'No active academic year');
		}
		if(!$semester){
			return redirect()->back()->with('error', 'No active semester');
		}
		$staff = User::find(Auth::user()->id)->staff;
		$application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->whereYear('end_date',explode('/',$ac_year->academicYear->year)[0])->first();
		$applicant = Applicant::whereDoesntHave('student')->whereHas('selections',function($query) use($application_window){$query->where('status','SELECTED')
								->where('application_window_id',$application_window->id);})->with('selections.campusProgram.program')->where('status','ADMITTED')
								->where('application_window_id',$application_window->id)->where('campus_id', $staff->campus_id)
								->where('index_number',$request->keyword)->orWhere('surname',$request->keyword)->latest()->first();
		$student = Student::whereDoesntHave('registrations', function($query) use($ac_year, $semester){$query->where('semester_id',$semester->id)->where('study_academic_year_id',$ac_year->id);})
							->orWhereHas('registrations', function($query) use($ac_year, $semester){$query->where('status','UNREGISTERED')->where('semester_id',$semester->id)->where('study_academic_year_id',$ac_year->id);})
							->whereHas('studentshipStatus', function($query){$query->where('name','ACTIVE')->OrWhere('name','RESUMED');})
							->whereHas('academicStatus', function($query){$query->where('name','!=','FAIL&DISCO')->orWhere('name','!=','DECEASED');})
							->where('registration_number', $request->keyword)->with(['campusProgram.program','academicStatus'])->first();

		if($semester->id === 2 && $student->campusProgram->program->min_duration === $student->year_of_study){
			$finalist_status = SemesterRemarks::where('student_id', $student->id)->where('semester_id', $semester->id)->where('study_academic_year_id',$ac_year->id)
								->where('year_of_study',$student->year_of_study)->count();
			if($finalist_status > 0){
				return redirect()->back()->with('error', 'The student cannot be registered');
			}
		}
		if($request->keyword && !$applicant && !$student){
			return redirect()->back()->with('error', 'The student cannot be registered');
		}
		$data = [
			'semester'=>$semester,
			'applicant'=>$applicant,
			'student'=>$student,
			'ac_year'=>$ac_year
		];

        return view('dashboard.application.special-registration',$data)->withTitle('Special Registration');
    }

/**
     * Manual registration
     */
    public function registerManual(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
		$semester = Semester::where('status','ACTIVE')->first();
	    $studentship_status = StudentshipStatus::where('name','ACTIVE')->first();

		DB::beginTransaction();
		if($request->type == "applicant"){
			if(str_contains($semester->name,'2')){
				return redirect()->back()->with('error','Active semester must be set to first semester');
			}

			$applicant = Applicant::with(['intake','campus','nextOfKin','country','region','district','ward','insurances','programLevel'])->find($request->get('keyword'));

			$applicant->results_check = 1;
			$applicant->insurance_check = 1;
			$applicant->personal_info_check = 1;
			$applicant->medical_form_check = 1;
			$applicant->registered_by_user_id = Auth::user()->id;
			$applicant->save();

			$selection = ApplicantProgramSelection::with('campusProgram.program')->where('applicant_id',$request->get('keyword'))->where('status','SELECTED')->first();

			$academic_status = AcademicStatus::where('name','FRESHER')->first();

			$last_student = DB::table('students')->select(DB::raw('MAX(REVERSE(SUBSTRING(REVERSE(registration_number),1,7))) AS last_number'))->where('campus_program_id',$selection->campusProgram->id)->first();

			if(!empty($last_student->last_number)){
				$code = sprintf('%04d', substr($last_student->last_number, 0, 4) + 1);
			}else{
			   $code = sprintf('%04d',1);
			}
			$year = substr(date('Y'), 2);

			$prog_code = explode('.', $selection->campusProgram->code);

			$program_code = $prog_code[0].'.'.$prog_code[1];

			$stud_group = explode('.', $selection->campusProgram->code);

			if(str_contains($applicant->intake->name,'March')){
				if(str_contains($applicant->campus->name,'Kivukoni')){
					$program_code = $prog_code[0].'3.'.$prog_code[1];
					if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'3';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

						$stud_group = 'C'.$stud_group[1].'3';
					}
				} elseif (str_contains($applicant->campus->name,'Karume')) {

					$program_code = $prog_code[0].'Z3.'.$prog_code[1];

					if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z3';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

						$stud_group = 'C'.$stud_group[1].'Z3';

					}
				}  elseif (str_contains($applicant->campus->name,'Pemba')) {

					$program_code = $prog_code[0].'P3.'.$prog_code[1];

					if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P3';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

						$stud_group = 'C'.$stud_group[1].'P3';
					}
				}

			}else{
				// september intake
				if(str_contains($applicant->campus->name,'Karume')){

					$program_code = $prog_code[0].'Z9.'.$prog_code[1];

					if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {
						$program_code = $prog_code[0].'Z.'.$prog_code[1];
						if (str_contains($selection->campusProgram->program->name, 'Leadership') && str_contains($selection->campusProgram->program->name, 'Governance')) {

						$stud_group = $stud_group[0].$stud_group[1].'Z';

						} elseif (str_contains($selection->campusProgram->program->name, 'Procurement') && str_contains($selection->campusProgram->program->name, 'Supply')) {

						$stud_group = $stud_group[0].$stud_group[1].'Z';

						} else {

						$stud_group = $stud_group[0].'Z'.$stud_group[1];

						}

					} else if (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {

						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'Z9';

					} else if (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {

						$stud_group = 'C'.$stud_group[1].'Z9';

					}

				} elseif (str_contains($applicant->campus->name,'Kivukoni')) {

					if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {

						if (str_contains(strtolower($selection->campusProgram->program->name), 'human') && str_contains(strtolower($selection->campusProgram->program->name), 'resource')) {

							$stud_group = substr($stud_group[0], 0, 1).$stud_group[1];

						} else {

							$stud_group = $stud_group[0].$stud_group[1];
						}
					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {
						$program_code = $prog_code[0].'9.'.$prog_code[1];
						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'9';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {
						$program_code = $prog_code[0].'9.'.$prog_code[1];
						$stud_group = 'C'.$stud_group[1];
					}
				} elseif (str_contains($applicant->campus->name,'Pemba')) {
					$program_code = $prog_code[0].'P9.'.$prog_code[1];

					if (str_contains(strtolower($selection->campusProgram->program->name), 'bachelor')) {
						$program_code = $prog_code[0].'P.'.$prog_code[1];
						$stud_group = $stud_group[0].$stud_group[1].'P';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'diploma')) {
						$stud_group = substr($stud_group[0], 1, 1).$stud_group[1].'P9';

					} elseif (str_contains(strtolower($selection->campusProgram->program->name), 'certificate') || str_contains(strtolower($selection->campusProgram->program->name), 'technician')) {
						$stud_group = 'C'.$stud_group[1].'P9';
					}
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
			$student->image = $applicant->passport_picture;
			$student->campus_program_id = $selection->campusProgram->id;
			$student->registration_number = 'MNMA/'.$program_code.'/'.$code.'/'.$year;
			$student->disability_status_id = $applicant->disability_status_id;
			$student->studentship_status_id = $studentship_status->id;
			$student->academic_status_id = $academic_status->id;

			$user = User::find($applicant->user_id);
			$student->user_id = $user->id;
			$student->save();

			$loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$ac_year->id)->first();
			if($loan_allocation){
				if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
					$registration = $reg;
				}else{
					$registration = new Registration;
				}
				$registration->study_academic_year_id = $ac_year->id;
				$registration->semester_id = $semester->id;
				$registration->student_id = $student->id;
				$registration->year_of_study = 1;
				$registration->registration_date = date('Y-m-d');
				$registration->registered_by_staff_id = $staff->id;
				$registration->status = $loan_allocation->has_signed == 1? 'REGISTERED' : 'UNREGISTERED';
				$registration->save();

				$loan_allocation->registration_number = $student->registration_number;
				$loan_allocation->student_id = $student->id;
				$loan_allocation->save();
			}else{
				if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
					$registration = $reg;
				}else{
					$registration = new Registration;
				}
				$registration->study_academic_year_id = $ac_year->id;
				$registration->semester_id = $semester->id;
				$registration->student_id = $student->id;
				$registration->year_of_study = 1;
				$registration->registration_date = date('Y-m-d');
				$registration->registered_by_staff_id = $staff->id;
				$registration->status = 'REGISTERED';
				$registration->save();
			}

			$check_insurance = false;
			if(count($applicant->insurances) != 0){
				if($applicant->insurances[0]->verification_status != 'VERIFIED'){
					$check_insurance = true;
				}
			}

			if($ac_year->nhif_enabled == 1){
				if($applicant->insurance_status == 0 || $check_insurance){
				 try{
					 $path = public_path().'/avatars/'.$student->image;
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
						  'Gender'=> $applicant->gender == 'M'? 'Male' : 'Female'
                          //,		  'PhotoImage'=>$base64
					  ];

					  $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/StudentRegistration';
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

					$url = 'https://verfication.nhif.or.tz/omrs/api/v1/Verification/SubmitCardApplications';
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
					}catch(\Exception $e){
						$record = new InsuranceRegistration;
						$record->applicant_id = $applicant->id;
						$record->student_id = $student->id;
						$record->study_academic_year_id = $ac_year->id;
						$record->is_success = 0;
						$record->save();
					}
				}
			}
			$tuition_invoice = Invoice::whereHas('feeType',function($query){
				   $query->where('name','LIKE','%Tuition%');
			})->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

			$misc_invoice = Invoice::whereHas('feeType',function($query){
				   $query->where('name','LIKE','%Miscellaneous%');
			})->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->first();

			$usd_currency = Currency::where('code','USD')->first();

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

			$next_of_kin_email = $applicant->nextOfKin->email? $applicant->nextOfKin->email : 'UNKNOWN';

			if ($tuition_invoice) {
				$acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('".$stud_reg."','".$stud_group."','".$stud_name."','".$applicant->address."','".$applicant->district->name."','".$applicant->ward->name."','".$applicant->street."','".$applicant->region->name."','".$applicant->country->name."','".$applicant->address."','".$applicant->country->name."','".$next_of_kin."','".$applicant->phone."','".$applicant->nextOfKin->phone."','".''."','STD','TSH','".$applicant->email."','".$next_of_kin_email."')");
				$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$tuition_invoice->control_no."','".date('Y',strtotime($tuition_invoice->created_at))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','1','".$tuition_invoice->feeType->gl_code."','".$tuition_invoice->feeType->name."','".$tuition_invoice->feeType->description."','".$tuition_invoice->amount."','0','".date('Ymd',strtotime(now()))."')");
			}
			if(str_contains($applicant->programLevel->name,'Bachelor')){
				$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
					$query->where('name','LIKE','%TCU%');
				})->where('study_academic_year_id',$ac_year->id)->with(['feeItem.feeType'])->first();
			}else{
				$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
					$query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
				})->where('study_academic_year_id',$ac_year->id)->with(['feeItem.feeType'])->first();
			}

			$other_fees = FeeAmount::whereHas('feeItem',function($query){
					$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
				})->with(['feeItem.feeType'])->where('study_academic_year_id',$ac_year->id)->get();

			if($misc_invoice){
				if(str_contains($applicant->nationality,'Tanzania')){
					$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."','".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."','".$quality_assurance_fee->feeItem->feeType->description."','".$quality_assurance_fee->amount_in_tzs."','0','".date('Ymd',strtotime(now()))."')");

					foreach ($other_fees as $fee) {
						$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."','".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$fee->feeItem->feeType->gl_code."','".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."','".$fee->amount_in_tzs."','0','".date('Y',strtotime(now()))."')");
					}
				}else{
					$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."','".date('Y',strtotime($misc_invoice->created_at))."','".$quality_assurance_fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$quality_assurance_fee->feeItem->feeType->gl_code."','".$quality_assurance_fee->feeItem->feeType->name."','".$quality_assurance_fee->feeItem->feeType->description."','".($quality_assurance_fee->amount_in_usd*$usd_currency->factor)."','0','".date('Ymd',strtotime(now()))."')");

					foreach ($other_fees as $fee) {
						$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$misc_invoice->control_no."','".date('Y',strtotime($misc_invoice->created_at))."','".$fee->feeItem->feeType->description."','".$stud_reg."','".$stud_name."','1','".$fee->feeItem->feeType->gl_code."','".$fee->feeItem->feeType->name."','".$fee->feeItem->feeType->description."','".($fee->amount_in_usd*$usd_currency->factor)."','0','".date('Ymd',strtotime(now()))."')");
					}
				}
			}

			if ($tuition_invoice) {
				$tuition_receipts = GatewayPayment::where('control_no',$tuition_invoice->control_no)->get();

				foreach($tuition_receipts as $receipt){
					if($receipt->psp_name == 'National Microfinance Bank'){
						$bank_code = 619;
						$bank_name = 'NMB';
					}else{
						$bank_code = 615;
						$bank_name = 'CRDB';
					}

					$acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$tuition_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$receipt->control_no."','".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
				}
			}


			if($misc_invoice){
				$misc_receipts = GatewayPayment::where('control_no',$misc_invoice->control_no)->get();

				foreach ($misc_receipts as $receipt) {
					if($receipt->psp_name == 'National Microfinance Bank'){
						$bank_code = 619;
						$bank_name = 'NMB';
					}else{
						$bank_code = 615;
						$bank_name = 'CRDB';
					}

					$acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($receipt->transaction_id,5)."','".date('Ymd',strtotime($receipt->datetime))."','".$misc_invoice->feeType->description."','".$stud_reg."','".$stud_name."','".$receipt->control_no."','".$receipt->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
				}
			}

			$acpac->close();

			Invoice::whereHas('feeType',function($query){
				   $query->where('name','LIKE','%Tuition%');
			})->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->update(['payable_type'=>'student','payable_id'=>$student->id,'applicable_id'=>$ac_year->id,'applicable_type'=>'academic_year']);

			Invoice::whereHas('feeType',function($query){
				   $query->where('name','LIKE','%Miscellaneous%');
			})->with(['gatewayPayment','feeType'])->where('payable_type','applicant')->where('payable_id',$applicant->id)->update(['payable_type'=>'student','payable_id'=>$student->id,'applicable_id'=>$ac_year->id,'applicable_type'=>'academic_year']);

			$transfered_status = false;

			try{
			   Mail::to($user)->send(new StudentAccountCreated($student, $selection->campusProgram->program->name,$ac_year->academicYear->year, $transfered_status));
			}catch(Exception $e){}

			DB::commit();

			return redirect()->to('application/special-registration')->with('message','Student registered successfully with registration number '.$student->registration_number);

			}elseif($request->type == "student"){
				$student = Student::where('id',$request->keyword)->with(['applicant','studentshipStatus','academicStatus','semesterRemarks','overallRemark'])->first();
				if($student->semesterRemarks){
					foreach($student->semesterRemarks as $rem){
						if($student->academicStatus->name == 'RETAKE'){
							if($rem->semester_id == session('active_semester_id') && $rem->remark != 'RETAKE'){
								return redirect()->back()->with('error','The student cannot be registered');
							}
						}
					}
				}
/*
				if($student->overallRemark){
					if($student->overallRemark){
						if($student->overallRemark->remark == 'SUPP'){
							return redirect()->back()->with('error','The student cannot be registered2');
						}
					}
				} */

				$annual_remarks = AnnualRemark::where('student_id',$student->id)->latest()->get();
				$semester_remarks = SemesterRemark::with('semester')->where('student_id',$student->id)->latest()->get();
				$can_register = true;
				if(count($annual_remarks) != 0){
					$last_annual_remark = $annual_remarks[0];
					$year_of_study = $last_annual_remark->year_of_study;
					if($last_annual_remark->remark == 'RETAKE'){
						$year_of_study = $last_annual_remark->year_of_study;
					}elseif($last_annual_remark->remark == 'CARRY'){
						$year_of_study = $last_annual_remark->year_of_study;
					}elseif($last_annual_remark->remark == 'REPEAT'){
						$year_of_study = $last_annual_remark->year_of_study;
					}elseif($last_annual_remark->remark == 'SUPP'){
						$year_of_study = $last_annual_remark->year_of_study;
					}elseif($last_annual_remark->remark == 'PASS'){
						if(str_contains($semester_remarks[0]->semester->name,'2')){
						   $year_of_study = $last_annual_remark->year_of_study + 1;
						}else{
						   $year_of_study = $last_annual_remark->year_of_study;
						}
					}elseif($last_annual_remark->remark == 'FAIL&DISCO'){
						$can_register = false;
						return redirect()->back()->with('error','The student cannot be registered');
					}elseif($last_annual_remark->remark == 'INCOMPLETE'){
						$can_register = false;
						return redirect()->back()->with('error','The student cannot be registered');
					}
				}elseif(count($semester_remarks) == 1){
					$year_of_study = 1;
				}else{
					$year_of_study = 1;
				}

				$registration = new Registration;
				$registration->year_of_study = $year_of_study;
				$registration->student_id = $student->id;
				$registration->study_academic_year_id = session('active_academic_year_id');
				$registration->semester_id = session('active_semester_id');
				$registration->registration_date = date('Y-m-d');
				$registration->status = 'REGISTERED';
				$registration->save();

				$stud = Student::find($student->id);
				$stud->year_of_study = $year_of_study;
				$stud->save();

				return redirect()->to('application/special-registration')->with('message','Registration completed successfully');
			}

	}

      /**
   * Display registration deadline
   */
  public function showAdmissionReferenceNumber(Request $request)
  {
      $staff = User::find(Auth::user()->id)->staff;
      $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status','ACTIVE')->first();
   //    $boolFlag = false
   //    foreach($app_window as $window){
   //       if($window->status == 'ACTIVE' && $window->intake->name == 'September'){
   //          $boolFlag = true
   //       }else{
   //          $boolFlag = false
   //       }
   //    }
   // // dd(json_encode(Intake::whereId($app_window[0]->intake_id)->pluck('name')[0]));

      $data = [
           'campus_id'  => $staff->campus_id,
           'campuses'=>Campus::all(),
           'app_window' => $app_window,
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'campus'=>Campus::find($request->get('campus_id')),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'references'=>Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')? AdmissionReferenceNumber::where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('intake',$request->get('intake'))->get() : AdmissionReferenceNumber::where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('campus_id',$staff->campus_id)->get(),
           'request'=>$request,
           'awards'=>Award::all(),
           'intakes'=>Intake::all(),
        ];
        return view('dashboard.admission.reference-numbers',$data)->withTitle('Admission Reference Number');
  }

  public function storeAdmissionReferenceNumber(Request $request)
  {
      $validation = Validator::make($request->all(),[
          'study_academic_year_id'=>'required',
          'intake'=>'required',
          'applicable_level'=>'required',
          'reference_number'=>'required',
          'campus_id'=>'required',
      ]);

      if($validation->fails()){
         if($request->ajax()){
            return response()->json(array('error_messages'=>$validation->messages()));
         }else{
            return redirect()->back()->withInput()->withErrors($validation->messages());
         }
      }

      $reference = new AdmissionReferenceNumber;
      $reference->intake = $request->get('intake');
      $reference->name = $request->get('reference_number');
      $reference->campus_id = $request->get('campus_id');
      $reference->study_academic_year_id = $request->get('study_academic_year_id');
      $reference->applicable_levels = serialize($request->get('applicable_level'));
      $reference->save();

      return redirect()->back()->with('message','Graduation date created successfully');
  }
}
