<?php

namespace App\Http\Controllers\ResultsRequests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OUTServiceController extends Controller
{
    public function getResults(Request $request)
    {
         $url = 'http://196.216.247.11/index.php/results/student';
         $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                            <Request>
                            <UsernameToken>
                            <Username>'.config('constants.OUT_USERNAME').'</Username>
                            <SessionToken>'.config('constants.OUT_TOKEN').'</SessionToken>
                            </UsernameToken>
                            <RequestParameters>
                            <RegNo> N18-642-0001</ RegNo >
                            </RequestParameters>
                            </Request>';
        // $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
        // $json = json_encode($xml_response);
        // $array = json_decode($json,TRUE);

        $result = $this->sendXmlOverPost($url,$xml_request);

        return dd($result);

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
}
