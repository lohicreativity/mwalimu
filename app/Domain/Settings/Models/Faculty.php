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
     * Establish one to many relationship with campuses
     */
    public function campuses()
    {
    	return $this->hasMany(Campus::class);
    }


}
