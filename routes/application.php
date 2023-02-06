<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\NextOfKinController;
use App\Http\Controllers\ApplicationWindowController;
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
Route::get('/', [ApplicationController::class,'index']);
Route::get('registration', [ApplicationController::class,'index']);
Route::post('registration/store',[ApplicationController::class,'store']);
Route::get('login',[ApplicantController::class,'showLogin']);
Route::get('logout',[ApplicantController::class,'logout']);
Route::post('authenticate',[ApplicantController::class,'authenticate']);




Route::middleware(['auth:sanctum', 'verified'])->group(function(){

	Route::get('dashboard',[ApplicantController::class,'dashboard']);
	Route::get('basic-information',[ApplicantController::class,'editBasicInfo']);
	Route::get('next-of-kin',[ApplicantController::class,'editNextOfKin']);
	Route::get('payments',[ApplicantController::class,'payments']);
	Route::get('results',[ApplicantController::class,'requestResults']);
	Route::get('select-programs',[ApplicantController::class,'selectPrograms']);
	Route::get('upload-documents',[ApplicantController::class,'uploadDocuments']);
	Route::get('upload-avn-documents',[ApplicantController::class,'uploadAvnDocuments']);
	Route::get('submission',[ApplicantController::class,'submission']);

	Route::post('update-basic-info',[ApplicantController::class,'updateBasicInfo']);
	Route::post('next-of-kin/store',[NextOfKinController::class,'store']);
	Route::post('next-of-kin/update',[NextOfKinController::class,'update']);
	Route::post('upload-documents',[ApplicationController::class,'uploadDocuments']);
	Route::get('delete-document',[ApplicationController::class,'deleteDocument']);
	Route::post('store-program-selection',[ApplicantController::class,'storeProgramSelection']);
	Route::get('reset-program-selection/{id}',[ApplicationController::class,'resetProgramSelection']);
	Route::post('request-control-number',[ApplicationController::class,'getControlNumber']);
	Route::post('program/select',[ApplicationController::class,'selectProgram']);
	Route::post('submit-application',[ApplicationController::class,'submitApplication']);
	Route::get('summary',[ApplicationController::class,'downloadSummary']);


	Route::get('application-windows', [ApplicationWindowController::class,'index']);
	Route::post('application-window/store', [ApplicationWindowController::class,'store']);
	Route::post('application-window/update', [ApplicationWindowController::class,'update']);
	Route::get('application-window/{id}/destroy', [ApplicationWindowController::class,'destroy']);


	Route::get('entry-requirements', [EntryRequirementController::class,'index']);
	Route::get('entry-requirements-capacity', [EntryRequirementController::class,'showCapacity']);
	Route::post('entry-requirements-capacity/update', [EntryRequirementController::class,'updateCapacity']);
	Route::post('entry-requirement/store', [EntryRequirementController::class,'store']);
	Route::post('entry-requirement/update', [EntryRequirementController::class,'update']);
	Route::get('entry-requirement/{id}/destroy', [EntryRequirementController::class,'destroy']);

	Route::get('application-dashboard',[ApplicationController::class, 'showDashboard']);
	Route::get('download-applicants-list',[ApplicantController::class,'downloadApplicantsList']);

	Route::get('search-for-applicant',[ApplicationController::class,'searchForApplicant']);
	Route::post('reset-applicant-password',[ApplicationController::class,'resetApplicantPassword']);
	Route::get('reset-applicant-password-default',[ApplicationController::class,'resetApplicantPasswordDefault']);


	Route::get('applicants/list',[ApplicationController::class,'showApplicantsList']);
    Route::get('fetch-necta-results/{index_number}/{exam_id}',[NECTAServiceController::class,'getResults']);
    Route::get('fetch-nacte-results/{avn}',[NACTEServiceController::class,'getResults']);
    Route::get('fetch-necta-results-admin/{index_number}/{exam_id}',[NECTAServiceController::class,'getResultsAdmin']);
    Route::get('fetch-nacte-results-admin/{avn}',[NACTEServiceController::class,'getResultsAdmin']);
    Route::get('admin-fetch-results',[ApplicationController::class,'getNectaResults']);
    Route::post('get-necta-results',[ApplicantController::class,'getNectaResults']);
    Route::post('get-nacte-results',[ApplicantController::class,'getNacteResults']);
    Route::get('fetch-out-results/{reg_no}',[OUTServiceController::class,'getResults']);
    Route::get('fetch-out-results-admin/{reg_no}',[OUTServiceController::class,'getResultsAdmin']);
    Route::get('update-nacte-reg-no/{nacte_reg_no}',[ApplicantController::class,'updateNacteRegNumber']);


    Route::post('necta-result/decline',[NectaResultController::class,'destroy']);
    Route::post('nacte-result/decline',[NacteResultController::class,'destroy']);
    Route::post('out-result/decline',[OutResultController::class,'destroy']);

    Route::post('necta-result/confirm',[NectaResultController::class,'confirm']);
    Route::post('nacte-result/confirm',[NacteResultController::class,'confirm']);
    Route::post('out-result/confirm',[OutResultController::class,'confirm']);

    Route::get('nullify-necta-results',[NectaResultController::class,'nullify']);
    Route::get('nullify-nacte-results',[NacteResultController::class,'nullify']);
    Route::get('nullify-out-results',[OutResultController::class,'nullify']);


    Route::get('application-window-campus-programs', [ApplicationWindowController::class,'showPrograms']);
	Route::post('application-window-campus-programs/update', [ApplicationWindowController::class,'updatePrograms']);

	Route::get('selected-applicants',[ApplicationController::class,'selectedApplicants']);
	Route::get('selected-applicants/download',[ApplicationController::class,'downloadSelectedApplicants']);
	Route::post('submit-selected-applicants-tcu',[ApplicationController::class,'submitSelectedApplicants']);

	Route::get('window/{id}/activate',[ApplicationWindowController::class,'activate']);
	Route::get('window/{id}/deactivate',[ApplicationWindowController::class,'deactivate']);


	Route::get('run-selection',[ApplicationController::class,'showRunSelection']);
	Route::post('run-applicants-selection',[ApplicationController::class,'runSelection']);
	Route::get('run-selection-program',[ApplicationController::class,'showRunSelectionByProgram']);
	Route::post('run-applicants-selection-program',[ApplicationController::class,'runSelectionByProgram']);

    Route::get('nhif/status',[NHIFController::class,'requestToken']);

    Route::get('admit-applicant/{applicant_id}/{selection_id}',[ApplicationController::class,'admitApplicant']);
    Route::post('register-applicant',[ApplicationController::class,'registerApplicant']);
    Route::get('applicants-registration',[ApplicationController::class,'applicantsRegistration']);
    Route::get('applicants-admission',[ApplicationController::class,'applicantsAdmission']);
    Route::get('upload-attachments',[ApplicationController::class, 'uploadAttachments']);
    Route::post('upload-attachment-file',[ApplicationController::class,'uploadAttachmentFile']);
    Route::get('download-attachment',[ApplicationController::class,'downloadAttachment']);
    Route::get('delete-attachment',[ApplicationController::class,'deleteAttachment']);
    Route::get('admission-package',[ApplicationController::class,'admissionPackage']);
    Route::get('download-admission-letter',[ApplicationController::class,'downloadAdmissionLetter']);

    Route::post('send-admission-letter',[ApplicationController::class,'sendAdmissionLetter']);
    Route::post('update-applicant-nva',[ApplicantController::class,'updateNVAStatus']);
    Route::get('other-information',[ApplicantController::class,'showOtherInformation']);
    Route::post('update-insurance-status',[ApplicantController::class,'updateInsuranceStatus']);
    Route::post('update-insurance-status-admin',[ApplicationController::class,'updateInsuranceStatus']);
    Route::post('update-insurance',[ApplicantController::class,'updateInsurance']);
    Route::post('update-hostel-status',[ApplicantController::class,'updateHostelStatus']);
    Route::post('update-hostel-status-admin',[ApplicationController::class,'updateHostelStatus']);

    Route::get('hostel-statuses',[ApplicationController::class,'showHostelStatus']);
    Route::get('insurance-statuses',[ApplicationController::class,'showInsuranceStatus']);
    Route::get('download-insurance-status',[ApplicationController::class,'downloadInsuranceStatus']);
    Route::get('download-hostel-status',[ApplicationController::class,'downloadHostelStatus']);
    Route::get('out-results',[OUTServiceController::class,'getResults']);

    Route::get('submit-applicants',[ApplicationController::class,'submitApplicants']);

    Route::post('retrieve-applicants-tcu',[ApplicationController::class,'getApplicantsFromTCU']);

    Route::post('retrieve-confirmed-applicants-tcu',[ApplicationController::class,'getConfirmedFromTCU']);

    Route::get('admission-confirmation',[ApplicationController::class,'showConfirmAdmission']);

    Route::post('confirm-admission',[ApplicationController::class,'confirmAdmission']);
    Route::post('unconfirm-admission',[ApplicationController::class,'unconfirmAdmission']);
    Route::post('cancel-admission',[ApplicationController::class,'cancelAdmission']);
    Route::post('restore-cancelled-admission',[ApplicationController::class,'restoreCancelledAdmission']);
    Route::post('request-confirmation-code',[ApplicationController::class,'requestConfirmationCode']);

    
    Route::get('admitted-applicants',[ApplicationController::class,'admittedApplicants']);

    Route::get('get-nacte-applicants',[ApplicationController::class,'getVerifiedApplicantsNACTE']);

    Route::get('tamisemi-applicants',[ApplicationController::class,'tamisemiApplicants']);
    Route::post('get-tamisemi-applicants',[ApplicationController::class,'downloadTamisemiApplicants']);

    Route::get('store-requirements-as-previous',[EntryRequirementController::class,'storeAsPrevious']);
 
    Route::get('postponement',[ApplicantController::class,'showPostponementRequest']);
    Route::post('request-postponement',[ApplicantController::class,'requestPostponement']);
    Route::get('download-postponement-letter',[ApplicantController::class,'downloadPosponementLetter']);

    Route::post('delete-applicant-invoice',[ApplicantController::class,'deleteInvoice']);

    Route::post('upload-camera-img',[ApplicantController::class,'uploadCameraImage']);
    Route::post('upload-signature',[ApplicantController::class, 'uploadSignature']);

    Route::get('edit-applicant-details',[ApplicantController::class,'editApplicantDetails']);
    Route::post('update-applicant-details',[ApplicantController::class,'updateApplicantDetails']);

    Route::post('update-teacher-certificate-status',[ApplicationController::class,'updateTeacherCertificateStatus']);
    Route::post('update-veta-certificate',[ApplicationController::class,'updateVetaCertificate']);


    Route::get('failed-insurance-registrations',[ApplicationController::class,'showFailedInsuranceRegistrations']);
    Route::post('resubmit-insurance-registrations',[ApplicationController::class,'resubmitInsuranceRegistrations']);
	
	Route::get('internal-transfers',[ApplicationController::class,'showInternalTransfersAdmin']);
	Route::get('external-transfers',[ApplicationController::class,'showExternalTransfersAdmin']);
	Route::get('external-transfer/{id}/edit',[ApplicationController::class,'editExternalTransfer']);
	Route::post('internal-transfers-submission',[ApplicationController::class,'internalTransfersSubmission']);
	Route::post('register-external-transfer',[ApplicationController::class,'registerExternalTransfer']);
	Route::post('update-external-transfer',[ApplicationController::class,'updateExternalTransfer']);
	
	Route::get('check-receipt',[ApplicantController::class,'checkReceipt']);
	Route::get('reset-selections',[ApplicationController::class,'resetSelections']);
	
});

Route::get('get-award-by-id',[AwardController::class,'getById']);
