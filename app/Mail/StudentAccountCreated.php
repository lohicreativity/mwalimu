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

    protected $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Student $student, $program_name, $year, $password)
    {
        $this->student = $student;
        $this->program_name = $program_name;
        $this->year = $year;
        $this->password = $password;
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
                        'notification_message'=>'I am pleased to inform you that you have been registered for '.$this->program_name.' in academic year '.$this->year.'. Your registration number is <strong>'.$this->student->registration_number.'</strong>. Please visit '.config('constants.SITE_URL').'/application/login, using your application username and password, to create your student account.',
                    ]);
    }
}
