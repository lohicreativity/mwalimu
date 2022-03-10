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
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\FeeAmount;
use Illuminate\Support\Facades\Http;
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
    public function appealResults(Request $request)
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
    	 $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($request, $student){
         	   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($request){
         	 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'));
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();

         $count = 0;
         foreach($results as $result){
         	 if($request->get('result_'.$result->id)){
         	 	 $appeal = new Appeal;
         	 	 $appeal->examination_result_id = $result->id;
         	 	 $appeal->module_assignment_id = $result->module_assignment_id;
         	 	 $appeal->student_id = $result->student_id;
         	 	 $appeal->save();
                 $count++;
         	 }
         }

         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Appeal%');
            })->where()->where('study_academic_year_id',$result->moduleAssignment->study_academic_year_id)->first();

         if($student->applicant->country->code == 'TZ'){
             $amount = $count*$fee_amount->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $count*$fee_amount->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
        $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
        $invoice->save();

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_amount->feeItem->feeType->description,
                                    $fee_amount->feeItem->feeType->gfs_code,
                                    $fee_amount->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->middle_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_amount->feeItem->feeType->duration,
                                    $invoice->currency);

        return redirect()->to('student/request-control-number')->with('message','Results appeals submitted successfully');
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

            
        return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
                        
        }
}
