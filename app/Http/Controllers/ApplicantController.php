<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\NextOfKin;
use App\Domain\Application\Models\ApplicationCycle;
use App\Domain\Application\Actions\ApplicantAction;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Models\FeeAmount;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ApplicantController extends Controller
{
    /**
     * Display a list of applicants
     */
    public function index()
    {
    	$data = [
           'applicants'=>Applicant::paginate(20)
    	];
    	return view('dashboard.application.applicants',$data)->withTitle('Applicants');
    }


    /**
     * Display login form
     */
    public function showLogin(Request $request)
    {
        return view('auth.applicant-login')->withTitle('Student Login');
    }

    /**
     * Authenticate student
     */
    public function authenticate(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'index_number'=>'required',
            'password'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $credentials = [
            'username'=>$request->get('index_number'),
            'password'=>$request->get('password')
        ];

        if(Auth::attempt($credentials)){
            return redirect()->to('application/dashboard')->with('message','Logged in successfully');
        }else{
           return redirect()->back()->with('error','Incorrect index number or password');
        }
    }

    /**
     * Edit basic information
     */
    public function editBasicInfo(Request $request)
    {
        $data = [
           'applicant'=>User::find(Auth::user()->id)->applicant,
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'disabilities'=>DisabilityStatus::all(),
        ];

        return view('dashboard.application.edit-basic-information',$data)->withTitle('Edit Basic Information');
    }

       /**
     * Edit basic information
     */
    public function editNextOfKin(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicant;
        $data = [
           'applicant'=>$applicant,
           'next_of_kin'=>NextOfKin::find($applicant->next_of_kin_id),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'disabilities'=>DisabilityStatus::all(),
        ];

        return view('dashboard.application.edit-next-of-kin',$data)->withTitle('Edit Next of Kin');
    }

    /**
     * Make application payment
     */
    public function payments(Request $request)
    {
        $data = [
           'fee_type'=>FeeType::with('feeItems')->where('name','LIKE','%Application%')->where('study_academic_year_id',$study_academic_year->id)->first()
        ];
        return view('dashboard.application.payments',$data)->withTitle('Payments');
    }

    /**
     * Store applicant into database
     */
    public function updateBasicInfo(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'birth_date'=>'required',
            'address'=>'required',
            'nationality'=>'required',
            'street'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ApplicantAction)->update($request);

        return Util::requestResponse($request,'Applicant updated successfully');
    }

    /**
     * Update specified applicant
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'birth_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ApplicantAction)->update($request);

        return Util::requestResponse($request,'applicant updated successfully');
    }

    /**
     * Remove the specified applicant
     */
    public function destroy(Request $request, $id)
    {
        try{
            $applicant = Applicant::findOrFail($id);
            $applicant->delete();
            return redirect()->back()->with('message','Applicant deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
