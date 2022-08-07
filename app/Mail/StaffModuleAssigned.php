<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Academic\Models\ModuleAssignment;

class StaffModuleAssigned extends Mailable
{
    use Queueable, SerializesModels;

    protected $assignment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ModuleAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->view('emails.custom-notification')
                    ->subject('Module Assignment')
                    ->with([
                       'heading'=>'Module Assignment',
                       'name'=>$this->assignment->staff->first_name.' '.$this->assignment->staff->surname,
                       'notification_message'=>'Please note that you have been assigned '.$this->assignment->module->name.' - '.$this->assignment->module->code.' in '.$this->assignment->programModuleAssignment->semester->name.' of academic year '.$this->assignment->studyAcademicYear->academicYear->year,
                    ]);
    }
}
