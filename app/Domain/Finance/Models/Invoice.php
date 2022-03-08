<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    /**
     * Establish one to many polymorphic relationship with payables
     */
    public function payable()
    {
    	return $this->morphTo();
    }

    /**
     * Establish one to many relationship with payment categories
     */
    public function feeType()
    {
    	return $this->belongsTo(FeeType::class,'fee_type_id');
    }
}
