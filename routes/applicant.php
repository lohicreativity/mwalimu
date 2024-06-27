<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\NextOfKinController;
use App\Http\Controllers\ApplicationWindowController;
use App\Http\Controllers\ApplicationBatchController;
use App\Http\Controllers\NectaResultController;
use App\Http\Controllers\NacteResultController;
use App\Http\Controllers\OutResultController;
use App\Http\Controllers\EntryRequirementController;
use App\Http\Controllers\NHIFController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\ResultsRequests\NECTAServiceController;
use App\Http\Controllers\ResultsRequests\NACTEServiceController;
use App\Http\Controllers\ResultsRequests\OUTServiceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('login',[ApplicantController::class,'showLogin']);
Route::get('logout',[ApplicantController::class,'logout']);
Route::post('authenticate',[ApplicantController::class,'authenticate']);




Route::middleware(['auth:sanctum', 'verified'])->group(function(){

	Route::get('dashboard',[ApplicantController::class,'dashboard']);
	Route::get('basic-information',[ApplicantController::class,'editBasicInfo']);
    Route::get('accept-tamisemi-selection',[ApplicantController::class,'acceptTamisemiSelection']);
    Route::get('reject-tamisemi-selection',[ApplicantController::class,'rejectTamisemiSelection']);
    Route::get('send-karume-applicants',[ApplicantController::class,'sendKarumeApplicants']);
    Route::get('add-applicant-tcu',[ApplicantController::class,'addApplicantTCU']);
	Route::get('next-of-kin',[ApplicantController::class,'editNextOfKin']);
	Route::get('payments',[ApplicantController::class,'payments']);
	Route::get('results',[ApplicantController::class,'requestResults']);
	Route::get('select-programs',[ApplicantController::class,'selectPrograms']);
	Route::get('upload-documents',[ApplicantController::class,'uploadDocuments']);
	Route::get('upload-avn-documents',[ApplicantController::class,'uploadAvnDocuments']);
	Route::get('submission',[ApplicantController::class,'submission']);

    Route::get('select-new-programmes', [ApplicantController::class, 'selectPrograms']);

	Route::post('update-basic-info',[ApplicantController::class,'updateBasicInfo']);
	Route::post('next-of-kin/store',[NextOfKinController::class,'store']);
	Route::post('next-of-kin/update',[NextOfKinController::class,'update']);
	Route::post('upload-documents',[ApplicationController::class,'uploadDocuments']);
    Route::get('view-document',[ApplicationController::class,'viewDocument']);
	Route::get('delete-document',[ApplicationController::class,'deleteDocument']);
	Route::post('store-program-selection',[ApplicantController::class,'storeProgramSelection']);
	Route::get('reset-program-selection/{id}',[ApplicationController::class,'resetProgramSelection']);
	Route::post('request-control-number',[ApplicationController::class,'getControlNumber']);
	Route::post('program/select',[ApplicationController::class,'selectProgram']);
	Route::post('submit-application',[ApplicationController::class,'submitApplication']);
	Route::get('summary',[ApplicationController::class,'downloadSummary']);

    Route::get('fetch-necta-results/{index_number}/{exam_id}',[NECTAServiceController::class,'getResults']);
    Route::get('fetch-nacte-results/{avn}',[NACTEServiceController::class,'getResults']);

    Route::post('get-nacte-results',[ApplicantController::class,'getNacteResults']);
    Route::get('fetch-out-results/{reg_no}',[OUTServiceController::class,'getResults']);
    Route::get('update-nacte-reg-no/{reg_no}',[ApplicantController::class,'updateNacteRegNumber']);


    Route::post('necta-result/decline',[NectaResultController::class,'destroy']);
    Route::post('nacte-result/decline',[NacteResultController::class,'destroy']);
    Route::post('out-result/decline',[OutResultController::class,'destroy']);

    Route::post('necta-result/confirm',[NectaResultController::class,'confirm']);
    Route::post('nacte-result/confirm',[NacteResultController::class,'confirm']);
    Route::post('out-result/confirm',[OutResultController::class,'confirm']);

    Route::get('nullify-necta-results',[NectaResultController::class,'nullify']);
    Route::get('nullify-nacte-results',[NacteResultController::class,'nullify']);
    Route::get('nullify-nacte-reg-results',[NacteResultController::class,'nullifyNacteReg']);
    Route::get('nullify-out-results',[OutResultController::class,'nullify']);

    Route::post('nacte-reg-result/confirm',[NacteResultController::class,'confirmNacteRegNumber']);
    Route::post('nacte-reg-result/decline',[NacteResultController::class,'declineNacteRegNumber']);

    Route::get('upload-attachments',[ApplicationController::class, 'uploadAttachments']);
    Route::post('upload-attachment-file',[ApplicationController::class,'uploadAttachmentFile']);
    Route::get('download-attachment',[ApplicationController::class,'downloadAttachment']);
    Route::get('delete-attachment',[ApplicationController::class,'deleteAttachment']);
    Route::get('admission-package',[ApplicationController::class,'admissionPackage']);
    Route::get('download-admission-letter',[ApplicationController::class,'downloadAdmissionLetter']);

    Route::get('self-send-admission-letter',[ApplicationController::class,'selfSendAdmissionLetter']);
    Route::post('update-applicant-nva',[ApplicantController::class,'updateNVAStatus']);
    Route::get('other-information',[ApplicantController::class,'showOtherInformation']);
    Route::post('update-insurance-status',[ApplicantController::class,'updateInsuranceStatus']);
    Route::post('reset-insurance-status',[ApplicationController::class,'resetInsuranceStatus']);
    Route::post('update-insurance-status-admin',[ApplicationController::class,'updateInsuranceStatus']);
    Route::post('update-insurance',[ApplicantController::class,'updateInsurance']);
    Route::post('update-hostel-status',[ApplicantController::class,'updateHostelStatus']);
    Route::post('update-hostel-status-admin',[ApplicationController::class,'updateHostelStatus']);

    Route::get('hostel-statuses',[ApplicationController::class,'showHostelStatus']);
    Route::get('insurance-statuses',[ApplicationController::class,'showInsuranceStatus']);
    Route::post('preview-insurance-status',[ApplicationController::class,'previewInsuranceStatus']);
    Route::get('download-insurance-status',[ApplicationController::class,'downloadInsuranceStatus']);
    Route::get('download-hostel-status',[ApplicationController::class,'downloadHostelStatus']);
    Route::get('out-results',[OUTServiceController::class,'getResults']);

    Route::get('admission-confirmation',[ApplicationController::class,'showConfirmAdmission']);

    Route::post('confirm-admission',[ApplicationController::class,'confirmAdmission']);
    Route::post('unconfirm-admission',[ApplicationController::class,'unconfirmAdmission']);
    Route::post('cancel-admission',[ApplicationController::class,'cancelAdmission']);
    Route::post('restore-cancelled-admission',[ApplicationController::class,'restoreCancelledAdmission']);
    Route::post('request-confirmation-code',[ApplicationController::class,'requestConfirmationCode']);
    Route::get('cancel-admission',[ApplicationController::class,'cancelAdmission']);

    Route::get('other-applicants',[ApplicationController::class,'otherApplicants']);
    Route::get('other-applicants/reject',[ApplicationController::class,'rejectOtherApplicants']);
    Route::get('view-applicant-documents',[ApplicationController::class,'viewApplicantDocuments']);

    Route::get('postponement',[ApplicantController::class,'showPostponementRequest']);
    Route::post('request-postponement',[ApplicantController::class,'requestPostponement']);
    Route::get('download-postponement-letter',[ApplicantController::class,'downloadPosponementLetter']);

    Route::get('applicant-details', [ApplicantController::class, 'applicantDetails']);
    Route::get('edit-applicant-details',[ApplicantController::class,'editApplicantDetails']);
    Route::post('update-applicant-details',[ApplicantController::class,'updateApplicantDetails']);

    Route::post('update-teacher-certificate-status',[ApplicationController::class,'updateTeacherCertificateStatus']);
    Route::post('update-veta-certificate',[ApplicationController::class,'updateVetaCertificate']);
});

Route::get('get-award-by-id',[AwardController::class,'getById']);
