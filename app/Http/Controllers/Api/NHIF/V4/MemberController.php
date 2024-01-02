<?php

namespace App\Http\Controllers\Api\NHIF\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controler;

class MemberController extends Controller
{
    public function register(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'FormFourIndexNo' => 'required|string',
            'FirstName' => 'required|string',
            'MiddleName' => 'nullable|string',
            'Surname' => 'required|string',
            'AdmissionNo' => 'required|string',
            'CollageFaculty' => 'required|string',
            'MobileNo' => 'required|string',
            'ProgrammeOfStudy' => 'required|string',
            'CourseDuration' => 'required|integer',
            'MaritalStatus' => 'required|string',
            'DateJoiningEmployer' => 'required|date',
            'DateOfBirth' => 'required|date',
            'NationalID' => 'required|string',
            'Gender' => 'required|string',
        ]);

        // Send a request to the external API for member registration
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '  /*. $request->bearerToken() */,
        ])->post('http://196.13.105.15/OMRS/api/v1/Verification/StudentRegistration', $request->all());

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json(['statusCode' => 200, 'message' => 'Application Received Successfully'], 200);
        } else {
            return response()->json(['statusCode' => $response->status(), 'message' => 'Error in member registration'], $response->status());
        }
    }

    public function submitApplications(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'BatchNo' => 'required|string',
            'Description' => 'required|string',
            'CorrelationID' => 'required|string',
            'CardNo' => 'required|string',
            'MobileNo' => 'required|string',
            'IntakeCode' => 'required|string',
            'NationalID' => 'required|string',
            'AcademicYear' => 'required|string',
            'YearOfStudy' => 'required|integer',
            'Category' => 'required|integer',
        ]);

        // Send a request to the external API for card application submission
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->post('http://196.13.105.15/OMRS/api/v1/Verification/SubmitCardApplications', $request->all());

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json(['statusCode' => 200, 'message' => 'Application Received Successfully'], 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in card application submission'
            ], $response->status());
        }
    }

    public function getCardStatus(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'CardNo' => 'required|string',
        ]);

        // Send a request to the external API for retrieving card status
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->get('http://196.13.105.15/OMRS/api/v1/Verification/GetStudentsCardStatus', [
            'CardNo' => $request->input('CardNo'),
        ]);

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in retrieving card status'
            ], $response->status());
        }
    }

    public function getRegistrationList(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'Year' => 'nullable|integer',
            'Gender' => 'nullable|string',
            'admissionNo' => 'nullable|string',
            'formFourIndexNo' => 'nullable|string',
        ]);

        // Send a request to the external API for retrieving the registration list
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->get('http://196.13.105.15/OMRS/api/v1/Verification/GetRegistrationList', [
            'Year' => $request->input('Year'),
            'Gender' => $request->input('Gender'),
            'admissionNo' => $request->input('admissionNo'),
            'formFourIndexNo' => $request->input('formFourIndexNo'),
        ]);

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in retrieving the registration list'
            ], $response->status());
        }
    }

    public function getAcademicIntake(Request $request)
    {
        // Send a request to the external API for retrieving academic intake information
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->get('http://196.13.105.15/omrs/api/v1/verification/GetAcademicIntake');

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in retrieving academic intake information'
            ], $response->status());
        }
    }

    public function verifyStudentRegistration(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'FirstName' => 'required|string',
            'MiddleName' => 'nullable|string',
            'LastName' => 'required|string',
            'Gender' => 'required|string',
            'DateOfBirth' => 'required|date',
            'FormFourIndexNo' => 'required|string',
        ]);

        // Send a request to the external API for verifying student registration
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->post('http://196.13.105.15/omrs/api/v1/verification/VerifyStudentRegistration', $request->all());

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in verifying student registration'
            ], $response->status());
        }
    }

    public function getStudentControlNoStatus(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'BatchNo' => 'required|string',
        ]);

        // Send a request to the external API for retrieving student control number status
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken(),
        ])->get('http://196.13.105.15/omrs/api/v1/verification/GetStudentControlNoStatus', [
            'BatchNo' => $request->input('BatchNo'),
        ]);

        // Check the response from the external API
        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'statusCode' => $response->status(),
                'reasonPhrase' => $response->reasonPhrase(),
                'message' => $response->json()['message'] ?? 'Error in retrieving student control number status'
            ], $response->status());
        }
    }
}
