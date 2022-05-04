<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanAllocationCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.custom-notification')
                    ->subject('Call for Loan Signature')
                    ->with([
                        'heading'=>'Call for Loan Signature',
                        'name'=>$this->user->username,
                        'notification_message'=>'I am pleased to inform you that your loan payment has been received. Please, visit loans office for signing'
                    ]);
    }
}
