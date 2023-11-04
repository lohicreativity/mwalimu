<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NHIFService
{
    /**
     * Register NHIF member
     */
    // public function registerMembers(Request $request)
    // {
    // 	$applicants = Applicant::whereHas('selections',function($query){
    //             $query->where('status','APPROVING');
    // 	})->where('application_window_id',$request->get('application_window_id'))->get();

    // 	$data = [];
    // 	foreach($applicants as $applicant){
    //          $app_data['FormFourIndexNo'] = $applicant->index_number;
    //          $app_data['FirstName'] = $applicant->first_name;
    //          $app_data['MiddleName'] = $applicant->middle_name;
    //          $app_data['Surname'] = $applicant->surname;
    //          $app_data['AdmissionNo'] = $applicant->admission_no;
    //          $app_data['CollegeFaculty'] =
    //          $app_data['ProgrammeOfStudy'] =
    //          $app_data['CourseDuration'] =
    //          $app_data['MaritalStatus'] = $applicant->marital_status;
    //          $app_data['DateJoiningEmployer'] =
    //          $app_data['DateOfBirth'] = $applicant->birth_date;
    //          $app_data['NationalID'] = $applicant->nin;
    //          $app_data['Gender'] = $applicant->gender;

    //          $data[] = $app_data;
    // 	}

    //     $url = 'http://196.13.105.15/OMRS/api/v1/Verification/StudentRegistration';
    // 	Http::withHeaders([
    //          'Content-Type'=>'application/json',
    //          'Authorization'=>'Bearer '.config('NHIF_TOKEN')
    // 	])->post($url,$data);
    // }

    /**
     * Submit card applications
     */
    public static function submitCardApplications($ac_year, $applicants)
    {
        $data = [];
        foreach ($applicants as $applicant) {
            $data[] = [
               'CollerationID'=>$applicant->index_number,
               'MobileNo'=>$applicant->phone,
               'AcademicYear'=>$ac_year,
               'YearOfStudy'=>1,
               'Category'=>1
            ];
        }

        $payload = [
            'BatchNo'=>'8002217/'.$ac_year.'/001',
            'Description'=>'Card applications on '.date('M, Y'),
            'CardApplications'=>$data
        ];

          $url = 'http://verification.nhif.or.tz/omrs/api/v1/Verification/SubmitCardApplications';
          $token = self::requestToken();

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json",
            $token));
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return json_decode($result);

    }

    /**
     * Check status
     */
    public static function checkCardStatus($card_no)
    {
          $url = 'https://verification.nhif.or.tz/omrs/api/v1/Verification/GetStudentsCardStatus?CardNo='.$card_no;
          $token = self::requestToken();

    	  $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json",
            $token));
          // curl_setopt($ch, CURLOPT_POST, 1);
          // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return json_decode($result);
    }

    /**
     * Get NHIF token
     */
     public static function requestToken()
     {
        $url = 'https://verification.nhif.or.tz/omrs/auth';

        $curl_handle = curl_init();


        $client = 'MNMAS';

        curl_setopt_array($curl_handle, array(
        CURLOPT_URL => $url,
        CURLOPT_SSLVERSION => 6,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 500,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "client_id=".$client."&client_secret=".$client."&grant_type=client_credentials&scope=OMRS",
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded",
          "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl_handle);
        $response = json_decode($response);
        $StatusCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        $err = curl_error($curl_handle);

        curl_close($curl_handle);


        if ($err) {
           return (object) array('error' => $err);
        } else {
           return 'Authorization: '.$response->{'token_type'}.' '.$response->{'access_token'};
        }
    }

}
