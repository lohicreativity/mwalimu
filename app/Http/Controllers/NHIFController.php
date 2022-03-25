<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NHIFController extends Controller
{
    /**
     * Register NHIF member
     */
    public function registerMembers(Request $request)
    {
    	$applicants = Applicant::whereHas('selections',function($query){
                $query->where('status','APPROVING');
    	})->where('application_window_id',$request->get('application_window_id'))->get();

    	$data = [];
    	foreach($applicants as $applicant){
             $app_data['FormFourIndexNo'] = $applicant->index_number;
             $app_data['FirstName'] = $applicant->first_name;
             $app_data['MiddleName'] = $applicant->middle_name;
             $app_data['Surname'] = $applicant->surname;
             $app_data['AdmissionNo'] = $applicant->admission_no;
             $app_data['CollegeFaculty'] =
             $app_data['ProgrammeOfStudy'] =
             $app_data['CourseDuration'] = 
             $app_data['MaritalStatus'] = $applicant->marital_status;
             $app_data['DateJoiningEmployer'] =
             $app_data['DateOfBirth'] = $applicant->birth_date;
             $app_data['NationalID'] = $applicant->nin;
             $app_data['Gender'] = $applicant->gender;

             $data[] = $app_data;
    	}
        
        $url = 'http://196.13.105.15/OMRS/api/v1/Verification/StudentRegistration';
    	Http::withHeaders([
             'Content-Type'=>'application/json',
             'Authorization'=>'Bearer '.config('NHIF_TOKEN')
    	])->post($url,$data);
    }

    /**
     * Check status
     */
    public function checkCardStatus(Request $request)
    {
    	$url = 'http://196.13.105.15/omrs/stsidentity';
    	$data = [
            'grant_type'=>'client_credential',
            'client_id'=>'MNMAS',
            'client_secret'=>'MNMAS',
            'scope'=>'OMRS',
            'EmployerNo'=>'8002217'
    	];

    	// $res = Http::withHeaders([
     //          'Content-Type'=>'application/x-www-form-urlencoded'
    	// ])->post($url,$data);

    	  $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return dd(json_decode($result));

    	// return dd($res->getBody());

    	// $url = 'http://196.13.105.15/OMRS/api/v1/Verification/GetStudentsCardStatus?CardNo=101502255519';//.$request->get('card_no');
    	// $response = Http::withHeaders([
     //         'Content-Type'=>'application/json',
     //         'Authorization'=>'Bearer '.config('NHIF_TOKEN')
    	// ])->get($url);

     //    return dd($response);
    }
}
