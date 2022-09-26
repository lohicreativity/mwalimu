<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleAttendance extends Model
{
    use HasFactory;

    protected $table = 'module_attendances';
}
