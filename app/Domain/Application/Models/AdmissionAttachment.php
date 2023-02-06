<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Settings\Models\Campus;
use Illuminate\Database\Eloquent\Model;

class AdmissionAttachment extends Model
{
    use HasFactory;

    protected $table = 'admission_attachments';

    public function campus()
    {
        return $this->belongsTo(Campus::class,'campus_id');
    }
}
