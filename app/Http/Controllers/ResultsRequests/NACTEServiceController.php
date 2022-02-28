<?php

namespace App\Http\Controllers\ResultsRequests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NacteResult;

class NACTEServiceController extends Controller
{
    public function getResults(Request $request,$avn)
    {

        if($details = NacteResultDetail::with('results')->where('avn',$avn)->where('applicant_id',$request->get('applicant_id'))->first()){
            return response()->json(['details'=>$details]);
        }else{
            $response = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/results/'.config('constants.NACTE_API_KEY').'/'.$avn);
            if(!isset(json_decode($response)->params)){
                return redirect()->back()->with('error','Invalid AVN');
            }
            if($det = NacteResultDetail::where('avn',$avn)->where('applicant_id',$request->get('applicant_id'))->first()){
                $detail = $det;
            }else{
                $detail = new NacteResultDetail;
                $detail->institution = json_decode($response)->params[0]->institution;
                $detail->programme = json_decode($response)->params[0]->programme;
                $detail->firstname = json_decode($response)->params[0]->firstname;
                $detail->middlename = json_decode($response)->params[0]->middlename;
                $detail->surname = json_decode($response)->params[0]->surname;
                $detail->gender = json_decode($response)->params[0]->gender;
                $detail->avn = json_decode($response)->params[0]->AVN;
                $detail->registration_number = json_decode($response)->params[0]->registration_number;
                $detail->diploma_gpa = json_decode($response)->params[0]->diploma_gpa;
                $detail->diploma_code = json_decode($response)->params[0]->diploma_code;
                $detail->diploma_category = json_decode($response)->params[0]->diploma_category;
                $detail->diploma_graduation_year = json_decode($response)->params[0]->diploma_graduation_year;
                $detail->username = json_decode($response)->params[0]->username;
                $detail->date_birth = json_decode($response)->params[0]->date_birth;
                $detail->applicant_id = $request->get('applicant_id');
                $detail->save();
            }
            foreach(json_decode($response)->params[0]->diploma_results as $result){
             if($rs = NacteResult::where('subject',$result->subject)->where('nacte_result_detail_id',$detail->id)->first()){
                $res = $rs;
             }else{
                $res = new NacteResult;
             }
             $res->subject = $result->subject;
             $res->grade = $result->grade;
             $res->applicant_id = $request->get('applicant_id');
             $res->nacte_result_detail_id = $detail->id;
             $res->save();
        }

            $details = NacteResultDetail::with('results')->find($detail->id);
            return response()->json(['details'=>$details]);
        }
    }
}
