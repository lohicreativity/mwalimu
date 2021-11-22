<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Registration;

class Stream extends Model
{
    use HasFactory;

    protected $table = 'streams';

    /**
     * Establish one to many relationship with programs
     */
    public function program()
    {
    	return $this->belongsTo(Program::class,'program_id');
    }

    /**
     * Establish one to many relationship with groups
     */
    public function groups()
    {
        return $this->hasMany(Group::class,'stream_id');
    }

    /**
     * Establish one to many relationship with students
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class,'stream_id');
    }
}
