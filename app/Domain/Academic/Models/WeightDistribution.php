<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightDistribution extends Model
{
    use HasFactory;

    protected $table = 'module_weight_distributions';

    /**
     * Establish one to many relationship with modules
     */
    public function module()
    {
    	return $this->belongsTo(Module::class,'module_id');
    }
}
