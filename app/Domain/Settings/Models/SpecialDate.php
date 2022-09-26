<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\CampusProgram;

class SpecialDate extends Model
{
    use HasFactory;

    protected $table = 'special_dates';
}
