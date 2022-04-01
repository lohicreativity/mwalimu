<?php

namespace App\Http\Controllers\ResultsRequests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\Applicant;

class NECTAServiceController extends Controller
{
    public function getToken($key)
    {
         $response = Http::get('https://api.necta.go.tz/api/public/auth/'.$key);
         return json_decode($response)->token;
    }

    public function getResults(Request $request,$index_number,$exam_id)
    {
        $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
        $exam_year = explode('-',$index_number)[2];

        if($details = NectaResultDetail::with('results')->where('index_number',$index_no)->where('exam_id',$exam_id)->where('applicant_id',$request->get('applicant_id'))->first()){
            return response()->json(['details'=>$details]);
        }else{
            try{
            $token = $this->getToken(config('constants.NECTA_API_KEY'));
            $response = Http::get('https://api.necta.go.tz/api/public/results/'.$index_no.'/'.$exam_id.'/'.$exam_year.'/'.$token);
            }catch(\Exception $e){
                return response()->json(['error'=>'Please refresh your browser and try again']);
            }
            if(!isset(json_decode($response)->results)){
                return redirect()->back()->with('error','Invalid Index number or year');
            }
            if($det = NectaResultDetail::where('index_number',$index_no)->where('exam_id',$exam_id)->where('applicant_id',$request->get('applicant_id'))->first()){
                $detail = $det;
            }else{
                $detail = new NectaResultDetail;
                $detail->center_name = json_decode($response)->particulars->center_name;
                $detail->center_number = json_decode($response)->particulars->center_number;
                $detail->first_name = json_decode($response)->particulars->first_name;
                $detail->middle_name = json_decode($response)->particulars->middle_name;
                $detail->last_name = json_decode($response)->particulars->last_name;
                $detail->sex = json_decode($response)->particulars->sex;
                $detail->index_number = json_decode($response)->particulars->index_number;
                $detail->division = json_decode($response)->results->division->division;
                $detail->points = json_decode($response)->results->division->points;
                $detail->exam_id = $exam_id;
                $detail->applicant_id = $request->get('applicant_id');
                $detail->save();
            }
            foreach(json_decode($response)->results->subjects as $subject){
                if($rs = NectaResult::where('subject_code',$subject->subject_code)->where('necta_result_detail_id',$detail->id)->first()){
                    $res = $rs;
                }else{
                    $res = new NectaResult;
                }
                $res->subject_name = $subject->subject_name;
                $res->subject_code = $subject->subject_code;
                $res->grade = $subject->grade;
                $res->applicant_id = $request->get('applicant_id');
                $res->necta_result_detail_id = $detail->id;
                $res->save();
            }

            $applicant = Applicant::with('programLevel')->find($request->get('applicant_id'));
            if(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'DIRECT' && $exam_id == 2){
                $applicant->results_complete_status = 1;
            }elseif(str_contains($applicant->programLevel->name,'Diploma') && $applicant->entry_mode == 'DIRECT' && $exam_id == 1){
                $applicant->results_complete_status = 1;
            }elseif(str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'DIRECT' && $exam_id == 1){
                $applicant->results_complete_status = 1;
            }
            $applicant->save();

            $details = NectaResultDetail::with('results')->find($detail->id);
            return response()->json(['details'=>$details]);
        }
    }
}
