<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ExaminationResultLog;
use App\Domain\Academic\Repositories\Interfaces\SpecialExamInterface;
use DB, Auth;

class SpecialExamAction implements SpecialExamInterface{
	
	public function store(Request $request){
              DB::beginTransaction();
              $result_log = new ExaminationResultLog;
              $result_log->module_assignment_id = $request->get('module_assignment_id');
              $result_log->student_id = $request->get('student_id');
              $result_log->final_score = null;
              $result_log->exam_type = 'FINAL';
              $result_log->final_remark = 'POSTPONED';
              $result_log->final_uploaded_at = now();
              $result_log->uploaded_by_user_id = Auth::user()->id;
              $result_log->save();
              
              if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->where('exam_type',$request->get('type'))->first()){
                  $result = $res;
              }else{
                 $result = new ExaminationResult;
              }
              $result->module_assignment_id = $request->get('module_assignment_id');
              $result->student_id = $request->get('student_id');
              $result->final_score = null;
              $result->exam_type = 'FINAL';
              $result->final_remark = 'POSTPONED';
              $result->final_uploaded_at = now();
              $result->uploaded_by_user_id = Auth::user()->id;
              $result->save();


		            $exam = new SpecialExam;
                $exam->student_id = $request->get('student_id');
                $exam->study_academic_year_id = $request->get('study_academic_year_id');
                $exam->module_assignment_id = $request->get('module_assignment_id');
                $exam->semester_id = $request->get('semester_id');
                $exam->type = $request->get('type');
                $exam->status = 'APPROVED';
                $exam->save();
               DB::commit();
	}

	public function update(Request $request){
                DB::beginTransaction();
              $result_log = new ExaminationResultLog;
              $result_log->module_assignment_id = $request->get('module_assignment_id');
              $result_log->student_id = $request->get('student_id');
              $result_log->final_score = null;
              $result_log->exam_type = 'FINAL';
              $result_log->final_remark = 'INCOMPLETE';
              $result_log->final_uploaded_at = now();
              $result_log->uploaded_by_user_id = Auth::user()->id;
              $result_log->save();
              
              if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->where('exam_type',$request->get('type'))->first()){
                  $result = $res;
              }else{
                 $result = new ExaminationResult;
              }
              $result->module_assignment_id = $request->get('module_assignment_id');
              $result->student_id = $request->get('student_id');
              $result->final_score = null;
              $result->exam_type = 'FINAL';
              $result->final_remark = 'INCOMPLETE';
              $result->final_uploaded_at = now();
              $result->uploaded_by_user_id = Auth::user()->id;
              $result->save();

		$exam = SpecialExam::find($request->get('special_exam_id'));
                $exam->student_id = $request->get('student_id');
                $exam->study_academic_year_id = $request->get('study_academic_year_id');
                $exam->module_assignment_id = $request->get('module_assignment_id');
                $exam->semester_id = $request->get('semester_id');
                $exam->type = $request->get('type');
                $exam->save();
              DB::commit();
	}
}