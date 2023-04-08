<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Semester;
use App\Domain\Settings\Models\Campus;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Finance\Actions\ProgramFeeAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ProgramFeeController extends Controller
{
    /**
     * Display a list of amounts
     */
    public function index(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
      $application_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','ACTIVE')->first();
      if(!$application_window){
          return redirect()->back()->with('error','No active application window');
      }
      $ac_year = date('Y',strtotime($application_window->end_date));
      $study_ac_yr = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
            $query->where('year','LIKE','%'.$ac_year.'/%');
            
      })->first();
      if(!$study_ac_yr){
            return redirect()->back()->with('error','No corresponding academic year');
      }
	  
    	$data = [
           'fees'=>$request->get('query')? ProgramFee::wherehas('campusProgram',function($query) use($request){
                $query->where('campus_id',$request->get('campus_id'));
           })->whereHas('studyAcademicYear.academicYear',function($query) use($request){
                $query->where('year','LIKE','%'.$request->get('query').'%');
           })->with('campusProgram.program')->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->paginate(20) : ProgramFee::wherehas('campusProgram',function($query) use($request){
                $query->where('campus_id',$request->get('campus_id'));
           })->with('campusProgram.program')->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->paginate(20),
           'campus_programs'=>CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get(),
           'fee_items'=>FeeItem::all(),
           'ac_year'=>$study_ac_yr,
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'semesters'=>Semester::all(),
           'campuses'=>Campus::all(),
           'staff'=>$staff,
           'request'=>$request
    	];
	  
    	return view('dashboard.finance.program-fees',$data)->withTitle('Program Fees');
    }

    /**
     * Display fee structure
     */
    public function feeStructure(Request $request)
    {
    	   $data = [
            'fees'=>ProgramFee::with(['StudyAcademicYear.academicYear'])->paginate(20)
         ];
         return view('dashboard.finance.program-fee-structure',$data)->withTitle('Fee Structure');
    }

    /**
     * Store amount into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount_in_tzs'=>'required',
            'amount_in_usd'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ProgramFee::where('campus_program_id',$request->get('campus_program_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->count() != 0){
            return redirect()->back()->with('error','Programme fee already exists');
        }

        (new ProgramFeeAction)->store($request);

        return Util::requestResponse($request,'Program fee created successfully');
    }

    /**
     * Store as previous
     */
    public function storeAsPrevious(Request $request)
    {
         $previous_ac_yr = StudyAcademicYear::latest()->offset(1)->first();
         if(!$previous_ac_yr){
             return redirect()->back()->with('error','No previous academic year');
         }
         $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
         $prog_fees = ProgramFee::where('study_academic_year_id',$previous_ac_yr->id)->get();
         foreach($prog_fees as $fee){
            $pf = new ProgramFee;
            $pf->study_academic_year_id = $study_academic_year->id;
            $pf->year_of_study = $fee->year_of_study;
            $pf->amount_in_tzs = $fee->amount_in_tzs;
            $pf->amount_in_usd = $fee->amount_in_usd;
            $pf->fee_item_id = $fee->fee_item_id;
            $pf->campus_program_id = $fee->campus_program_id;
            $pf->save();
         }
         return redirect()->back()->with('message','Programme fees stored as previously successfully');
    }

    /**
     * Update specified program fee
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount_in_tzs'=>'required',
            'amount_in_usd'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        if(ProgramFee::isUsed($request->get('campus_program_id'),$request->get('year'),$request->get('year_of_study'),$request->get('study_academic_year_id'))){
             return redirect()->back()->with('error','Programme fee cannot be updated because it has already been used');
        }


        (new ProgramFeeAction)->update($request);

        return Util::requestResponse($request,'Program fee updated successfully');
    }

    /**
     * Remove the specified program fee
     */
    public function destroy(Request $request, $id)
    {
        try{
            if(ProgramFee::isUsed($request->get('campus_program_id'),$request->get('year'),$request->get('year_of_study'),$request->get('study_academic_year_id'))){
               return redirect()->back()->with('error','Programme fee cannot be deleted because it has already been used');
            }
            $fee = ProgramFee::findOrFail($id);
            $fee->delete();
            return redirect()->back()->with('message','Program fee deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
