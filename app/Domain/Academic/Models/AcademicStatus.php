<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicStatus extends Model
{
    use HasFactory;

    protected $table = 'academic_statuses';
}
