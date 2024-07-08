<?php

namespace App\Http\Livewire\Application;

use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Models\NectaResult;
use App\Models\User;
use Auth;
use Livewire\Component;

class EntryRequirements extends Component
{
    public $selectedEntryRequirement;
    public $application_window_id;

    public function fetchEntryRequirement($Requirement): void
    {
        $this->selectedEntryRequirement = EntryRequirement::where('id',$Requirement->id)->with(['campusProgram.program.award'])->first();
        $this->application_window_id = $Requirement->application_window_id;
    }


    // public function setSelectedDepartment($campusDepartment): void
    // {
    //     $campusDepartment = CampusDepartment::query()
    //         ->where('campus_id', $campusDepartment['campus_id'])
    //         ->where('department_id', $campusDepartment['department_id'])
    //         ->first();

    //     $this->unit_category_id = $campusDepartment->department->unit_category_id;
    //     $this->campus_id = $campusDepartment->campus_id;

    //     $faculty = Faculty::query()
    //     ->where('campus_id',$this->campus_id)
    //     ->first();

    //     $this->parent_id = match ($this->unit_category_id) {
    //         1 => $this->campus_id,
    //         2 => $faculty->id, 
    //         4 => $campusDepartment->department->id,
    //     };

    //     $this->selectedDepartment = $campusDepartment->department;
    //     $this->getParent();
    //     $this->setPrevCampus($campusDepartment);
    // }

    // public function setPrevCampus($campusDepartment): void
    // {
    //     $campusDepartment = CampusDepartment::query()
    //         ->where('campus_id', $campusDepartment['campus_id'])
    //         ->where('department_id', $campusDepartment['department_id'])
    //         ->first();

    //     $this->prev_campus_id = $campusDepartment->campus_id;
    //     $this->prev_unit_category_id = $campusDepartment->unit_category_id;
    //     $this->prev_parent_id = $campusDepartment->parent_id;
    // }
    // public function updatedUnitCategoryId()
    // {
    //     $this->getParent();
    // }

    // public function getParent()
    // {
    //     if (filled($this->selectedDepartment)){
    //         $this->parents = match ((int)$this->unit_category_id) {

    //             1 => Campus::query()->where('id',$this->campus_id)->get(),

    //             2 => Faculty::query()->orderBy('name')
    //                           ->where('campus_id',$this->campus_id)
    //                           ->get(), 
    //             4 => $this->department()
    //                 ->where('unit_category_id', 2)
    //                 ->get(),

    //             default => new Collection(),
    //         };
    //     }
    // }

    public function render()
    {

        $staff = User::find(Auth::user()->id)->staff;
        //$approving_status = ApplicantProgramSelection::where('application_window_id',$request->get('application_window_id'))->where('status','APPROVING')->count();
        $requirements = EntryRequirement::where('application_window_id',$this->application_window_id)->get();
        
        $campusProgramIds = [];
        foreach ($requirements as $req) {
          $campusProgramIds[] = $req->campus_program_id;
        }

        return view('livewire.application.entry-requirements', [
            'entry_requirements'=>EntryRequirement::with(['campusProgram.program.award'])->where('application_window_id',$this->application_window_id)->latest()->get(),
            'campus_programs'=>CampusProgram::with('program')->where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($this->application_window_id),
            'subjects'=>NectaResult::whereHas('detail',function($query){$query->where('exam_id',1);})->distinct()->get(['subject_name']),
            'high_subjects'=>NectaResult::whereHas('detail',function($query){$query->where('exam_id',2);})->distinct()->get(['subject_name']),
            'prog_selection_status'=>ApplicantProgramSelection::where('application_window_id',$this->application_window_id)->count() == 0? false : true,
            ]);

            // $data = [
            //     'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->with(['intake','campus'])->latest()->get(),
            //     'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            //     'campus_programs'=>CampusProgram::with('program')->where('campus_id',$staff->campus_id)->get(),
            //     'cert_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
            //              $query->where('name','LIKE','%4%');
            //     })->whereHas('applicationWindows',function($query) use($request){
            //               $query->where('id',$request->get('application_window_id'));
            //        })->with('program')->where('campus_id',$staff->campus_id)->whereNotIn('id',$campusProgramIds)->get(),
            //     'diploma_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
            //              $query->where('name','LIKE','%6%');
            //     })->whereHas('applicationWindows',function($query) use($request){
            //               $query->where('id',$request->get('application_window_id'));
            //        })->with('program')->where('campus_id',$staff->campus_id)->whereNotIn('id',$campusProgramIds)->get(),
            //     'degree_campus_programs'=>CampusProgram::whereHas('program.ntaLevel',function($query){
            //              $query->where('name','LIKE','%7%')->orWhere('name','LIKE','%8%');
            //     })->whereHas('applicationWindows',function($query) use($request){
            //               $query->where('id',$request->get('application_window_id'));
            //        })->with('program')->where('campus_id',$staff->campus_id)->whereNotIn('id',$campusProgramIds)->get(),
            //     'entry_requirements'=>$request->get('query')? EntryRequirement::whereHas('campusProgram.program',function($query) use($request){
            //              $query->where('name',$request->get('query'));
            //        })->with(['campusProgram.program.award'])->where('application_window_id',$request->get('application_window_id'))->latest()->get() : 
            //        EntryRequirement::with(['campusProgram.program.award'])->where('application_window_id',$request->get('application_window_id'))->latest()->get(),
            //     'subjects'=>NectaResult::whereHas('detail',function($query){
            //         $query->where('exam_id',1);
            //     })->distinct()->get(['subject_name']),
            //     'high_subjects'=>NectaResult::whereHas('detail',function($query){
            //         $query->where('exam_id',2);
            //     })->distinct()->get(['subject_name']),
            //     'equivalent_subjects'=>NacteResult::distinct()->get('subject'),
            //     'staff'=>$staff,
            //     'diploma_programs'=>Program::whereHas('campusPrograms',function($query) use($staff){
            //          $query->where('campus_id',$staff->campus_id);
            //        })->where('name','LIKE','%Diploma%')->get(),
            //     'prog_selection_status'=>ApplicantProgramSelection::where('application_window_id',$request->get('application_window_id'))->count() == 0? false : true,
            //     'request'=>$request,
            //     'certificate_requirements'=>EntryRequirement::where('application_window_id',$request->get('application_window_id'))->where('level','certificate')->count(),
            //     'diploma_requirements'=>EntryRequirement::where('application_window_id',$request->get('application_window_id'))->where('level','diploma')->count(),
            //     'bachelor_requirements'=>EntryRequirement::where('application_window_id',$request->get('application_window_id'))->where('level','certificate')->count()
            //  ];
    }
}
