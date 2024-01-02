<?php

namespace App\Http\Controllers\Api\NHIF\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controler;

class AuthController extends Controller
{
    public function getToken(Request $request)
    {
        $response = Http::post('http://196.13.105.15/omrs/stsidentity', [
            'grant_type' => $request->input('grant_type'),
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret'),
            'scope' => $request->input('scope'),
        ]);

        //store
        return $response->json();
    }
}
