<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Repositories\Interfaces\FeeTypeInterface;

class FeeTypeAction implements FeeTypeInterface{
	
	public function store(Request $request){
		$type = new FeeType;
        $type->code = $request->get('code');
        $type->gfs_code = $request->get('gfs_code');
        $type->payment_option = $request->get('payment_option');
        $type->duration = $request->get('duration');
        $type->description = $request->get('description');
        $type->is_external = $request->get('is_external') == 'EXTERNAL' || $request->get('is_internal') == 'BOTH'? 1 : 0;
        $type->is_external = $request->get('is_internal') == 'INTERNAL' || $request->get('is_internal') == 'BOTH'? 1 : 0;
        $type->save();
	}

	public function update(Request $request){
		$type = type::find($request->get('type_id'));
        $type->name = $request->get('name');
        $type->save();
	}
}

$table->string('name');
            $table->string('code');
            $table->string('gfs_code');
            $table->string('payment_option');
            $table->mediumInteger('duration');
            $table->string('description');
            $table->tinyInteger('is_external');
            $table->tinyInteger('is_internal');
            $table->tinyInteger('is_paid_per_semester');
            $table->tinyInteger('is_paid_only_once');