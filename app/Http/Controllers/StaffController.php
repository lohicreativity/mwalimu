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
use App\Domain\HumanResources\Actions\StaffAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class StaffController extends Controller
{
    /**
     * Display a list of staffs
     */
    public function index()
    {
    	$data = [
           'staffs'=>Staff::with(['country','region','district','ward','designation'])->paginate(20),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'designations'=>Designation::all(),
           'disabilities'=>DisabilityStatus::all(),
           'campuses'=>Campus::all(),
           'staff'=>User::find(Auth::user()->id)->staff
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
               'staff'=>Staff::with(['disabilityStatus','country','region','district','designation'])->find($id)
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
               'staff'=>Staff::findOrFail($id),
               'countries'=>Country::all(),
               'regions'=>Region::all(),
               'districts'=>District::all(),
               'wards'=>Ward::all(),
               'designations'=>Designation::all(),
               'disabilities'=>DisabilityStatus::all(),
               'campuses'=>Campus::all()
            ];
            return view('dashboard.human-resources.edit-staff',$data)->withTitle('Edit Staff');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
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
            'phone'=>'required',
            'nin'=>'required'
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
            'phone'=>'required',
            'nin'=>'required'
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
}
