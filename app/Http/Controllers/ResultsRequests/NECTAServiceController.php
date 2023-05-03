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
/*         $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
        $exam_year = explode('-',$index_number)[2]; */

        if(str_contains(strtoupper($index_number),'EQ')){
            $index_no = explode('-',$index_number)[0];
            $exam_year = explode('-',$index_number)[1];
        }else{

            $index_no = explode('-',$index_number)[0].'-'.explode('-',$index_number)[1];
            $exam_year = explode('-',$index_number)[2];
        }
                
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
