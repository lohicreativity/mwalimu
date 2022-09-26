<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\TamisemiStudent;

class TamisemiApplicantCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $student;

    protected $applicant;

    protected $program_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TamisemiStudent $student, Applicant $applicant, $program_name)
    {
        $this->student = $student;
        $this->applicant = $applicant;
        $this->program_name = $program_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.tamisemi-applicant')
                    ->subject('TAMISEMI Selected Students for MNMA')
                    ->with([
                        'heading'=>'TAMISEMI Selected Students for MNMA',
                        'name'=>$this->student->fullname,
                        'notification_message'=>'I am pleased to inform you that TAMISEMI has selected you for '.$this->program_name.' at The Mwalimu Nyerere Memorial Academy.'.' Please visit '.config('constants.SITE_URL').'/application/login to log in to your user account to provide more information. Your username and password are as follows;',
                        'username'=>$this->applicant->index_number,
                        'password'=>$this->applicant->surname
                    ]);
    }
}
