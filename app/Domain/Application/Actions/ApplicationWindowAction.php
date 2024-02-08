<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Application\Repositories\Interfaces\ApplicationWindowInterface;
use App\Utils\DateMaker;
use Carbon\Carbon;
use DB;
use App\Domain\Application\Models\ApplicationBatch;

class ApplicationWindowAction implements ApplicationWindowInterface{
	
	public function store(Request $request){
                DB::beginTransaction();
		$window = new ApplicationWindow;
                $window->intake_id = $request->get('intake_id');
                $window->begin_date = DateMaker::toDBDate($request->get('begin_date'));
                $window->end_date = DateMaker::toDBDate($request->get('end_date'));
                if(!empty($request->get('bsc_end_date'))){
                        $window->bsc_end_date =  DateMaker::toDBDate($request->get('bsc_end_date'));
                }else{
                        $window->bsc_end_date =  DateMaker::toDBDate($request->get('end_date'));
                }
                if(!empty($request->get('msc_end_date'))){
                        $window->msc_end_date =  DateMaker::toDBDate($request->get('msc_end_date'));
                }else{
                        $window->msc_end_date =  DateMaker::toDBDate($request->get('end_date'));
                }
                $window->status = $request->get('status');
                $window->campus_id = $request->get('campus_id');
                $window->save();

                if(!AcademicYear::where('year',date('Y',strtotime($request->get('begin_date'))).'/'.date('Y',strtotime($request->get('begin_date')))+1)->first()){

                        $ac_year = new AcademicYear;
                        $ac_year->year = date('Y',strtotime($request->get('begin_date'))).'/'.date('Y',strtotime($request->get('begin_date')))+1;
                        $ac_year->save();

                        if(!StudyAcademicYear::where('academic_year_id',$ac_year->id)->first()){
                                $year = new StudyAcademicYear;
                                $year->academic_year_id = $ac_year->id;
                                $year->begin_date = Carbon::parse($request->get('begin_date'))->format('Y-m-d');
                                $year->end_date = Carbon::parse($request->get('begin_date'))->addMonths(12)->format('Y-m-d');
                                $year->status = 'INACTIVE';
                                $year->save();
                        }
                }
                $batch = new ApplicationBatch;
                $batch->application_window_id = $window->id;
                $batch->program_level_id = 1;
                $batch->no = 1;
                $batch->begin_date = $window->begin_date;
                $batch->end_date = $window->end_date;
                $batch->save();

                $batch_od = new ApplicationBatch;
                $batch_od->application_window_id = $window->id;
                $batch_od->program_level_id = 2;
                $batch_od->no = 1;
                $batch_od->begin_date = $window->begin_date;
                $batch_od->end_date = $window->bsc_end_date;
                $batch_od->save();

                $batch_bsc = new ApplicationBatch;
                $batch_bsc->application_window_id = $window->id;
                $batch_bsc->program_level_id = 4;
                $batch_bsc->no = 1;
                $batch_bsc->begin_date = $window->begin_date;
                $batch_bsc->end_date = $window->bsc_end_date;
                $batch_bsc->save();

                $batch_msc = new ApplicationBatch;
                $batch_msc->application_window_id = $window->id;
                $batch_msc->program_level_id = 5;
                $batch_msc->no = 1;
                $batch_msc->begin_date = $window->begin_date;
                $batch_msc->end_date = $window->msc_end_date;
                $batch_msc->save();

                DB::commit();
	}

	public function update(Request $request){
		$window = ApplicationWindow::find($request->get('application_window_id'));
                $window->intake_id = $request->get('intake_id');
                $window->begin_date = DateMaker::toDBDate($request->get('begin_date'));
                $window->end_date = DateMaker::toDBDate($request->get('end_date'));
                $window->bsc_end_date = DateMaker::toDBDate($request->get('bsc_end_date'));
                $window->msc_end_date = DateMaker::toDBDate($request->get('msc_end_date'));
                $window->status = $request->get('status');
                $window->campus_id = $request->get('campus_id');
                $window->save();
	}
}