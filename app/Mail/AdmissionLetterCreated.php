<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\AdmissionAttachment;

class AdmissionLetterCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $applicant;

    protected $study_academic_year;

    protected $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Applicant $applicant, $study_academic_year, $pdf)
    {
        $this->applicant = $applicant;
        $this->study_academic_year = $study_academic_year;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $file_name = base_path('public/uploads').'/Admission-Letter-'.$this->applicant->first_name.'-'.$this->applicant->surname.'.pdf';
        // $attachments = AdmissionAttachment::all();
        // foreach ($attachments as $attachment) {
        //     $this->attach(public_path().'/uploads/'.$attachment->file_name);
        // }    
        return $this->view('emails.admission-letter')
                    ->subject('Admission Letter')
                    ->with([
                       'heading'=>'Admission Letter',
                       'name'=>$this->applicant->first_name.' '.$this->applicant->surname,
                       'notification_message'=>'We are pleased to inform you that you have been admitted to Mwalimu Nyerere Memorial Academy for academic year '.$this->study_academic_year->academicYear->year,
                       'program_name'=>$this->applicant->selections[0]->campusProgram->program->name,
                       'study_year'=>$this->study_academic_year->academicYear->year
                    ]);
    }
}
