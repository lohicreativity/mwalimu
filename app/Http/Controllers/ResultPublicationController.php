<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Actions\ResultPublicationAction;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Utils\Util;
use Validator;

class ResultPublicationController extends Controller
{
    /**
     * Display a list of publications
     */
    public function index()
    {
    	$data = [
    	   'semesters'=>Semester::all(),
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'publications'=>ResultPublication::with(['semester','StudyAcademicYear.academicYear','ntaLevel','campus'])->paginate(20)
    	];
    	return view('dashboard.academic.results-publications',$data)->withTitle('Results Publications');
    }

    /**
     * Store publication into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'semester_id'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->count() != 0){
        	return redirect()->back()->with('error','Result publication already exists');
        }


        (new ResultPublicationAction)->store($request);

        return Util::requestResponse($request,'Results publication created successfully');
    }

    /**
     * Update specified publication
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'semester_id'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ResultPublicationAction)->update($request);

        return Util::requestResponse($request,'Results publication updated successfully');
    }

     /**
     * Publish results publication
     */
    public function publish($id)
    {
        try{
            $publication = ResultPublication::findOrFail($id);
            $publication->status = 'PUBLISHED';
            $publication->save();

            return redirect()->back()->with('message','Results published successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Publish results publication
     */
    public function unpublish($id)
    {
        try{
            $publication = ResultPublication::findOrFail($id);
            $publication->status = 'UNPUBLISHED';
            $publication->save();

            return redirect()->back()->with('message','Results unpublished successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified publication
     */
    public function destroy(Request $request, $id)
    {
        try{
            $publication = ResultPublication::findOrFail($id);
            $publication->delete();
            return redirect()->back()->with('message','Results publication deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
