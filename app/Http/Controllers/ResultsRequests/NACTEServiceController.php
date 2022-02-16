<?php

namespace App\Http\Controllers\ResultsRequests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NACTEServiceController extends Controller
{
    public function getToken($key)
    {
         $response = Http::get('http://41.93.40.137/nacteapi/index.php/api/institutions/'.$key);
         return $response->json();
    }

    public function getResults($index_number,$exam_id,$exam_year)
    {
    	$token = $this->getToken(config('constants.NECTA_KEY'))->token;
    	$response = Http::get('https://api.necta.go.tz:8080/api/public/results/'.$index_number.'/'.$exam_id.'/'.$exam_year);
    	return $response;
    }
    }
}
