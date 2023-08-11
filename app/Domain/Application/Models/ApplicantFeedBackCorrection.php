<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantFeedBackCorrection extends Model
{
    use HasFactory;

    protected $table = 'applicant_nacte_feedback_corrections';
}