<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Currency;
use App\Domain\Settings\Repositories\Interfaces\CurrencyInterface;

class CurrencyAction implements CurrencyInterface{
	
	public function store(Request $request){
	     $currency = new Currency;
           $currency->name = $request->get('name');
           $currency->code = $request->get('code');
           $currency->factor = $request->get('factor');
           $currency->save();
	}

	public function update(Request $request){
	     $currency = Currency::find($request->get('currency_id'));
           $currency->name = $request->get('name');
           $currency->code = $request->get('code');
           $currency->factor = $request->get('factor');
           $currency->save();
	}
}