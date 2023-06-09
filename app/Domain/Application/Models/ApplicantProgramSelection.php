<?php

namespace App\Domain\Application\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Academic\Models\CampusProgram;

class ApplicantProgramSelection extends Model
{
    use HasFactory;

    protected $table = 'applicant_program_selections';

    /**
     * Establish one to many relationship with applicants
     */
    public function applicant()
    {
    	return $this->belongsTo(Applicant::class,'applicant_id');
    }

    /**
     * Establish one to many relationship with campus programs
     */
    public function campusProgram()
    {
    	return $this->belongsTo(CampusProgram::class,'campus_program_id');
    }

    /**
     * Check if applicant has selected
     */
    public static function hasSelected($selections,$program,$choice = null)
    {
    	$status = false;
    	if(is_iterable($selections)){
           if($choice){
               foreach($selections as $selection){
               	  if($selection->campus_program_id == $program->id && $selection->order == $choice){
               	  	 $status = true;
               	  	 break;
               	  }
               }
           }else{
               foreach($selections as $selection){
                  if($selection->campus_program_id == $program->id){
                     $status = true;
                     break;
                  }
               }
           }
    	}
    	return $status;
    }

    /**
     * Check if applicant has selected
     */
    public static function hasSelectedChoice($selections,$choice)
    {
        $status = false;
        if(is_iterable($selections)){
           foreach($selections as $selection){
              if($selection->order == $choice){
                 $status = true;
                 break;
              }
           }
        }
        return $status;
    }
}
