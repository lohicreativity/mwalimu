<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabilityStatus extends Model
{
    use HasFactory;

    protected $table = 'disability_statuses';

    /**
     * Set name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }

    /**
     * Get name attribute
     */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }
}
