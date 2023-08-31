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
        $file_name = base_path('public/uploads').'/Admission-Letter-'.$this->applicant->first_name.'-'.$this->applicant->surname.'.pdf';
        $attachments = AdmissionAttachment::where('campus_id', $this->applicant->campus_id)->get();

        foreach($attachments as $attachment){
            if(in_array($this->applicant->selections[0]->campusProgram->program->award->name, unserialize($attachment->applicable_levels))){
                if(file_exists(public_path().'/uploads/'.$attachment->file_name)){
                    $this->attach(public_path().'/uploads/'.$attachment->file_name);
                 }
            }
        }
   
        return $this->view('emails.admission-letter')
                    ->subject('MNMA Admission Letter')
                    ->with([
                       'heading'=>'Admission Letter',
                       'name'=>$this->applicant->first_name.' '.$this->applicant->surname,
                       'notification_message'=>'We are pleased to inform you that you have been admitted to The Mwalimu Nyerere Memorial Academy for academic year '
                       .$this->study_academic_year->academicYear->year.'. Attached herewith is your admission letter and other relevant documents.',
                       'program_name'=>$this->applicant->selections[0]->campusProgram->program->name,
                       'study_year'=>$this->study_academic_year->academicYear->year
                    ])->attach($file_name);
    }
}
