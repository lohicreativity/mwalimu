<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $table = 'wards';

    /**
     * Establish one to many relationship with districts
     */
    public function district()
    {
    	return $this->belongsTo(District::class,'district_id');
    }
}
