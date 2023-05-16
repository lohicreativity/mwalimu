<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;

class FeeItem extends Model
{
    use HasFactory;

    protected $table = 'fee_items';

    /**
     * Establish one to many relationship with fee types
     */
    public function feeType()
    {
    	return $this->belongsTo(FeeType::class,'fee_type_id');
    }

    /**
     * Establish one to many relationship with fee amounts
     */
    public function feeAmounts()
    {
    	return $this->hasMany(FeeAmount::class,'fee_item_id');
    }

    /**
     * Get name attribute
     */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Set name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }

    public function campus()
    {
    	return $this->belongsTo(Campus::class,'campus_id');
    }

}
