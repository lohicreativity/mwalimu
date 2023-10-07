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
use App\Domain\Academic\Models\Award;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Settings\Models\Intake;
use App\Utils\Util;

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
      $app_window = ApplicationWindow::where('campus_id', $staff->campus_id)->where('status','ACTIVE')->latest()->first();
   //    $boolFlag = false
   //    foreach($app_window as $window){
   //       if($window->status == 'ACTIVE' && $window->intake->name == 'September'){
   //          $boolFlag = true
   //       }else{
   //          $boolFlag = false
   //       }
   //    }
   // // dd(json_encode(Intake::whereId($app_window[0]->intake_id)->pluck('name')[0]));
      $data = [
           'campus_id'  => $staff->campus_id,
           'campuses'=>Campus::all(),
           'app_window' => $app_window,
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'campus'=>Campus::find($request->get('campus_id')),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'orientation_dates'=>Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')? SpecialDate::where('name','Orientation')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get() :
            SpecialDate::where('name','Orientation')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_id',$staff->campus_id)->get(),
           'request'=>$request,
           'awards'=>Award::all(),
           'intakes'=>Intake::all(),
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
      // dd($request->all());
      $validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'campus_id'=>'required',
            'intake'=>'required',
            'applicable_level'=>'required',
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

        $orientation_dates = SpecialDate::where('intake',$request->get('intake'))->where('study_academic_year_id',$request->get('study_academic_year_id'))
                                           ->where('name','Orientation')->where('campus_id',$request->get('campus_id'))->get();

   $find_level = false;

        if(count($orientation_dates) > 0){
            foreach($orientation_dates as $orientation_date){
               foreach($request->get('applicable_level') as $level){
                  if(in_array($level, unserialize($orientation_date->applicable_levels))){
                     return redirect()->back()->with('error','Orientation date for '.$level.' in '.$request->get('intake').' intake of this academic has already been created');
                  }else{
                     $find_level = true;
                  }
               }
            }
         
            if($find_level){
               $group_id = Util::randString(100);
               $date = new SpecialDate;
               $date->date = DateMaker::toDBDate($request->get('orientation_date'));
               $date->name = $request->get('name');
               $date->campus_id = $request->get('campus_id');
               $date->study_academic_year_id = $request->get('study_academic_year_id');
               $date->intake = $request->get('intake');
               $date->applicable_levels = serialize($request->get('applicable_level'));
               $date->group_id = $group_id;
               $date->save();
      
               $date = new SpecialDate;
               $date->date = Carbon::parse($request->get('orientation_date'))->addDays(13)->format('Y-m-d');
               $date->begin_date = DateMaker::toDBDate($request->get('orientation_date'));
               $date->name = 'New Registration Period';
               $date->campus_id = $request->get('campus_id');
               $date->study_academic_year_id = $request->get('study_academic_year_id');
               $date->intake = $request->get('intake');
               $date->applicable_levels = serialize($request->get('applicable_level'));
               $date->group_id = $group_id;
               $date->save();
      
               $date = new SpecialDate;
               $date->date = Carbon::parse($request->get('orientation_date'))->addDays(20)->format('Y-m-d');
               $date->begin_date = Carbon::parse($request->get('orientation_date'))->addDays(6)->format('Y-m-d');;
               $date->name = 'Continueing Registration Period';
               $date->campus_id = $request->get('campus_id');
               $date->study_academic_year_id = $request->get('study_academic_year_id');
               $date->intake = $request->get('intake');
               $date->applicable_levels = serialize($request->get('applicable_level'));
               $date->group_id = $group_id;
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
               $year->save();
      
               return redirect()->back()->with('message','Orientation date created successfully');
           }

         }else {

         $group_id = Util::randString(100);
         $date = new SpecialDate;
         $date->date = DateMaker::toDBDate($request->get('orientation_date'));
         $date->name = $request->get('name');
         $date->campus_id = $request->get('campus_id');
         $date->study_academic_year_id = $request->get('study_academic_year_id');
         $date->intake = $request->get('intake');
         $date->applicable_levels = serialize($request->get('applicable_level'));
         $date->group_id = $group_id;
         $date->save();

         $date = new SpecialDate;
         $date->date = Carbon::parse($request->get('orientation_date'))->addDays(13)->format('Y-m-d');
         $date->begin_date = DateMaker::toDBDate($request->get('orientation_date'));
         $date->name = 'New Registration Period';
         $date->campus_id = $request->get('campus_id');
         $date->study_academic_year_id = $request->get('study_academic_year_id');
         $date->intake = $request->get('intake');
         $date->applicable_levels = serialize($request->get('applicable_level'));
         $date->group_id = $group_id;
         $date->save();

         $date = new SpecialDate;
         $date->date = Carbon::parse($request->get('orientation_date'))->addDays(20)->format('Y-m-d');
         $date->begin_date = Carbon::parse($request->get('orientation_date'))->addDays(6)->format('Y-m-d');;
         $date->name = 'Continueing Registration Period';
         $date->campus_id = $request->get('campus_id');
         $date->study_academic_year_id = $request->get('study_academic_year_id');
         $date->intake = $request->get('intake');
         $date->applicable_levels = serialize($request->get('applicable_level'));
         $date->group_id = $group_id;
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
         $year->save();

         return redirect()->back()->with('message','Orientation date created successfully');
        }
    }

     /**
     * Update orientation date
     */
    public function updateOrientationDate(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'orientation_date'=>'required',
            'campus_id'=>'required',
            'applicable_level'=>'required'
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
        
        $special_date = SpecialDate::find($request->get('special_date_id'));

        $orientation_dates = SpecialDate::where('intake',$request->get('intake'))->where('study_academic_year_id',$request->get('study_academic_year_id'))
                                          ->where('name','Orientation')->where('campus_id',$request->get('campus_id'))->where('id','!=',$request->get('special_date_id'))->get();
        
        ///$date = SpecialDate::find($request->get('special_date_id'));
        $find_level = false;

        if(count($orientation_dates) > 0){
            foreach($orientation_dates as $orientation_date){
               foreach($request->get('applicable_level') as $level){
                  if(in_array($level, unserialize($orientation_date->applicable_levels))){
                     return redirect()->back()->with('error','Orientation date for '.$level.' in '.$request->get('intake').' intake of this academic has already been created');
                  }else{
                     $find_level = true;
                  }
               }
            }
         
            if($find_level){
               $special_date->date = DateMaker::toDBDate($request->get('orientation_date'));
               $special_date->name = $request->get('name');
               $special_date->campus_id = $request->get('campus_id');
               $special_date->study_academic_year_id = $request->get('study_academic_year_id');
               $special_date->intake = $request->get('intake');
               $special_date->applicable_levels = serialize($request->get('applicable_level'));
               $special_date->save();
       
               $new_reg_date = SpecialDate::where('group_id',$special_date->group_id)->where('name','New Registration Period')->first();
               $new_reg_date->date = Carbon::parse($request->get('orientation_date'))->addDays(13)->format('Y-m-d');
               $new_reg_date->begin_date = DateMaker::toDBDate($request->get('orientation_date'));
               $new_reg_date->campus_id = $request->get('campus_id');
               $new_reg_date->intake = $request->get('intake');
               $new_reg_date->applicable_levels = serialize($request->get('applicable_level'));
               $new_reg_date->save();
       
               $cont_reg_date = SpecialDate::where('group_id',$special_date->group_id)->where('name','Continueing Registration Period')->first();
               $cont_reg_date->date = Carbon::parse($request->get('orientation_date'))->addDays(20)->format('Y-m-d');
               $cont_reg_date->begin_date = Carbon::parse($request->get('orientation_date'))->addDays(6)->format('Y-m-d');;
               $cont_reg_date->campus_id = $request->get('campus_id');
               $cont_reg_date->intake = $request->get('intake');
               $cont_reg_date->applicable_levels = serialize($request->get('applicable_level'));
               $cont_reg_date->save();
       
               return redirect()->back()->with('message','Orientation updated successfully');
           }

         }else {
        
            $special_date->date = DateMaker::toDBDate($request->get('orientation_date'));
            $special_date->name = $request->get('name');
            $special_date->campus_id = $request->get('campus_id');
            $special_date->study_academic_year_id = $request->get('study_academic_year_id');
            $special_date->intake = $request->get('intake');
            $special_date->applicable_levels = serialize($request->get('applicable_level'));
            $special_date->save();
    
            $new_reg_date = SpecialDate::where('group_id',$special_date->group_id)->where('name','New Registration Period')->first();
            $new_reg_date->date = Carbon::parse($request->get('orientation_date'))->addDays(13)->format('Y-m-d');
            $new_reg_date->begin_date = DateMaker::toDBDate($request->get('orientation_date'));
            $new_reg_date->campus_id = $request->get('campus_id');
            $new_reg_date->intake = $request->get('intake');
            $new_reg_date->applicable_levels = serialize($request->get('applicable_level'));
            $new_reg_date->save();
    
            $cont_reg_date = SpecialDate::where('group_id',$special_date->group_id)->where('name','Continueing Registration Period')->first();
            $cont_reg_date->date = Carbon::parse($request->get('orientation_date'))->addDays(20)->format('Y-m-d');
            $cont_reg_date->begin_date = Carbon::parse($request->get('orientation_date'))->addDays(6)->format('Y-m-d');;
            $cont_reg_date->campus_id = $request->get('campus_id');
            $cont_reg_date->intake = $request->get('intake');
            $cont_reg_date->applicable_levels = serialize($request->get('applicable_level'));
            $cont_reg_date->save();
    
            return redirect()->back()->with('message','Orientation updated successfully');
        }

    }
}
