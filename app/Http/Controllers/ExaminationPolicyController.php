<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Actions\ExaminationPolicyAction;
use App\Utils\Util;
use Validator;

class ExaminationPolicyController extends Controller
{
    /**
     * Display a list of examinations
     */
    public function index(Request $request)
    {
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'policies'=>ExaminationPolicy::paginate(20)
    	];
    	return view('dashboard.academic.examination-policies',$data)->withTitle('Examination Policies');
    }

    /**
     * Store examination into database
     */
    public function store(Request $request)
    {
    	$validation = $request->validate($request->all(),[
            'module_pass_mark'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ExaminationPolicyAction)->store($request);

        return Util::requestResponse($request,'Examination policy created successfully');
    }

    /**
     * Update specified examination
     */
    public function update(Request $request)
    {
    	$validation = $request->validate($request->all(),[
            'module_pass_mark'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ExaminationPolicyAction)->update($request);

        return Util::requestResponse($request,'Examination policy updated successfully');
    }

    /**
     * Remove the specified examination
     */
    public function destroy(Request $request, $id)
    {
        try{
            $policy = ExaminationPolicy::findOrFail($id);
            $policy->delete();
            return redirect()->back()->with('message','Examination policy deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
