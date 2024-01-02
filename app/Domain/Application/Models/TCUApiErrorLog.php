<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TCUApiErrorLog extends Model
{
    use HasFactory;

    protected $table = 'tcu_api_error_log';

    /**
     * Check if applicant exists
     */
    public static function containsApplicant($logs,$student_id)
    {  
    	$status = false;
    	foreach ($logs as $log) {
    		if($log->student_id == $student_id){
                $status = true;
    		}
    	}
    	return $status;
    }
}
