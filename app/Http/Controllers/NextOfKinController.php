<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Actions\NextOfKinAction;
use App\Domain\Application\Models\Applicant;
use App\Utils\Util;
use Validator;

class NextOfKinController extends Controller
{
    /**
     * Store next of kin
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'address'=>'required',
            'nationality'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new NextOfKinAction)->store($request);
		
		$applicant = Applicant::find($request->get('applicant_id'));
        
		if($applicant->is_transfered == 1){
			return redirect()->to('application/results')->with('message','Next of Kin created successfully');
		}else{
           return redirect()->to('application/payments')->with('message','Next of Kin created successfully');
		}
    }

    /**
     * Update next of kin
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'address'=>'required',
            'nationality'=>'required',
			'phone' => 'required|digits:10|regex:/(0)[0-9]/',
            'address'=>'required|integer'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new NextOfKinAction)->update($request);

        $applicant = Applicant::find($request->get('applicant_id'));
        
		if($applicant->is_transfered == 1){
			return redirect()->to('application/results')->with('message','Next of Kin created successfully');
		}else{
            return Util::requestResponse($request,'Next of Kin updated successfully');
		}
    }
}
