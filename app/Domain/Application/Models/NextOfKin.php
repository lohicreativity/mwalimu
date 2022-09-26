<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;

class NextOfKin extends Model
{
    use HasFactory;

    protected $table = 'next_of_kins';

    /**
     * Establish one to many relationship with countries
     */
    public function country()
    {
    	return $this->belongsTo(Country::class,'country_id');
    }

    /**
     * Establish one to many relationship with regions
     */
    public function region()
    {
    	return $this->belongsTo(Region::class,'region_id');
    }

    /**
     * Establish one to many relationship with districts
     */
    public function district()
    {
    	return $this->belongsTo(District::class,'district_id');
    }

    /**
     * Establish one to many relationship with wards
     */
    public function ward()
    {
    	return $this->belongsTo(Ward::class,'ward_id');
    }

}
