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
use Validator, Auth;
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

class StaffController extends Controller
{
    /**
     * Display a list of staffs
     */
    public function index(Request $request)
    {
       if($request->has('query')){
          $staffs = Staff::with(['country','region','district','ward','designation','user.roles'])->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('pf_number','LIKE','%'.$request->get('query').'%')->paginate(20);
       }else{
          $staffs = Staff::with(['country','region','district','ward','designation','user.roles'])->paginate(20);
       }
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
    	return view('dashboard.human-resources.staffs',$data)->withTitle('staffs');
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
			$student_payer = Student::where('registration_number', $request->keyword)
			->orWhere('surname',$request->keyword)
			->with(['applicant','campusProgram.program','studentShipStatus'])->first();
			$applicant_payer = Applicant::where('index_number', $request->keyword)->orWhere('surname',$request->keyword)->first();
			if(!$student_payer && !$applicant_payer){
				return redirect()->back()->with('error','There is no such a payer');
			}
			$applicant_payer? $paid_as_applicant = Invoice::where('payable_id',$applicant_payer->id)->with('feeType','gateway_payment')->get() : $paid_as_applicant = null;
			$student_payer? $paid_as_student = Invoice::where('payable_id', $student_payer->id)->with('feeType','gatewayPayment')->get() : $student_payer = null;
			return $paid_as_student;
			$data = [
				'payer'=>$student_payer? $student_payer : $applicant_payer,
				'category'=>$student_payer? 'student' : 'applicant'
			];
		}else{
			$data = [
				'payer'=>[]
			];			
		}

        return view('dashboard.finance.payer-details',$data)->withTitle('Payer Details');   
    }	
}
