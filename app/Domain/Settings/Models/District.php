<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';

    /**
     * Establish one to many relationship with regions
     */
    public function region()
    {
    	return $this->belongsTo(Region::class,'region_id');
    }
}
