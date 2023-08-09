/**
     * Run application selection
     */
    public function runSelection(Request $request)
    {
        if($request->get('award_id') >= 5){
            return redirect()->back()->with('error','Selection for this programme level cannot be conducted by the system');
        }

        ini_set('memory_limit', '-1');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;

        // $closed_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('status','INACTIVE')->latest()->first();
        // changed closed window query

/*         $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        ->where('end_date','>=', implode('-', explode('-', now()->format('Y-m-d'))))
        ->where('status','INACTIVE')->latest()->first(); */

        $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        ->where('status','INACTIVE')->latest()->first();
        
        if($closed_window){
            return redirect()->back()->with('error','Application window is inactive');
        }

        $award = Award::find($request->get('award_id'));
 
        if(str_contains(strtolower($award->name),'basic') || str_contains(strtolower($award->name),'diploma')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('end_date','>=',now()->format('Y-m-d'))->first();
        }elseif(str_contains(strtolower($award->name),'bachelor')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('bsc_end_date','>=',now()->format('Y-m-d'))->first();
        }elseif(str_contains(strtolower($award->name),'master')){
            $open_window = ApplicationWindow::where('campus_id',$staff->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('status','ACTIVE')
            ->where('msc_end_date','>=',now()->format('Y-m-d'))->first();
        }
 
        if($open_window){
             return redirect()->back()->with('error','Application window not closed yet');
        }

        if(ApplicationWindow::where('campus_id',$staff->campus_id)->where('end_date','>=',implode('-', explode('-', now()->format('Y-m-d'))))->where('status','INACTIVE')->first()){
             return redirect()->back()->with('error','Application window is not active');
        }

        $batch_id = 0;
        if(!empty($request->get('award_id'))){
            $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id', $request->get('application_window_id'))->where('program_level_id',$request->get('award_id'))->latest()->first();
            $batch_id = $batch->id;
        }

        // Phase I
/*         $campus_programs = CampusProgram::whereHas('applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('award_id'));
        })->with(['program','entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('campus_id',$staff->campus_id)->get();
 */

        $campus_programs = CampusProgram::select('id','program_id')
                                        ->whereHas('applicationWindows',function($query) use($request){$query->where('id',$request->get('application_window_id'));})
                                        ->whereHas('program',function($query) use($request){ $query->where('award_id',$request->get('award_id'));})
                                        ->whereHas('entryRequirements', function($query) use($request){$query->where('application_window_id',$request->get('application_window_id'));})
                                        ->with(['program:id,name','entryrequirements:id,max_capacity,campus_program_id'])->where('campus_id',$staff->campus_id)->get();

        foreach($campus_programs as $program){
            $count_selections = ApplicantProgramSelection::where('campus_program_id', $program->id)->where('batch_id',$batch_id)->where('status', 'APPROVING')->count();
            $count[$program->id] = $count_selections;
        }

        if (Auth::user()->hasRole('admission-officer')) {

/*             $applicants = Applicant::whereHas('selections',function($query) use($request, $staff, $batch_id){
                $query->where('application_window_id',$request->get('application_window_id'))->where('batch_id',$batch_id)->where('campus_id', $staff->campus_id);
            })->with(['selections'=>function($query) use($batch_id){$query->where('batch_id',$batch_id);},'nectaResultDetails.results','nacteResultDetails.results'])
            ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get(); */

            $applicants = Applicant::whereHas('selections',function($query) use($request, $staff, $batch_id){$query->where('id',$request->get('application_window_id'))
                                    ->where('batch_id',$batch_id)->where('campus_id', $staff->campus_id)->where('status','ELIGIBLE');})
                                    ->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])
                                    ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();

        } else {

            $applicants = Applicant::whereHas('selections',function($query) use($request, $batch_id){
                $query->where('application_window_id',$request->get('application_window_id'))->where('batch_id',$batch_id);
            })->with(['selections'=>function($query) use($batch_id){$query->where('batch_id',$batch_id);},'nectaResultDetails.results','nacteResultDetails.results'])
            ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();

        }

        // Phase II
        $choices = array(1,2,3,4);
/*         $applicants = Applicant::with(['selections'=>function($query) use($batch_id){$query->where('batch_id',$batch_id);},'nectaResultDetails.results','nacteResultDetails.results'])
        ->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->get(); */

        $applicants = Applicant::select('id','rank_points','program_level_id','avn_no_results','teacher_certificate_status','teacher_diploma_certificate')
                                ->whereHas('selections',function($query) use($request, $batch_id){$query->where('application_window_id',$request->get('application_window_id'))
                                ->where('batch_id',$batch_id)->where('status','ELIGIBLE');})
                                ->with(['selections:id,order,batch_id,campus_program_id,status,applicant_id','nacteResultDetails:id,applicant_id',
                                'nacteResultDetails.results:id,nacte_result_detail_id'])
                                ->where('program_level_id',$request->get('award_id'))->whereNull('is_tamisemi')->get();

        for($i = 0; $i < count($applicants); $i++){
            for($j = $i + 1; $j < count($applicants); $j++){
               if($applicants[$i]->rank_points < $applicants[$j]->rank_points){
                 $temp = $applicants[$i];
                 $applicants[$i] = $applicants[$j];
                 $applicants[$j] = $temp;
               }
            }
        }
        
        $selected_program = [];
        foreach ($applicants as $applicant) {
          $selected_program[$applicant->id] = false;
        }

        $selection_status = false;
        foreach($choices as $choice){   
            foreach ($campus_programs as $program) {

                if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                }

                if($program->entryRequirements[0]->max_capacity == null){
                     return redirect()->back()->with('error','mMximum capacity for '.$program->program->name.' have not been specified.');
                }

                if(isset($program->entryRequirements[0])){
                foreach($applicants as $applicant){
				  $has_results = true;
                  if($applicant->teacher_certificate_status != 1){
					  if(count($applicant->nacteResultDetails) != 0 && $applicant->program_level_id !=2){
						  if(count($applicant->nacteResultDetails[0]->results) == 0){
							  $has_results = false;
						  }
					  }

					  if($has_results){
						  foreach($applicant->selections as $selection){
							 if($selection->order == $choice && $selection->batch_id == $batch->id && $selection->campus_program_id == $program->id){
								if($count[$program->id] < $program->entryRequirements[0]->max_capacity && $selection->status == 'ELIGIBLE' && !$selected_program[$applicant->id]){
								   if(ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->where('batch_id',$batch->id)->count() == 0 && 
                                     ($applicant->avn_no_results !== 1 || $applicant->teacher_diploma_certificate == null)){
									   $select = ApplicantProgramSelection::find($selection->id);
									   $select->status = 'APPROVING';
									   $select->status_changed_at = now();
									   $select->save();

									   Applicant::where('id',$applicant->id)->update(['status'=>'SELECTED']);
                                       $selection_status = true; 
									   $selected_program[$applicant->id] = true;

									   $count[$program->id]++;
								   }
								}
							 }
						  }
					  }
				  }
                }
              }
           }
        }

        if($selection_status){
            return redirect()->back()->with('message','Selection run successfully');
        }else{
            return redirect()->back()->with('error','Selection has not been successfully. Please retry.'); 
        }

    }