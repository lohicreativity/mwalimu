<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\TranscriptRequest;

class TranscriptRequestController extends Controller
{
    /**
     * Display a list of transcrript requests
     */
    public function index(Request $request)
    {
    	$data = [
           'transcript_requests'=>TranscriptRequest::latest()->paginate(20)
    	];
    	return view('dashboard.academic.transcript-requests',$data)->withTitle('Transcript Requests');
    }
}
