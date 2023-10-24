<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\HumanResources\Models\Designation;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Department;
use App\Domain\HumanResources\Actions\StaffAction;
use App\Models\Role;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth, File, Storage, DB;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Domain\Academic\Models\Program;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Academic\Models\StudyAcademicYear;
use Illuminate\Support\Facades\Http;

class StaffController extends Controller
{
    /**
     * Display a list of staffs
     */
    public function index(Request $request)
    {

      $staffs = Staff::with(['country','region','district','ward','designation','user.roles'])->get();

    	$data = [
           'staffs'=>$staffs,
           'roles'=>Role::where('name','!=','student')->get(),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'designations'=>Designation::all(),
           'disabilities'=>DisabilityStatus::all(),
           'campuses'=>Campus::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
    	];
    	return view('dashboard.human-resources.staffs',$data)->withTitle('Staff Details');
    }

    /**
     * Display form for creating new staff
     */
    public function create()
    {
        $data = [
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'designations'=>Designation::all(),
           'disabilities'=>DisabilityStatus::all(),
           'campuses'=>Campus::all(),
           'departments'=>Department::all(),
           'staff'=>User::find(Auth::user()->id)->staff
        ];
        return view('dashboard.human-resources.add-staff',$data)->withTitle('Add Staff');
    }


