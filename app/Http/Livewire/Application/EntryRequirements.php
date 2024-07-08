<?php

namespace App\Http\Livewire\Application;

use App\Domain\Application\Models\EntryRequirement;
use Livewire\Component;

class EntryRequirements extends Component
{
    public $selectedEntryRequirement;

    public function fetchEntryRequirement($id): void
    {
        $this->selectedEntryRequirement = EntryRequirement::where('id',$id)->with(['campusProgram.program.award'])->first();
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

    // public function render()
    // {
    //     $staff = User::find(Auth::user()->id)->staff;

    //     if (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {
    //         $this->departments = Department::whereHas('campuses')->with(['unitCategory','campuses'])->latest()->get();
    //         $this->faculties = Faculty::all();

    //     }elseif(Auth::user()->hasRole('admission-officer')) {

    //         $this->departments = Department::whereHas('campuses', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
    //             ->with(['unitCategory','campuses'])->latest()->get();
    //         $this->faculties = Faculty::where('campus_id',$staff->campus_id)->get();
    //     }


    //     return view('livewire.general-settings.departments', [
    //         'unit_categories'  =>UnitCategory::all(),
    //         'all_departments'  => Department::where('parent_id','>',0)->get(),
    //         'campuses'         =>Campus::all(),
    //         'faculties'        =>Faculty::all(),
    //         'staff'            => $staff,
    //         'campusDepartments'      => CampusDepartment::query()->with(['campus', 'department.unitCategory', 'department.parent'])->get(),
    //         ]);
    // }
}
