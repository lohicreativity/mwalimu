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
use Validator, Auth;


class PostponementController extends Controller
{
     /**
     * Display a list of postponements
     */
    public function index(Request $request)
    {
    	$data = [
    	     'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'student'=>$request->has('registration_number')? Student::where('registration_number',$request->get('registration_number'))->first() : null,
           'postponements'=>Postponement::with(['student','StudyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get(),
           'semesters'=>Semester::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.postponements',$data)->withTitle('Postponements');
    }

    /**
     * Store postponement into database
     */
    public function store(Request $request)
    {
      if(Postponement::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','!=','RESUMED')->count() != 0){
            return redirect()->back()->with('error','You have already requested for postponement for this academic year');
        }

        if(Registration::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->count() == 0){
            return redirect()->back()->with('error','You cannot postpone because you have not been registered yet for this semester');
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
            $postponement->status = 'POSTPONED';
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
                $ps->status = $request->get('accept')? 'POSTPONED' : 'DECLINED';
                $ps->save();
                if($request->get('accept')){
                  $student = Student::find($post->student_id);
                  $student->studentship_status_id = $status->id;
                  $student->save();
                }
            }
         }

         return redirect()->back()->with('message','Postponements accepted successfully');
    }

    /**
     * Decline the specified postponement
     */
    public function decline(Request $request, $id)
    {
        try{
            $postponement = Postponement::findOrFail($id);
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

            return redirect()->to('academic/postponements')->with('message','Postponement recommended successfully');
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
