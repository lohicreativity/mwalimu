<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayPayment extends Model
{
    use HasFactory;

    protected $table = 'gateway_payments';

        /**
     * Establish one to one relationship with gateway payments
     */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'bill_id', 'reference_no');
    }

}
