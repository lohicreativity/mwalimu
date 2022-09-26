<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Registration\Models\Registration;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    /**
     * Establish one to many relationship with streams
     */
    public function stream()
    {
    	return $this->belongsTo(Stream::class,'stream_id');
    }
}
