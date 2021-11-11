<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Actions\AssessmentPlanAction;
use App\Utils\Util;
use Validator;

class AssessmentPlanController extends Controller
{
    /**
     * Store plan into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'marks'=>'required|numeric'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
 
        // Check is assessment plan does not exceed module weight
        $module = ModuleAssignment::find($request->get('module_assignment_id'))->module;
        $plans = AssessmentPlan::where('module_assignment_id',$request->get('module_assignment_id'))->get();
        $sum = 0;
        foreach($plans as $plan){
           $sum += $plan->marks;
        }
        $sum += $request->get('marks');
        if($sum > $module->course_work){
        	if($request->ajax()){
                return response()->json(array('error_messages'=>'Module cannot exceed module weight'));
             }else{
                return redirect()->back()->with('error','Module cannot exceed module weight');
             }
        }


        (new AssessmentPlanAction)->store($request);

        return Util::requestResponse($request,'Assessment plan created successfully');
    }

    /**
     * Update specified plan
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'marks'=>'required|numeric'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
        
        // Check is assessment plan does not exceed module weight
        $module = ModuleAssignment::find($request->get('module_assignment_id'))->module;
        $plans = AssessmentPlan::where('module_assignment_id',$request->get('module_assignment_id'))->get();
        $sum = 0;
        foreach($plans as $plan){
           $sum += $plan->marks;
        }
        $sum += $request->get('marks');
        if($sum > $module->course_work){
        	if($request->ajax()){
                return response()->json(array('error_messages'=>'Module cannot exceed module weight'));
             }else{
                return redirect()->back()->with('error','Module cannot exceed module weight');
             }
        }

        (new AssessmentPlanAction)->update($request);

        return Util::requestResponse($request,'Assessment plan updated successfully');
    }

    /**
     * Remove the specified plan
     */
    public function destroy(Request $request, $id)
    {
        try{
            $plan = AssessmentPlan::findOrFail($id);
            $plan->delete();
            return redirect()->back()->with('message','Assessment plan deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
