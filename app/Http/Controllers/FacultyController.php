<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Actions\FacultyAction;
use Validator;


class FacultyController extends Controller
{
    /**
     * Display a list of faculties
     */

    public function index()
    {
        $data = [
            'campuses' => Campus::all()
        ];

    	return view('dashboard.settings.faculties', $data)->withTitle('faculties');
    }

     /**
     * Store faculty into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'faculty_name'=>'required|unique:faculty',
            'campuses'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CampusAction)->store($request);

        return Util::requestResponse($request,'Faculty created successfully');
    }

}
