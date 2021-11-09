<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\HumanResources\Models\Designation;
use App\Domain\HumanResources\Actions\StaffAction;
use App\Utils\Util;
use Validator;

class StaffController extends Controller
{
    /**
     * Display a list of staffs
     */
    public function index()
    {
    	$data = [
           'staffs'=>staff::paginate(20)
    	];
    	return view('dashboard.human-resources.staffs',$data)->withTitle('staffs');
    }

    /**
     * Store staff into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'last_name'=>'required',
            'birth_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new StaffAction)->store($request);

        return Util::requestResponse($request,'Staff updated successfully');
    }

    /**
     * Update specified staff
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'last_name'=>'required',
            'birth_date'=>'required'
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
            $staff->delete();
            return redirect()->back()->with('message','Staff deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
