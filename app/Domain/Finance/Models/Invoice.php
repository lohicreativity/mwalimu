<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\Payment;

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
     * Establish one to many polymorphic relationship with usables
     */
    public function usable()
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

    /**
     * Establish one to many relationship with study academic year
     */
    public function applicable()
    {
        return $this->morphTo();
    }

    /**
     * Establish one to one relationship with gateway payments
     */
    public function gatewayPayment()
    {
        return $this->belongsTo(GatewayPayment::class,'gateway_payment_id');
    }

        /**
     * Establish one to one relationship with gateway payments
     */
    public function payments()
    {
        return $this->hasMany(GatewayPayment::class,'bill_id');
    }
    /**
     * Establish one to many relationship with appeals
     */
    public function appeals()
    {
        return $this->hasMany(Appeal::class,'invoice_id');
    }
}
