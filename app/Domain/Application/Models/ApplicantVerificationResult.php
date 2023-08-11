<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ApplicantVerificationResult extends Model
{
    use HasFactory;

    protected $table = 'applicant_verification_results';
}

