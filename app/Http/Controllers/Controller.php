<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\ApplicationWindow;
use Illuminate\Contracts\Auth\Guard;
use App\Models\User;
use Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected $user;

    public function __construct(Guard $guard)
    {
    	$this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
            $latest_academic_year = StudyAcademicYear::latest()->first();
	    	$semester = Semester::where('status','ACTIVE')->first();
	    	
	    	
	    	if($this->user){
	    	  $staff = User::find($this->user->id)->staff;

	    	  if($staff){
	    	     session(['staff_campus_id'=>$staff->campus_id]);
	    	     $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','ACTIVE')->latest()->first();
	    	    if($application_window){
		          session(['active_window_id'=>$application_window->id]);
		        }else{
		          session(['active_window_id'=>null]);
		        }
	    	  }else{
	    	  	 session(['staff_campus_id'=>null]);
	    	  }

	        }else{
	          session(['staff_campus_id'=>null]);
	        } 
	        if($study_academic_year){
	    	  session(['active_academic_year_id'=>$study_academic_year->id]);
	        }else{
	          session(['active_academic_year_id'=>null]);
	        }
	        if($latest_academic_year){
	    	  session(['latest_academic_year_id'=>$latest_academic_year->id]);
	        }else{
	          session(['latest_academic_year_id'=>null]);
	        }
	        if($semester){
	          session(['active_semester_id'=>$semester->id]);
	        }else{
	          session(['active_semester_id'=>null]);
	        }

	        

            return $next($request);
        });

    	
    }

    /**
     * Return a JSON response for success.
     *
     * @param  array  $data
     * @param  string $code
     * @return \Illuminate\Http\JsonResponse
     */
	public function success($data, $code = 200){
		return response()->json(['error' => false, 'data' => $data], $code);
	}
	
    /**
     * Return a JSON response for error.
     *
     * @param  array  $message
     * @param  string $code
     * @return \Illuminate\Http\JsonResponse
     */
	public function error($message, $code = 500){
		return response()->json(['error' => true, 'message' => $message], $code);
	}
}
