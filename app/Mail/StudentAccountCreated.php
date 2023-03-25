<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Domain\Registration\Models\Student;

class StudentAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $student;

    protected $program_name;

    protected $year;

    protected $transfered_status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Student $student, $program_name, $year, $transfered_status)
    {
        $this->student = $student;
        $this->program_name = $program_name;
        $this->year = $year;
        $this->transfered_status = $transfered_status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.student-credentials')
                    ->subject('Successful Registration')
                    ->with([
                        'heading'=>'Successful Registration',
                        'name'=>$this->student->first_name.' '.$this->student->surname,
                        'notification_message'=> $this->transfered_status? 'I am pleased to inform you that you have been successfully transfered to '.$this->program_name.'. Your new registration number is <strong>'.$this->student->registration_number.'</strong>. Please visit '.config('constants.SITE_URL').'/student/login and use the same password to access your student account.' :

						'I am pleased to inform you that you have been registered for '.$this->program_name.' in academic year '.$this->year.'. Your registration number is <strong>'.$this->student->registration_number.'</strong>. Please visit '.config('constants.SITE_URL').'/application/login, using your application username and password, to create your student account.',
                    ]);
    }
}
