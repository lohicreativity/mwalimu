<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicationBatch;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Application\Repositories\Interfaces\ApplicationWindowInterface;
use App\Utils\DateMaker;
use Carbon\Carbon;
use DB;

class ApplicationBatchAction{
	
	public function store(Request $request){
		// $window = ApplicationWindow::where('campus_id',session('staff_campus_id'))->latest()->first();
        $batch = ApplicationBatch::where('id',$request->batch_id)->first();
        // $batch = new ApplicationBatch;
        // $batch->batch_no = $last_batch > 0 ? $last_batch + 1: 1; 
        // $batch->application_window_id = $window->id;
        // $batch->program_level_id = $request->get('program_level_id');
        // $batch->begin_date = DateMaker::toDBDate($request->get('begin_date'));
        // $batch->end_date = DateMaker::toDBDate($request->get('end_date'));
        // $batch->save();
        if($request->get('program_level_id') == 1 || $request->get('program_level_id') == 2){
            ApplicationWindow::where('id', $batch->application_window_id)->update(['end_date'=>DateMaker::toDBDate($request->get('end_date')), 'begin_date'=>DateMaker::toDBDate($request->get('begin_date'))]);
        }elseif($request->get('program_level_id') == 4){
            ApplicationWindow::where('id', $batch->application_window_id)->update(['bsc_end_date'=>DateMaker::toDBDate($request->get('end_date')), 'begin_date'=>DateMaker::toDBDate($request->get('begin_date'))]);
        }elseif($request->get('program_level_id') == 5){
            ApplicationWindow::where('id', $batch->application_window_id)->update(['msc_end_date'=>DateMaker::toDBDate($request->get('end_date')), 'begin_date'=>DateMaker::toDBDate($request->get('begin_date'))]);
        }
        
	}


}