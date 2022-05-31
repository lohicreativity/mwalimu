<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManagerStatic as Image;
use Auth, PDF;

class RegistrationController extends Controller
{
    /**
     * Create new registration
     */
    public function create(Request $request)
    {
    	$student = User::find(Auth::user()->id)->student()->with('applicant')->first();
    	$annual_remarks = AnnualRemark::where('student_id',$student->id)->latest()->get();
        $semester_remarks = SemesterRemark::with('semester')->where('student_id',$student->id)->latest()->get();
        if(count($annual_remarks) != 0){
        	$last_annual_remark = $annual_remarks[0];
        	$year_of_study = $last_annual_remark->year_of_study;
        	if($last_annual_remark->remark == 'RETAKE'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'CARRY'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'PASS'){
        		if(str_contains($semester_remarks[0]->semester->name,'2')){
                   $year_of_study = $last_annual_remark->year_of_study + 1;
        		}else{
                   $year_of_study = $last_annual_remark->year_of_study;
        		}
        	}
        }elseif(count($semester_remarks) == 1){
        	$year_of_study = 1;
        }

    	 $program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$year_of_study)->first();

         if(!$program_fee){
            return redirect()->back()->with('error','No programme fee set for this academic year');
         }

         if($student->applicant->country->code == 'TZ'){
             $amount = $program_fee->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $program_fee->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
        $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
        $invoice->save();

        $registration = new Registration;
        $registration->year_of_study = $year_of_study;
        $registration->student_id = $student->id;
        $registration->study_academic_year_id = session('active_academic_year_id');
        $registration->semester_id = session('active_semester_id');
        $registration->save();

        $stud = Student::find($student->id);
        $stud->year_of_study = $year_of_study;
        $stud->save();

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $program_fee->feeItem->feeType->description,
                                    $program_fee->feeItem->feeType->gfs_code,
                                    $program_fee->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->middle_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $program_fee->feeItem->feeType->duration,
                                    $invoice->currency);

        return redirect()->to('student/request-control-number')->with('message','Registration initiated successfully');
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

    /**
     * Print ID card
     */
    public function printIDCard(Request $request)
    {
        $data = [
            'student'=>Student::with('campusProgram.program','campusProgram.campus')->where('registration_number',$request->get('registration_number'))->first(),
            'semester'=>Semester::where('status','ACTIVE')->first(),
            'study_academic_year'=>StudyAcademicYear::where('status','ACTIVE')->first()
        ];
        return view('dashboard.registration.id-card',$data)->withTitle('ID Card');
    }


    /**
     * Show ID Card
     */
    public function showIDCard(Request $request)
    {
        $data = [
            'student'=>Student::with('campusProgram.program','campusProgram.campus')->where('registration_number',$request->get('registration_number'))->first(),
            'semester'=>Semester::where('status','ACTIVE')->first(),
            'study_academic_year'=>StudyAcademicYear::where('status','ACTIVE')->first()
        ];
         $pdf = PDF::loadView('dashboard.registration.reports.id-card',$data,[],[
               'format'=>'A7',
               'mode' => 'utf-8',
               'allow_charset_conversion' => true,
               'margin_top'=>0,
               'margin_bottom'=>0,
               'margin_left'=>0,
               'margin_right'=>0,
               'orientation'=>'L',
               'display_mode'=>'fullpage',
               // 'format'=>[500,400]
        ]);
        return  $pdf->stream(); 
         // return view('dashboard.registration.reports.id-card',$data);
    }

    /**
     * Crop student image
     */
    public function cropStudentImage(Request $request)
    {
          $y1=$request->get('top');
          $x1=$request->get('left');
          $w=$request->get('right');
          $h=$request->get('bottom');
          $image=public_path().'/img/user-avatar.png';

          $type = explode('.', $image)[1];

          list( $width,$height ) = getimagesize( $image );
          $newwidth = 600;
          $newheight = 400;

          switch($type){
            case 'bmp': $img = imagecreatefromwbmp($image); break;
            case 'gif': $img = imagecreatefromgif($image); break;
            case 'jpg': $img = imagecreatefromjpeg($image); break;
            case 'png': $img = imagecreatefrompng($image); break;
            default : return "Unsupported picture type!";
          }

          $thumb = imagecreatetruecolor( $newwidth, $newheight );
          $source = $img; //imagecreatefromjpeg($image);

          imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
          imagejpeg($thumb,$image,100); 


          $im = $img; //imagecreatefromjpeg($image);
          $dest = imagecreatetruecolor($w,$h);
            
          imagecopyresampled($dest,$im,0,0,$x1,$y1,$w,$h,$w,$h);

          switch($type){
            case 'bmp': imagewbmp($dest,$image); break;
            case 'gif': imagegif($dest,$image); break;
            case 'jpg': imagejpeg($dest,$image); break;
            case 'png': imagepng($dest,$image); break;
          }
          //imagejpeg($dest,$image, 100);

          return redirect()->back()->with('message','Image cropped successfully');
    }

    /**
     * Print ID Card Bulk
     */
    public function printIDCardBulk(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $data = [
            'campus_programs'=>CampusProgram::with('program')->where('campus_id',$staff->campus_id)->get(),
            'students'=>Registration::with(['student.campusProgram.campus','student.campusProgram.program'])->whereHas('student',function($query) use($request){
                    $query->where('campus_program_id',$request->get('campus_program_id'));
                })->where('study_academic_year_id',$study_academic_year->id)->get()
        ];
        return view('dashboard.registration.id-card-bulk',$data)->withTitle('ID Card Bulk');

    }

    /**
     * Show ID Card Bulk
     */
    public function showIDCardBulk(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $data = [
            'students'=>Registration::with(['student.campusProgram.campus','student.campusProgram.program'])->whereHas('student',function($query) use($request){
                    // $query->where('campus_program_id',$request->get('campus_program_id'));
                })->where('study_academic_year_id',$study_academic_year->id)->get(),
            'semester'=>$semester,
            'study_academic_year'=>$study_academic_year
        ];
        // return view('dashboard.registration.print-id-card-bulk',$data)->withTitle('Print ID Card Bulk');
        $pdf = PDF::loadView('dashboard.registration.print-id-card-bulk',$data,[],[
               'format'=>'A7',
               // 'mode' => 'utf-8',
               // 'allow_charset_conversion' => true,
               // 'margin_top'=>0,
               // 'margin_bottom'=>0,
               // 'margin_left'=>0,
               // 'margin_right'=>0,
               'orientation'=>'L',
               'display_mode'=>'fullpage',
               // 'format'=>[500,400]
        ]);
        return  $pdf->stream(); 
    }

}
