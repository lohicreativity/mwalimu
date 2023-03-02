<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Actions\CampusAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class CampusController extends Controller
{
    /**
     * Display a list of campuses
     */
    public function index()
    {
    	$data = [
           'campuses'=>Campus::with(['campusPrograms.program'])->paginate(20),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.settings.campuses',$data)->withTitle('Campuses');
    }

    /**
     * Store campus into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:campuses',
            'abbreviation'=>'required',
            'email'=>'required|email',
            'phone'=>'required|digits:10|regex:/(0)[0-9]/'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CampusAction)->store($request);

        return Util::requestResponse($request,'Campus created successfully');
    }

    /**
     * Update specified campus
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'update-name'=>'required',
            'update-abbreviation'=>'required',
            'update-email'=>'required|email',
            'update-phone'=>'required|digits:10|regex:/(0)[0-9]/'
      ], 
      $messages = [
         'update-name.required'           => 'The campus name field is required.',
         'update-abbreviation.required'   => 'The campus abbreviation field is required.',
         'update-email.required'          => 'The campus email field is required.',
         'update-phone.required'          => 'The campus phone field is required.',
      ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CampusAction)->update($request);

        return Util::requestResponse($request,'Campus updated successfully');
    }

    /**
     * Remove the specified campus
     */
    public function destroy($id)
    {
        try{
            $campus = Campus::findOrFail($id);
            if(CampusProgram::where('campus_id',$campus->id)->count() != 0){
               return redirect()->back()->with('error','Campus cannot be deleted because it has assigned programs');
            }else{
              $campus->delete();
              return redirect()->back()->with('message','Campus deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
