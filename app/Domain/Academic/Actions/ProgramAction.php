<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Repositories\Interfaces\ProgramInterface;
use DB;
use PhpOffice\PhpSpreadsheet\Shared\OLERead;

class ProgramAction implements ProgramInterface{
	
	public function store(Request $request){
                if($prog = Program::where('code',$request->get('code'))->orWhere('name',$request->get('name'))->first()){
                    $program = $prog;
                }else{
                        $program = new Program;
                        $program->name = $request->get('name');
                        $program->code = $request->get('code');
                        // $program->department_id = $request->get('department_id');
                        $program->nta_level_id = $request->get('nta_level_id');
                        $program->award_id = $request->get('award_id');
                        $program->description = $request->get('description');
                        $program->min_duration = $request->get('min_duration');
                        $program->max_duration = $request->get('max_duration');
                        // $program->category = $request->get('category');
                        $program->save();
						
						
                }
				
				if($pr = CampusProgram::where('program_id',$program->id)->where('campus_id',$request->get('campus_id'))->first()){
					$prog = $pr;
				}else{
					$prog = new CampusProgram;
				}
				$prog->program_id = $program->id;
				$prog->campus_id = $request->get('campus_id');
                if($request->get('campus_id') == 1){
                    $prog->code = $request->get('code');
                }elseif($request->get('campus_id') == 2){
                    $code = explode('.',$request->get('code'));
                    $prog->code = $code[0].'Z.'.$code[1];
                }elseif($request->get('campus_id') == 3){
                    $code = explode('.',$request->get('code'));
                    $prog->code = $code[0].'P.'.$code[1];
                }
				$prog->regulator_code = $request->get('regulator_code');
				$prog->save();
                
                DB::table('program_department')->where('program_id',$program->id)->where('campus_id',$request->get('campus_id'))->delete();
                $program->departments()->attach([$request->get('department_id')=>['campus_id'=>$request->get('campus_id')]]);
	}

	public function update(Request $request){

		        $program = Program::find($request->get('program_id'));
                $program->name = $request->get('name');
                $program->code = $request->get('code');
                // $program->department_id = $request->get('department_id');
                $program->nta_level_id = $request->get('nta_level_id');
                $program->award_id = $request->get('award_id');
                $program->description = $request->get('description');
                $program->min_duration = $request->get('min_duration');
                $program->max_duration = $request->get('max_duration');
                // $program->category = $request->get('category');
                $program->save();
				
				$prog = CampusProgram::find($request->get('campus_program_id'));
				$prog->program_id = $request->get('program_id');
				//$prog->campus_id = $request->get('campus_id');
                if($request->get('campus_id') == 1){
                    $prog->code = $request->get('code');
                }elseif($request->get('campus_id') == 2){
                    $code = explode('.',$request->get('code'));
                    $prog->code = $code[0].'Z.'.$code[1];
                }elseif($request->get('campus_id') == 3){
                    $code = explode('.',$request->get('code'));
                    $prog->code = $code[0].'P.'.$code[1];
                }
				$prog->regulator_code = $request->get('regulator_code');
                
				$prog->save();		

                DB::table('program_department')->where('program_id',$program->id)->where('department_id',Input::old('department_id'))->where('campus_id',$request->get('campus_id'))->delete();
                $program->departments()->attach([$request->get('department_id')=>['campus_id'=>$request->get('campus_id')]]);
	}
}