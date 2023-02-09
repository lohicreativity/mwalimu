<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Models\CampusFaculty;
use App\Domain\Settings\Actions\FacultyAction;
use App\Utils\Util;
use Validator, Auth;


class FacultyController extends Controller
{
    /**
     * Display a list of faculties
     */

    public function index()
    {
        $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('administrator')) {
            $faculties = Faculty::with(['campus'])->get();
        } else if (Auth::user()->hasRole('admission-officer')) {
            $faculties = Faculty::where('campus_id', $staff->campus_id)->get();
        }

        $data = [
            'campuses'  => Campus::all(),
            'faculties' => $faculties
        ];

    	return view('dashboard.settings.faculties', $data)->withTitle('faculties');
    }

     /**
     * Store faculty into database
     */
    public function store(Request $request)
    {
        if (Auth::user()->hasRole('administrator')) {
            $validation = Validator::make($request->all(),[
                'name'              =>  'required|unique:faculty',
                'abbreviation'      =>  'required',
                'campuses'          =>  'required',
            ]);
        } else if (Auth::user()->hasRole('admission-officer')) {
            $validation = Validator::make($request->all(),[
                'name'              =>  'required|unique:faculty',
                'abbreviation'      =>  'required'
            ]);
        }
    	

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FacultyAction)->store($request);

        return Util::requestResponse($request,'Faculty created successfully');
    }


     /**
     * Update specified faculty
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'abbreviation'=>'required',
            'campus'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FacultyAction)->update($request);


        return Util::requestResponse($request,'Faculty updated successfully');
    }

    /**
     * Remove the specified faculty
     */
    public function destroy($id)
    {
        // try{
        //     $faculty = faculty::findOrFail($id);
        //     if(CampusProgram::where('campus_id',$campus->id)->count() != 0){
        //        return redirect()->back()->with('error','Campus cannot be deleted because it has assigned programs');
        //     }else{
        //       $campus->delete();
        //       return redirect()->back()->with('message','Campus deleted successfully');
        //     }
        // }catch(Exception $e){
        //     return redirect()->back()->with('error','Unable to get the resource specified in this request');
        // }
    }

}
