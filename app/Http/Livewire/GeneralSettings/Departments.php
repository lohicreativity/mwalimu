<?php

namespace App\Http\Livewire\GeneralSettings;

use App\Domain\Academic\Models\Department;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\CampusDepartment;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Models\UnitCategory;
use App\Models\User;
use Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Departments extends Component
{
    public $departments;
    public $faculties = [];

    public $selectedDepartment;

    public $parent_id;

    public $unit_category_id;

    public $campus_id;

    public $parents;

    public function department()
    {
        return Department::query()->orderBy('unit_category_id')
            ->orderBy('name');
    }


    public function setSelectedDepartment($campusDepartment): void
    {
        $campusDepartment = CampusDepartment::query()
            ->where('campus_id', $campusDepartment['campus_id'])
            ->where('department_id', $campusDepartment['department_id'])
            ->first();

        $this->unit_category_id = $campusDepartment->department->unit_category_id;
        $this->campus_id = $campusDepartment->campus_id;
        $this->parent_id = match ($this->unit_category_id) {
            1 => $campusDepartment->campus_id,
            2, 4 => $campusDepartment->department->parent_id,
        };

        $this->selectedDepartment = $campusDepartment->department;
        $this->getParent();
    }

    public function updatedUnitCategoryId()
    {
        $this->getParent();
    }

    public function getParent()
    {
        if (filled($this->selectedDepartment)){
            $this->parents = match ((int)$this->unit_category_id) {

                1 => Campus::query()->orderBy('name')->get(),

                2 => Faculty::query()->orderBy('name')
                              ->where('campus_id',2)
                              ->get(), 
                4 => $this->department()
                    ->where('unit_category_id', $this->unit_category_id)
                    ->get(),

                default => new Collection(),
            };
        }
    }

    public function render()
    {
        $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {
            $this->departments = Department::whereHas('campuses')->with(['unitCategory','campuses'])->latest()->get();
            $this->faculties = Faculty::all();

        }elseif(Auth::user()->hasRole('admission-officer')) {

            $this->departments = Department::whereHas('campuses', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
                ->with(['unitCategory','campuses'])->latest()->get();
            $this->faculties = Faculty::where('campus_id',$staff->campus_id)->get();
        }


        return view('livewire.general-settings.departments', [
            'unit_categories'  =>UnitCategory::all(),
            'all_departments'  => Department::where('parent_id','>',0)->get(),
            'campuses'         =>Campus::all(),
            'staff'            => $staff,
            'campusDepartments'      => CampusDepartment::query()->with(['campus', 'department.unitCategory', 'department.parent'])->get(),
            ]);
    }
}
