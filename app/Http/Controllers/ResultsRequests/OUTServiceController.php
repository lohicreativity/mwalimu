<?php

namespace App\Http\Controllers\ResultsRequests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Domain\Application\Models\OutResultDetail;
use App\Domain\Application\Models\OutResult;
use App\Domain\Application\Models\Applicant;

class OUTServiceController extends Controller
{
    public function getResults(Request $request, $reg_no)
    {

        if($details = OutResultDetail::with('results')->where('reg_no',$reg_no)->where('applicant_id',$request->get('applicant_id'))->first()){
            return response()->json(['details'=>$details,'exists'=>1]);
        }else{

        $index_no='N18-642-3079';
        $index_no='N18-642-1486';
        $index_no='N18-642-0666'; //sample regno
        $index_no='N19-642-0590'; //sample regno
        $index_no='N19-642-1666'; //sample regno
        $index_no='N20-642-2243'; //sample regno
        //$key='SSASAUM';
        //$token='ee59f56dfdc562e77b0385fbc6298f6bfff';
        $key='ISW';
        $token='9f4e71f3fe7c708a1b3322b00ef9aef2';
        
        
        $datars = "<Request>
                   <UsernameToken>
                      <Username>".$key."</Username>
                      <SessionToken>".$token."</SessionToken>
                   </UsernameToken>
                   <RequestParameters>
                       <RegNo>".$reg_no."</RegNo>
                   </RequestParameters>
                   </Request>";
            $data_string = $datars;

            $ch = curl_init('http://196.216.247.11/index.php/results/student');

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = ['Content-Type: application/xml','OUT-Com: default.sp.in'];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            //echo $result;

            $xml            =       simplexml_load_string($result, NULL, LIBXML_NOEMPTYTAG);
            //$xml = $xml->saveXML();
            $mytoken        =       $xml->Response->ResponseParameters;
            $mytoken        =       $xml->ResponseParameters->Results->Subject;
            $obasic                 =       '';
            // print_r($xml->ResponseParameters);
            //echo $xml = $xml->saveXML();

            if($det = OutResultDetail::where('index_number',$index_no)->where('applicant_id',$request->get('applicant_id'))->first()){
                $detail = $det;
            }else{
                $app = Applicant::find($request->get('applicant_id'));
                $applicants = Applicant::where('user_id',$app->user_id)->get();
                foreach ($applicants as $appl) {
                        $detail = new OutResultDetail;
                        $detail->index_number = $xml->ResponseParameters->Indexno;
                        $detail->reg_no = $xml->ResponseParameters->RegNo;
                        $detail->first_name = $xml->ResponseParameters->FirstName;
                        $detail->middle_name = $xml->ResponseParameters->MidName;
                        $detail->surname = $xml->ResponseParameters->Surname;
                        $detail->gender = $xml->ResponseParameters->Gender;
                        $detail->birth_date = $xml->ResponseParameters->BirthDate;
                        $detail->academic_year = $xml->ResponseParameters->AcademicYear;
                        $detail->gpa = $xml->ResponseParameters->GPA;
                        $detail->classification = $xml->ResponseParameters->Classification;
                        $detail->applicant_id = $request->get('applicant_id');
                        $detail->save();
                    
                    foreach($xml->ResponseParameters->Results->Subject as $subject){
                        if($rs = OutResult::where('subject_code',$subject->Code)->where('out_result_detail_id',$detail->id)->first()){
                            $res = $rs;
                        }else{
                            $res = new OutResult;
                        }
                        $res->subject_name = $subject->SubjectName;
                        $res->subject_code = $subject->Code;
                        $res->grade = $subject->Grade;
                        $res->out_result_detail_id = $detail->id;
                        $res->save();
                    }
                }
            }
            $details = OutResultDetail::with('results')->find($detail->id);
            return response()->json(['details'=>$details,'exists'=>0]);
        }
    }

    /**
     * Send XML over POST
     */
    public function sendXmlOverPost($url,$xml_request)
    {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml"));
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return $result;
    }

    public function getResultsAdmin(Request $request,$reg_no)
    {
        try{
            $index_no='N18-642-3079';
            $index_no='N18-642-1486';
            $index_no='N18-642-0666'; //sample regno
            $index_no='N19-642-0590'; //sample regno
            $index_no='N19-642-1666'; //sample regno
            $index_no='N20-642-2243'; //sample regno
            //$key='SSASAUM';
            //$token='ee59f56dfdc562e77b0385fbc6298f6bfff';
            $key='ISW';
            $token='9f4e71f3fe7c708a1b3322b00ef9aef2';
            
            
            $datars = "<Request>
                   <UsernameToken>
                      <Username>".$key."</Username>
                      <SessionToken>".$token."</SessionToken>
                   </UsernameToken>
                   <RequestParameters>
                       <RegNo>".$reg_no."</RegNo>
                   </RequestParameters>
                   </Request>";
            $data_string = $datars;

            $ch = curl_init('http://196.216.247.11/index.php/results/student');

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = ['Content-Type: application/xml','OUT-Com: default.sp.in'];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            //echo $result;

            $xml            =       simplexml_load_string($result, NULL, LIBXML_NOEMPTYTAG);
            //$xml = $xml->saveXML();
            $mytoken        =       $xml->Response->ResponseParameters;
            $mytoken        =       $xml->ResponseParameters->Results->Subject;
            $obasic                 =       '';
        }catch(\Exception $e){
            return response()->json(['error'=>'Please refresh your browser and try again']);
        }
        return response()->json(['response'=>$xml->Response->ResponseParameters]);
    }
}
