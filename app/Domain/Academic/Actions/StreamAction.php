<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Stream;
use App\Domain\Academic\Models\StreamComponent;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Repositories\Interfaces\StreamInterface;

class StreamAction implements StreamInterface{
	
	public function store(Request $request){
              
             $component = StreamComponent::find($request->get('stream_component_id'));
             for($i = 1; $i <= $component->number_of_streams; $i++){
                  
                     $stream = new Stream;
                     $stream->name = $request->get('name_'.$i.'_component_'.$component->id);
                     $stream->year_of_study = $component->year_of_study;
                     $stream->campus_program_id = $component->campus_program_id;
                     $stream->study_academic_year_id = $component->study_academic_year_id;
                     $stream->number_of_students = $request->get('number_'.$i.'_component_'.$component->id);
                     $stream->stream_component_id = $component->id;
                      $stream->save();
                      
                     Registration::where('year_of_study',$component->year_of_study)->where('study_academic_year_id',$component->study_academic_year_id)->where('stream_id',0)->take($request->get('number_'.$i.'_component_'.$component->id))->update(['stream_id'=>$stream->id]);
              }
         
    }
}