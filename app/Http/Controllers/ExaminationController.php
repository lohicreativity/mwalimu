<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Examination;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Actions\ExaminationAction;
use App\Utils\Util;
use Validator;

class ExaminationController extends Controller
{
    /**
     * Display a list of examinations
     */
    public function index()
    {
    	$data = [
           'examinations'=>Examination::paginate(20)
    	];
    	return view('dashboard.academic.examinations',$data)->withTitle('examinations');
    }

    /**
     * Store examination into database
     */
    public function store(Request $request)
    {
    	$validation = $request->validate($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new examinationAction)->store($request);

        return Util::requestResponse($request,'Examination created successfully');
    }

    /**
     * Update specified examination
     */
    public function update(Request $request)
    {
    	$validation = $request->validate($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ExaminationAction)->update($request);

        return Util::requestResponse($request,'Examination updated successfully');
    }

    /**
     * Remove the specified examination
     */
    public function destroy(Request $request, $id)
    {
        try{
            $examination = Examination::findOrFail($id);
            $examination->delete();
            return redirect()->back()->with('message','Examination deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
}
