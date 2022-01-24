<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\HumanResources\Models\Staff;
use App\Models\User;

class ProgramModuleAssignmentRequest extends Model
{
    use HasFactory;

    protected $table = 'program_module_assignment_requests';

    /**
     * Establish one to many relationship with staffs
     */
    public function staff()
    {
    	return $this->belongsTo(Staff::class,'staff_id');
    }

    /**
     * Establish one to many relationship with program module assignment
     */
    public function programModuleAssignment()
    {
        return $this->belongsTo(ProgramModuleAssignment::class,'program_module_assignment_id');
    }

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
        return $this->belongsTo(User::class,'requested_by_user_id');
    }
}
