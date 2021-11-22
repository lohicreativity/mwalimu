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
                  switch ($i) {
                          case 1:
                                  $str = 'A';
                                  break;

                          case 2:
                                  $str = 'B';
                                  break;

                          case 3:
                                  $str = 'C';
                                  break;

                          case 4:
                                  $str = 'D';
                                  break;

                          case 5:
                                  $str = 'E';
                                  break;

                          case 6:
                                  $str = 'F';
                                  break;

                          case 7:
                                  $str = 'G';
                                  break;

                          case 8:
                                  $str = 'H';
                                  break;

                          case 9:
                                  $str = 'I';
                                  break;

                          case 10:
                                  $str = 'J';
                                  break;
                          
                          default:
                                  $str = 'M';
                                  break;
                  }
                     $stream = new Stream;
                     $stream->name = $request->get('name_'.$i);
                     $stream->year_of_study = $request->get('year_of_study');
                     $stream->campus_program_id = $request->get('campus_program_id');
                     $stream->study_academic_year_id = $request->get('study_academic_year_id');
                     $stream->number_of_students = $request->get('number_'.$i);
                     $stream->save();

                     Registration::where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->take($request->get('number_'.$i))->update(['stream_id'=>$stream->id]);
                     
             }
	     
	}
}