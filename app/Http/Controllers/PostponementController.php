<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Domain\Academic\Actions\PostponementAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Validator, Auth;


class PostponementController extends Controller
{
    /**
     * Display a list of postponements
     */
    public function index(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        if (Auth::user()->hasRole('administrator')|| Auth::user()->hasRole('arc')) {
            $postponements = $request->get('query')? Postponement::whereHas('student',function($query) use($request){
                $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
          })->with(['student','StudyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get() : Postponement::with(['student','StudyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('postponed_by_user_id')->get();

        }elseif(Auth::user()->hasRole('admission-officer')){
            $postponements = $request->get('query')? Postponement::whereHas('student',function($query) use($request){
                $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
          })->whereHas('student.applicant',function($query) use($staff) {$query->where('campus_id',$staff->campus_id);})
          ->with(['student','StudyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get() : Postponement::with(['student','StudyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('postponed_by_user_id')->get();
return $postponements;
        }elseif(Auth::user()->hasRole('hod')){
        }else{
            $postponements = [];
        }
    	$data = [
    	     'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'postponements'=>$postponements,
           'semesters'=>Semester::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.postponements',$data)->withTitle('Postponements');
    }

    /**
     * Display a list of postponements
     */
    public function resumptions(Request $request)
    {
      $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'postponements'=>Postponement::with(['student','StudyAcademicYear.academicYear','semester'])->where('resume_study_academic_year_id',$request->get('study_academic_year_id'))->whereNotNull('resumption_letter')->get(),
           'semesters'=>Semester::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
      ];
      return view('dashboard.academic.resumptions',$data)->withTitle('Postponement Resumptions');
    }

    /**
     * Store postponement into database
     */
    public function store(Request $request)
    {
      if(Postponement::where('student_id',$request->get('student_id'))->where('status','PENDING')->count() != 0){
            return redirect()->back()->with('error','You have pending postponement');
        }
      if(Postponement::where('student_id',$request->get('student_id'))->where('status','POSTPONED')->count() != 0){
            return redirect()->back()->with('error','You have not resumed from your previous postponement');
        }
      if(Postponement::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('status','!=','RESUMED')->count() != 0){
            return redirect()->back()->with('error','You have already requested for postponement for this academic year');
        }
        
        if(Postponement::where('student_id',$request->get('student_id'))->where('status','POSTPONED')->count() == 0){
            if(Registration::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->count() == 0){
                return redirect()->back()->with('error','You cannot postpone because you have not been registered yet for this semester');
            }
        }
    	$validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'student_id'=>'required',
            'category'=>'required',
            'semester_id'=>'required',
            'postponement_letter'=>'required|mimes:pdf',
            'supporting_document'=>'mimes:pdf'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PostponementAction)->store($request);

        return Util::requestResponse($request,'Postponement created successfully');
    }

    /**
     * Update specified postponement
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'student_id'=>'required',
            'category'=>'required',
            'semester_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PostponementAction)->update($request);

        return Util::requestResponse($request,'Postponement updated successfully');
    }

    /**
     * Accept the specified postponement
     */
    public function accept(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if(!$postponement->recommended_by_user_id){
                return redirect()->back()->with('error','Postponement cannot be accepted because it has not been recommended');
            }
            $postponement->status = 'POSTPONED';
            $postponement->postponed_by_user_id = Auth::user()->id;
            $postponement->save();

            $status = StudentshipStatus::where('name','POSTPONED')->first();

            $student = Student::find($postponement->student_id);
            $student->studentship_status_id = $status->id;
            $student->save();

            return redirect()->back()->with('message','Postponement accepted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Accept in bulk
     */
    public function acceptPostponements(Request $request)
    {
         $postponements = Postponement::where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

         $status = StudentshipStatus::where('name','POSTPONED')->first();

         foreach($postponements as $post){
            if($request->get('post_'.$post->id) == $post->id){
                $ps = Postponement::find($post->id);
                if(!$ps->recommended_by_user_id){
                return redirect()->back()->with('error','Special exam cannot be accepted because it has not been recommended');
                }
                $ps->status = $request->get('action') == 'Accept Selected'? 'POSTPONED' : 'DECLINED';
                $ps->postponed_by_user_id = Auth::user()->id;
                $ps->save();
                if($ps->status == 'POSTPONED'){
                  $student = Student::find($post->student_id);
                  $student->studentship_status_id = $status->id;
                  $student->save();
                }
            }
         }

         return redirect()->back()->with('message','Postponements accepted successfully');
    }

    /**
     * Accept in bulk
     */
    public function acceptResumptions(Request $request)
    {
         $postponements = Postponement::where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

         $status = StudentshipStatus::where('name','ACTIVE')->first();

         foreach($postponements as $post){
            if($request->get('post_'.$post->id) == $post->id){
                $ps = Postponement::find($post->id);
                if(!$ps->recommended_by_user_id){
                return redirect()->back()->with('error','Special exam cannot be accepted because it has not been recommended');
                }
                $ps->status = $request->get('action') == 'Accept Selected'? 'RESUMED' : 'POSTPONED';
                $ps->resume_study_academic_year_id = session('active_academic_year_id');
                $ps->resumed_by_user_id = Auth::user()->id;
                $ps->save();
                if($ps->status == 'RESUMED'){
                  $student = Student::find($post->student_id);
                  $student->studentship_status_id = $status->id;
                  $student->save();
                }
            }
         }

         return redirect()->back()->with('message','Resumptions accepted successfully');
    }

    /**
     * Decline the specified postponement
     */
    public function decline(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if(!$postponement->recommended_by_user_id){
                return redirect()->back()->with('error','Postponement cannot be declined because it has not been recommended');
            }
            $postponement->status = 'DECLINED';
            $postponement->save();

            return redirect()->back()->with('message','Postponement declined successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show recommendation for specified postponement
     */
    public function showRecommend(Request $request, $id)
    {
        $data = [
           'postponement'=>Postponement::with('student.campusProgram.program')->find($id)
        ];
        return view('dashboard.academic.recommend-postponement',$data)->withTitle('Recommendation');
    }

    /**
     * Show resumption
     */
    public function showResume(Request $request,$id)
    {
         $postponement = Postponement::find($id);
         $data = [
            'student'=>User::find(Auth::user()->id)->student,
            'postponement'=>$postponement
         ];
         return view('dashboard.student.resumption',$data)->withTitle('Resumption');
    }

    /**
     * Resume postponement
     */
    public function submitResume(Request $request)
    {
        $postponement = Postponement::find($request->get('postponement_id'));
        if($request->hasFile('resumption_letter')){
            $destination = SystemLocation::uploadsDirectory();
            $request->file('resumption_letter')->move($destination, $request->file('resumption_letter')->getClientOriginalName());
            $postponement->resume_study_academic_year_id = session('active_academic_year_id');
            $postponement->resumption_letter = $request->file('resumption_letter')->getClientOriginalName();
        }
        $postponement->save();
        return redirect()->to('student/postponements')->with('message','Resumption letter submitted successfully');
    }

    /**
     * Resume postponement
     */
    public function resumePostponement(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if($postponement->status != 'POSTPONED'){
                return redirect()->back()->with('error','You cannot proceed with resumption because the postponement is not active');
            }
            $postponement->status = 'RESUMED';
            $postponement->resumed_by_user_id = Auth::user()->id;
            $postponement->resume_study_academic_year_id = session('active_academic_year_id');
            $postponement->save();

            $status = StudentshipStatus::where('name','ACTIVE')->first();

            $student = Student::find($postponement->student_id);
            $student->studentship_status_id = $status->id;
            $student->save();

            return redirect()->back()->with('message','Postponement resumed successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Show resume recommendation
     */
    public function showResumeRecommend(Request $request, $id)
    {
         $data = [
           'postponement'=>Postponement::with('student.campusProgram.program')->find($id)
        ];
        return view('dashboard.academic.recommend-resumption',$data)->withTitle('Recommendation');
    }

    /**
     * Resume recommend
     */
    public function resumeRecommend(Request $request)
    {
        $validation = Validator::make($request->all(),[
              'recommendation'=>'required',
              'recommended'=>'required'
            ],[
              'recommended.required'=>'Recommendation option must be selected'
            ]);

            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }
            $postponement = Postponement::find($request->get('postponement_id'));
            $postponement->resumption_recommendation = $request->get('recommendation');
            $postponement->resume_recommended = $request->get('recommended');
            $postponement->resume_recommended_by_user_id = Auth::user()->id;
            $postponement->save();

            return redirect()->to('academic/postponement/resumptions?study_academic_year_id='.session('active_academic_year_id'))->with('message','Resumptions recommended successfully');
    }

    /**
     * Cancel resumption
     */
    public function cancelResumption($id)
    {
        try{
           $post = Postponement::findOrFail($id);
           if(file_exists(public_path().'/uploads/'.$post->resumption_letter)){
               unlink(public_path().'/uploads/'.$post->resumption_letter);
           }
           $post->resumption_letter = null;
           $post->save();
           return redirect()->back()->with('message','Resumption cancelled successfully');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get resource specified in this request');
        }
    }

    /**
     * Download letter
     */
    public function downloadLetter(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if(file_exists(public_path().'/uploads/'.$postponement->letter)){
               return response()->download(public_path().'/uploads/'.$postponement->letter);
            }else{
                return redirect()->back()->with('error','Unable to get the resource specified in this request');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Download letter
     */
    public function downloadResumptionLetter(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if(file_exists(public_path().'/uploads/'.$postponement->resumption_letter)){
               return response()->download(public_path().'/uploads/'.$postponement->resumption_letter);
            }else{
                return redirect()->back()->with('error','Unable to get the resource specified in this request');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
    

    /**
     * Download letter
     */
    public function downloadSupportingDocument(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            if(file_exists(public_path().'/uploads/'.$postponement->supporting_document)){
               return response()->download(public_path().'/uploads/'.$postponement->supporting_document);
            }else{
                return redirect()->back()->with('error','Unable to get the resource specified in this request');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Recommend the specified postponement
     */
    public function recommend(Request $request)
    {
            $validation = Validator::make($request->all(),[
              'recommendation'=>'required',
              'recommended'=>'required'
            ],[
              'recommended.required'=>'Recommendation option must be selected'
            ]);

            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }
            $postponement = Postponement::find($request->get('postponement_id'));
            $postponement->recommendation = $request->get('recommendation');
            $postponement->recommended = $request->get('recommended');
            $postponement->recommended_by_user_id = Auth::user()->id;
            $postponement->save();

            return redirect()->to('academic/postponements?study_academic_year_id='.session('active_academic_year_id'))->with('message','Postponement recommended successfully');
    }

    /**
     * Remove the specified postponement
     */
    public function destroy(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
            $postponement->delete();
            return redirect()->back()->with('message','Postponement deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
