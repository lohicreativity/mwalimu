<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\SpecialDate;
use App\Utils\DateMaker;
use Carbon\Carbon;
use App\Models\User;
use Validator, Auth;

class SpecialDateController extends Controller
{
	/**
	 * Disaplay special dates
	 */
	public function index(Request $request)
	{
        $data = [
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'campus'=>Campus::find($request->get('campus_id')),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'graduation_date'=>SpecialDate::where('name','Graduation')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_id',$request->get('campus_id'))->first(),
           'request'=>$request
        ];
        return view('dashboard.settings.graduation-date',$data)->withTitle('Graduation Date');
	}

  /**
   * Display registration deadline
   */
  public function showRegistrationDeadline(Request $request)
  {
      $data = [
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'campus'=>Campus::find($request->get('campus_id')),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'registration_date'=>SpecialDate::where('name','New Registration Period')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_id',$request->get('campus_id'))->first(),
           'request'=>$request
        ];
        return view('dashboard.registration.registration-date',$data)->withTitle('Registration Deadline');
  }

  /**
   * Display registration deadline
   */
  public function showOrientationDate(Request $request)
  {
      $staff = User::find(Auth::user()->id)->staff;

      $data = [
           'campus_id'  => $staff->campus_id,
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'campus'=>Campus::find($request->get('campus_id')),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'orientation_date'=>SpecialDate::where('name','Orientation')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_id',$request->get('campus_id'))->first(),
           'request'=>$request
        ];
        return view('dashboard.registration.orientation-date',$data)->withTitle('Orientation Date');
  }

    /**
     * Store graduation date
     */
    public function storeGraduationDate(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'graduation_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $date = new SpecialDate;
        $date->date = DateMaker::toDBDate($request->get('graduation_date'));
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        return redirect()->back()->with('message','Graduation date created successfully');
    }

    /**
     * Store graduation date
     */
    public function updateGraduationDate(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'graduation_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $date = SpecialDate::find($request->get('special_date_id'));
        $date->date = DateMaker::toDBDate($request->get('graduation_date'));
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        return redirect()->back()->with('message','Graduation date updated successfully');
    }

    /**
     * Store registration deadline
     */
    public function storeRegistrationDeadline(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'registration_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime($request->get('registration_date')) < strtotime(now()->format('Y-m-d'))){
          return redirect()->back()->with('error','Registration deadline cannot be less than today date');
        }

        $date = new SpecialDate;
        $date->date = DateMaker::toDBDate($request->get('registration_date'));
        $date->begin_date = Carbon::parse($request->get('registration_date'))->format('Y-m-d')->subDays(13);
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        return redirect()->back()->with('message','Registration deadline created successfully');
    }

     /**
     * Update registration deadline
     */
    public function updateRegistrationDeadline(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'registration_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime($request->get('registration_date')) < strtotime(now()->format('Y-m-d'))){
          return redirect()->back()->with('error','Registration deadline cannot be less than today date');
        }

        $date = SpecialDate::find($request->get('special_date_id'));
        $date->date = DateMaker::toDBDate($request->get('registration_date'));
        $date->begin_date = Carbon::parse($request->get('registration_date'))->subDays(13)->format('Y-m-d');
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        return redirect()->back()->with('message','Registration deadline updated successfully');
    }

    /**
     * Store orientation date
     */
    public function storeOrientationDate(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'orientation_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime($request->get('orientation_date')) < strtotime(now()->format('Y-m-d'))){
          return redirect()->back()->with('error','Orientation date cannot be less than today date');
        }

        $date = new SpecialDate;
        $date->date = DateMaker::toDBDate($request->get('orientation_date'));
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        $date = new SpecialDate;
        $date->date = Carbon::parse($request->get('orientation_date'))->addDays(13)->format('Y-m-d');
        $date->begin_date = DateMaker::toDBDate($request->get('orientation_date'));
        $date->name = 'New Registration Period';
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        $date = new SpecialDate;
        $date->date = Carbon::parse($request->get('orientation_date'))->addDays(20)->format('Y-m-d');
        $date->begin_date = Carbon::parse($request->get('orientation_date'))->addDays(6)->format('Y-m-d');;
        $date->name = 'Continueing Registration Period';
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        // $ac_year = new AcademicYear;
        // $ac_year->year = date('Y',strtotime($request->get('orientation_date'))).'/'.date('Y',strtotime($request->get('orientation_date')))+1;
        // $ac_year->save();
        $ac_year = AcademicYear::where('year',date('Y',strtotime($request->get('orientation_date'))).'/'.date('Y',strtotime($request->get('orientation_date')))+1)->first();

        // $year = new StudyAcademicYear;
        $year = StudyAcademicYear::whereHas('academicYear',function($query) use($request){
            $query->where('year',date('Y',strtotime($request->get('orientation_date'))).'/'.date('Y',strtotime($request->get('orientation_date')))+1);
        })->first();
        $year->academic_year_id = $ac_year->id;
        $year->begin_date = Carbon::parse($request->get('orientation_date'))->format('Y-m-d');
        $year->end_date = Carbon::parse($request->get('orientation_date'))->addMonths(12)->format('Y-m-d');
        $year->status = 'INACTIVE';
        $year->save();

        return redirect()->back()->with('message','Orientation date created successfully');
    }

     /**
     * Update orientation date
     */
    public function updateOrientationDate(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'orientation_date'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime($request->get('orientation_date')) < strtotime(now()->format('Y-m-d'))){
          return redirect()->back()->with('error','Orientation date cannot be less than today date');
        }

        $date = SpecialDate::find($request->get('special_date_id'));
        $date->date = DateMaker::toDBDate($request->get('orientation_date'));
        $date->name = $request->get('name');
        $date->campus_id = $request->get('campus_id');
        $date->study_academic_year_id = $request->get('study_academic_year_id');
        $date->save();

        return redirect()->back()->with('message','Orientation updated successfully');
    }
}
