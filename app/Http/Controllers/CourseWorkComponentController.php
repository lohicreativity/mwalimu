<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CourseWorksComponent;
use App\Domain\Academic\Actions\CourseWorkComponentAction;
use App\Utils\Util;
use Validator;

class CourseWorkComponentController extends Controller
{
    /**
     * Store department into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'tests'=>'required',
            'assignments'=>'required',
            'quizes'=>'required',
            'portfolios'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

		if($request->assignments == 0 && $request->quizes == 0 && $request->portfolios == 0){
			return redirect()->back()->with('error','You must specify atleast 3 components');
		}
        (new CourseWorkComponentAction)->store($request);

        return Util::requestResponse($request,'Course work components created successfully');
    }

}
