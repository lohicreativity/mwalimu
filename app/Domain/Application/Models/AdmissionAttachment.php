<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionAttachment extends Model
{
    use HasFactory;

    protected $table = 'admission_attachments';
}
