<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    /**
     * Establish one to many polymorphic relationship with payables
     */
    public function payable()
    {
    	return $this->morphTo();
    }

    /**
     * Establish one to many polymorphic relationship with usables
     */
    public function usable()
    {
    	return $this->morphTo();
    }

    /**
     * Establish one to many relationship with payment categories
     */
    public function category()
    {
    	return $this->belongsTo(PaymentCategory::class,'payment_category_id');
    }
}
