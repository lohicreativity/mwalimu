<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Academic\Models\Graduant;

class GraduationAlert extends Mailable
{
    use Queueable, SerializesModels;

    protected $graduant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Graduant $graduant)
    {
        $this->graduant = $graduant;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.graduation-alert')
                    ->subject('Graduation Confirmation')
                    ->with([
                        'heading'=>'Graduation Confirmation',
                        'name'=>$this->graduant->student->first_name.' '.$this->graduant->student->surname,
                        'notification_message'=>'Congratulations for successful completion of your studies. As we are preparing for graduation, we kindly request you to confirm your attendance. Please log in to the system and confirm your attendance. Wishing you all the best in your career'
                    ]);
    }
}
