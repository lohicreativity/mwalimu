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
        if(str_contains(strtoupper($index_number),'EQ')){
            $index_no = explode('-',$index_number)[0];
            $exam_year = explode('-',$index_number)[1];
        }else{

            $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
            $exam_year = explode('-',$index_number)[2];
        } 
        if($details = NectaResultDetail::with('results')->where('index_number',str_replace('-','/',$index_number))
                                       ->where('exam_id',$exam_id)->where('applicant_id',$request->get('applicant_id'))->first()){
            return response()->json(['details'=>$details,'exists'=>1]);
        }else{
            try{
            // $token = $this->getToken(config('constants.NECTA_API_KEY'));
            // $response = Http::get('https://api.necta.go.tz/api/public/results/'.$index_no.'/'.$exam_id.'/'.$exam_year.'/'.$token);
                $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                    'api_key'=>config('constants.NECTA_API_KEY'),
                    'exam_year'=>$exam_year,
                    'index_number'=>$index_no,
                    'exam_id'=>$exam_id
                ]);
                if(json_decode($response)->status->code == 0){
                    return response()->json(['error'=>'Results not found']);
                }
            }catch(\Exception $e){
                return response()->json(['error'=>'Please refresh your browser and try again']);
            }
            
            if($det = NectaResultDetail::where('index_number',$index_no)->where('exam_id',$exam_id)->where('applicant_id',$request->get('applicant_id'))->first()){
                $detail = $det;
                return response()->json(['details'=>$details,'exists'=>0]);
            }else{
                $app = Applicant::find($request->get('applicant_id'));
                $applicants = Applicant::where('user_id',$app->user_id)->get();
                foreach ($applicants as $appl) {
                    $detail = new NectaResultDetail;
                    $detail->center_name = json_decode($response)->particulars->center_name;
                    $detail->center_number = json_decode($response)->particulars->center_number;
                    $detail->first_name = json_decode($response)->particulars->first_name;
                    $detail->middle_name = json_decode($response)->particulars->middle_name;
                    $detail->last_name = json_decode($response)->particulars->last_name;
                    $detail->sex = json_decode($response)->particulars->sex;
                    $detail->index_number = str_replace('-','/',$index_number); //json_decode($response)->particulars->index_number;
                    $detail->division = json_decode($response)->results->division;
                    $detail->points = json_decode($response)->results->points;
                    $detail->exam_id = $exam_id;
                    $detail->applicant_id = $appl->id;
                    $detail->save();
                
                    foreach(json_decode($response)->subjects as $subject){
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
                }
                
                $details = NectaResultDetail::with('results')->find($detail->id);
                return response()->json(['details'=>$details,'exists'=>0]);
            }

            // $applicant = Applicant::with('programLevel')->find($request->get('applicant_id'));
            // if(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'DIRECT' && $exam_id == 2){
            //     $applicant->results_complete_status = 1;
            // }elseif(str_contains($applicant->programLevel->name,'Diploma') && $applicant->entry_mode == 'DIRECT' && $exam_id == 1){
            //     $applicant->results_complete_status = 1;
            // }elseif(str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'DIRECT' && $exam_id == 1){
            //     $applicant->results_complete_status = 1;
            // }
            // $applicant->save();

            return response()->json(['response'=>json_decode($response)]);
        }
    }

    public function getResultsAdmin(Request $request,$index_number,$exam_id)
    {
/*         $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
        $exam_year = explode('-',$index_number)[2]; */

        if(str_contains(strtoupper($index_number),'EQ')){
            $index_no = explode('-',$index_number)[0];
            $exam_year = explode('-',$index_number)[1];
        }else{

            $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
            $exam_year = explode('-',$index_number)[2];
        }
        if($details = NectaResultDetail::with('results')->where('index_number',str_replace('-','/',$index_number))
                                       ->where('exam_id',$exam_id)->where('applicant_id',$request->get('applicant_id'))->first()){
            return response()->json(['details'=>$details,'exists'=>1]);
        }else{                
            try{
                // $token = $this->getToken(config('constants.NECTA_API_KEY'));
                // $response = Http::get('https://api.necta.go.tz/api/public/results/'.$index_no.'/'.$exam_id.'/'.$exam_year.'/'.$token);
                    $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                        'api_key'=>config('constants.NECTA_API_KEY'),
                        'exam_year'=>$exam_year,
                        'index_number'=>$index_no,
                        'exam_id'=>$exam_id
                    ]);
                    if(json_decode($response)->status->code == 0){
                        return response()->json(['error'=>'Results not found']);
                    }
                }catch(\Exception $e){
                    return response()->json(['error'=>'Please refresh your browser and try again']);
                }
            return response()->json(['response'=>json_decode($response)]);
        }
    }
}
