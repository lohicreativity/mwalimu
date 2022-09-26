<?php

namespace App\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $table = 'payment_categories';

    /**
     * Estasblish one to many relationship with payment categories
     */
    public function payments()
    {
        return $this->hasMany(Payment::class,'payment_category_id');
    }


}
