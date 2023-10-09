<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Application\Models\Applicant;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Settings\Models\SpecialDate;
use NumberToWords\NumberToWords;
use Illuminate\Http\Request;
use App\Mail\AdmissionLetterCreated;
use App\Models\User;
use Mail, PDF, Auth;
use App\Domain\Application\Models\ApplicationWindow;

class SendAdmissionLetterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $application_window_id;
    protected $program_level_id;
    protected $reference_number;

    public $tries = 5;

    private $response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($program_level_id, $application_window_id, $reference_number)
    {
        $this->application_window_id = $application_window_id;
        $this->program_level_id = $program_level_id;
        $this->reference_number = $reference_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $application_window = ApplicationWindow::find($this->application_window_id);

            $applicants = Applicant::select('id','first_name','surname','email','campus_id','address','index_number','application_window_id','intake_id','nationality','region_id','program_level_id')
                                    ->whereHas('selections',function($query) {$query->where('status','SELECTED')->where('application_window_id',$this->application_window_id);})
                                    ->where('program_level_id',$this->program_level_id)
                                    ->where('status','SELECTED')
                                    ->where('campus_id', $application_window->campus_id)
                                    ->where('application_window_id',$this->application_window_id)
                                    ->where(function($query){$query->where('multiple_admissions',0)->orWhere('multiple_admissions',null)->orWhere('confirmation_status','CONFIRMED');})
                                    ->with([
                                        'intake:id,name',
                                        'selections'=>function($query){$query->select('id','status','campus_program_id','applicant_id')->where('status','SELECTED');},
                                        'selections.campusProgram:id,program_id,campus_id',
                                        'selections.campusProgram.program:id,name,award_id,min_duration',
                                        'selections.campusProgram.program.award:id,name',
                                        'campus:id,name',
                                        'applicationWindow:id,end_date',
                                        'region:id,name'
                                    ])->where('id',5)->get(); 

        foreach ($applicants as $applicant) {
            $campus_program_id = $applicant->selections[0]->campusProgram->id;
            $program_name = $applicant->selections[0]->campusProgram->program->name;
            SendAdmissionLetterToSelectedApplicantJob::dispatch(
                $applicant, 
                $this->program_level_id, 
                $campus_program_id,
                $program_name,
                $this->reference_number
            );
        }

        return;
    }

}