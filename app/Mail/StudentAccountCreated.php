<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $student;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
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
                        'notification_message'=>'Your student account has been successfully created. Please change your credentials on first login',
                        'username'=>$this->student->registration_number,
                        'password'=>$this->student->surname
                    ]);
    }
}
