<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Repositories\Interfaces\ApplicationWindowInterface;
use App\Utils\DateMaker;

class ApplicationWindowAction implements ApplicationWindowInterface{
	
	public function store(Request $request){
		$window = new ApplicationWindow;
                $window->intake_id = $request->get('intake_id');
                $window->capacity = $request->get('capacity');
                $window->begin_date = DateMaker::toDBDate($request->get('begin_date'));
                $window->end_date = DateMaker::toDBDate($request->get('end_date'));
                $window->save();
	}

	public function update(Request $request){
		$window = ApplicationWindow::find($request->get('application_window_id'));
                $window->intake_id = $request->get('intake_id');
                $window->capacity = $request->get('capacity');
                $window->begin_date = DateMaker::toDBDate($request->get('begin_date'));
                $window->end_date = DateMaker::toDBDate($request->get('end_date'));
                $window->save();
	}
}