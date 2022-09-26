<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ExaminationResultChange extends Model
{
    use HasFactory;

    protected $table = 'examination_result_changes';

    /**
     * Establish one to many relationship with users
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Establish one to many polymorphic relationship with resultables
     */
    public function resultable()
    {
        return $this->morphTo();
    }
}
