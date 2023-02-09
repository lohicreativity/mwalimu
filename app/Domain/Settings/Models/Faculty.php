<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;

class Faculty extends Model
{
    use HasFactory;

    protected $table = 'faculty';

    /**
     * Establish many to many relationship with campuses
     */
    public function campuses()
    {
        return $this->belongsToMany(Campus::class,'campus_faculty','faculty_id','campus_id');
    }

    /**
     * Establish one to many relationship with campuses
     */
    // public function campus()
    // {
    // 	return $this->belongsTo(Campus::class);
    // }


}
