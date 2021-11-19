<?php

namespace App\Domain\Registration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentshipStatus extends Model
{
    use HasFactory;

    protected $table = 'studentship_statuses';
}