    /**
     * Display staff details
     */
    public function show($id)
    {
        try{
            $data = [
               'profile_staff'=>Staff::with(['disabilityStatus','country','region','district','ward','designation'])->find($id),
               'staff'=>User::find(Auth::user()->id)->staff,
            ];
            return view('dashboard.human-resources.staff-details',$data)->withTitle('Staff Details');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display form for editng staff
     */
    public function edit($id)
    {
        try{
            $data = [
               'edit_staff'=>Staff::findOrFail($id),
               'countries'=>Country::all(),
               'regions'=>Region::all(),
               'districts'=>District::all(),
               'wards'=>Ward::all(),
               'designations'=>Designation::all(),
               'disabilities'=>DisabilityStatus::all(),
               'campuses'=>Campus::all(),
               'departments'=>Department::all(),
               'staff'=>User::find(Auth::user()->id)->staff
            ];
            return view('dashboard.human-resources.edit-staff',$data)->withTitle('Edit Staff');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Update roles
     */
    public function updateRoles(Request $request)
    {
        $roles = Role::all();
        $roleIds = [];
        $user = User::find($request->get('user_id'));
        foreach($roles as $role){
          if($request->get('role_'.$role->id) == $role->id){
            $roleIds[] = $role->id;
          }
        }
        $user->roles()->sync($roleIds);

        return Util::requestResponse($request,'Roles updated successfully');
    }

    /**
     * Store staff into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'birth_date'=>'required',
            'email'=>'required|email|unique:users',
            'address'=>'required',
            'phone'=>'required'

        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new StaffAction)->store($request);

        return Util::requestResponse($request,'Staff created successfully');
    }

    /**
     * Update specified staff
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'birth_date'=>'required',
            'address'=>'required',
            'phone'=>'required'
            
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new StaffAction)->update($request);

        return Util::requestResponse($request,'Staff updated successfully');
    }

    /**
     * Update specified staff details
     */
    public function updateDetails(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'address'=>'required',
            'phone'=>'required',
            'image'=>'mimes:png,jpg,jpeg'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new StaffAction)->updateDetails($request);

        return Util::requestResponse($request,'Staff details updated successfully');
    }

    /**
     * Remove the specified staff
     */
    public function destroy(Request $request, $id)
    {
        try{
            $staff = Staff::findOrFail($id);
            if(ModuleAssignment::where('staff_id',$staff->id)->count() != 0){
               return redirect()->back()->with('message','Staff cannot be deleted because he has alredy been assigned a module');
            }else{
               $staff->delete();
               return redirect()->back()->with('message','Staff deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
	
	
    public function viewPayerDetails(Request $request)
    {
		if(!empty($request->keyword)){
            $applicant = Applicant::select('id')->where('index_number',$request->keyword)->latest()->first();
            $applicant_id = $applicant? $applicant->id : 0;
            return $applicant_id;
			$student_payer = Student::where('registration_number', $request->keyword)
			->orWhere('surname',$request->keyword)->orWhere('applicant_id',$applicant_id)
			->with(['applicant','campusProgram.program','studentShipStatus'])->first();
			$applicant_payer = Applicant::whereDoesntHave('student',function($query) use($applicant_id){$query->where('applicant_id',$applicant_id);})->with(['programLevel','intake','disabilityStatus'])
            ->where('index_number', $request->keyword)->orWhere('surname',$request->keyword)->latest()->first();

            if(!$student_payer && !$applicant_payer){
				return redirect()->back()->with('error','There is no such a payer');
			}
			$applicant_payer? $paid_as_applicant = Invoice::where('payable_id',$applicant_id)->where('payable_type','applicant')->with('feeType','gatewayPayment')->whereNotNull('gateway_payment_id')->get() : 
            $paid_as_applicant = null;
			$student_payer? $paid_as_student = Invoice::where('payable_id', $student_payer->id)->where('payable_type','student')
            ->orWhere(function($query) use($student_payer){$query->where('payable_id',$student_payer->applicant->id)
                ->where('payable_type','applicant');})->with('feeType','gatewayPayment')->whereNotNull('gateway_payment_id')->get() : $paid_as_student = null;
      
            $reference_no = [];
            $total_fee_paid_amount = 0;
            if($applicant_payer && $paid_as_applicant){
                foreach($paid_as_applicant as $invoice){
                    $reference_no[] = $invoice->reference_no;
                }
                foreach($paid_as_applicant as $payment){
                    if(str_contains($payment->feeType->name, 'Tuition')){
                        $total_fee_paid_amount = GatewayPayment::where('bill_id', $payment->reference_no)->sum('paid_amount');
                        break;
                    }
                }

            }elseif($student_payer && $paid_as_student){
                foreach($paid_as_student as $invoice){
                    $reference_no[] = $invoice->reference_no;
                }
                foreach($paid_as_student as $payment){
                    if(str_contains($payment->feeType->name, 'Tuition')){
                        $total_fee_paid_amount = GatewayPayment::where('bill_id', $payment->reference_no)->sum('paid_amount');
                        break;
                    }
                }
            }

            $paid_receipts = GatewayPayment::select('bill_id','payment_channel','cell_number','psp_receipt_no','psp_name','created_at')->whereIn('bill_id',$reference_no)->get();
			$data = [
				'payer'=>$student_payer? $student_payer : $applicant_payer,
				'category'=>$student_payer? 'student' : 'applicant',
				'applicant_payments'=>$paid_as_applicant? $paid_as_applicant : [],
				'student_payments'=>$paid_as_student? $paid_as_student : [],
                'paid_receipts'=>$paid_receipts? $paid_receipts : [],
                'total_paid_fee'=>$total_fee_paid_amount
			];

		}else{
			$data = [
				'payer'=>[]
			];			
		}

        return view('dashboard.finance.payer-details',$data)->withTitle('Payer Details');   
    }
	
	  /**
	  * Download students' payments
	  */
	  public function downloadPayments(Request $request)
	  {
		$student = Student::with('applicant')->where('registration_number', $request->keyword)->first();
		$applicant = Applicant::with(['programLevel','intake','disabilityStatus'])->where('index_number', $request->keyword)->latest()->first();
		
		$applicant? $applicant_payments = Invoice::where('payable_id',$applicant->id)->with('feeType','gatewayPayment')->get() : [];
		$student? $student_payments = Invoice::where('payable_id', $student->id)->orWhere('payable_id',$student->applicant->id)->with('feeType','gatewayPayment')->get() : [];

		$payments = [];
		if($applicant){
			$payments = $applicant_payments;	
		}elseif($student){
			$payments = $student_payments;
		}
		$filename = $student? $student->registration_number : $applicant->index_number;
		$headers = [
				  'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
				  'Content-type'        => 'text/csv',
				  'Content-Disposition' => 'attachment; filename='.$filename.'-Payments.csv',
				  'Expires'             => '0',
				  'Pragma'              => 'public'
		];

		$callback = function() use ($payments) 
		{
		  $file_handle = fopen('php://output', 'w');
		  fputcsv($file_handle, ['Invoice Number','Invoice Date','Receipt Date','Control Number','Payment Item','Bill Amount','Paid Amount','Balance']);
		  foreach ($payments as $row) { 
			fputcsv($file_handle, [$row->reference_no,date('Y-m-d',strtotime($row->created_at)),date('Y-m-d',strtotime($row->gatewayPayment->created_at)),
			$row->control_no,$row->feeType->name,$row->amount,$row->gatewayPayment->paid_amount,$row->amount - $row->gatewayPayment->paid_amount]);
		  }
		  fclose($file_handle);
		};			

		return response()->stream($callback, 200, $headers);
	  }	

    public function initiateControlNumberRequest(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;

 		$fee_amount = FeeAmount::whereHas('feeItem.feeType', function($query) use($request){$query->where('id',$request->fee_type_id);})
					  ->where('study_academic_year_id',$request->study_academic_year_id)->first();

					  
		if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('arc')) {
			$student = Student::with('applicant')->where('registration_number', $request->registration_number)->first();
		}else{
			$student = Student::with('applicant')->whereHas('applicant', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
					   ->where('registration_number', $request->registration_number)->first();
		}

		if($student){
			$email = $student->email? $student->email : 'admission@mnma.ac.tz';
			$fee_type = Invoice::where('payable_id',$student->id)->where('payable_type','student')
						->where('fee_type_id',$request->fee_type_id)->whereNotNull('control_no')
						->with('gatewayPayment')->latest()->first();
			//$datediff = 1;
			if($fee_type){
				
				$now = strtotime(date('Y-m-d'));
				$last_invoice = strtotime($fee_type->created_at);
				//$validity = strtotime($fee_amount->duration
				$datediff = $now - $last_invoice;
				$datediff = round(($datediff/(60 * 60 * 24)));				

				if($fee_amount->duration >= $datediff){
					if($fee_type->gateway_payment_id == null){
						return redirect()->back()->with('error','The student has an unpaid invoice for '.$fee_amount->feeItem->feeType->name);	
					}
					if($fee_type->gatewayPayment->paid_amount < $fee_type->gatewayPayment->bill_amount){
						return redirect()->back()->with('error','The student has an unpaid invoice for '.$fee_amount->feeItem->feeType->name);	
					}
				}
			}
			DB::beginTransaction();
					
			$invoice = new Invoice;
			$invoice->reference_no = 'MNMA-'.time();
			if(str_contains($student->applicant->nationality,'Tanzania')){
			   $invoice->amount = round($fee_amount->amount_in_tzs);
			   $invoice->actual_amount = $invoice->amount;
			   $invoice->currency = 'TZS';
			}else{
			   $invoice->amount = round($fee_amount->amount_in_usd*$usd_currency->factor);
			   $invoice->actual_amount = $invoice->amount;
			   $invoice->currency = 'TZS';//'USD';
			}
			$invoice->payable_id = $student->id;
			$invoice->payable_type = 'student';
			$invoice->applicable_id = $request->study_academic_year_id;
			$invoice->applicable_type = 'academic_year';
			$invoice->fee_type_id = $fee_amount->feeItem->fee_type_id;
			$invoice->save();

			$generated_by = 'SP';
			$approved_by = 'SP';
			$inst_id = config('constants.SUBSPCODE');

            $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name; 
            $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

			$this->requestControlNumber($request,
										$invoice->reference_no,
										$inst_id,
										$invoice->amount,
										$fee_amount->feeItem->feeType->description,
										$fee_amount->feeItem->feeType->gfs_code,
										$fee_amount->feeItem->feeType->payment_option,
										$student->id,
										$first_name.' '.$surname,
										$student->phone,
										$email,
										$generated_by,
										$approved_by,
										$fee_amount->feeItem->feeType->duration,
										$invoice->currency);
			DB::commit();
			
			return redirect()->to('finance/show-control-number?registration_number='.$student->registration_number)->with('message','Control number created successfully');		
		}
    }	

    public function showControlNumber(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
					  
		if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('arc')) {
			$student = Student::with(['applicant','studentShipStatus'])->where('registration_number', $request->get('registration_number'))->first();
		}else{
			$student = Student::with(['applicant','studentShipStatus'])->whereHas('applicant', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
					   ->where('registration_number', $request->get('registration_number'))->first();
		}

		$data = [
			'student'=>$request->get('registration_number')? $student : [],
			'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
			'fee_types'=>FeeType::all(),
			'invoice'=>$request->get('registration_number')? Invoice::with('feeType')->where('payable_id',$student->id)->where('payable_type','student')->latest()->first() :[]
		];
			
        return view('dashboard.finance.create-control-number',$data)->withTItle('Create Control Number');
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
