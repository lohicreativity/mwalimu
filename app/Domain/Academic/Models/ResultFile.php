<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultFile extends Model
{
    use HasFactory;

    protected $table = 'results_files';

    /**
     * Establish one to many relationship with module assignments
     */
    public function moduleAssignment()
    {
    	return $this->belongsTo(ModuleAssignment::class,'module_assignment_id');
    }
}
