<?php
namespace App\Domain\Finance\Repositories\Interfaces;

use Illuminate\Http\Request;

interface FeeTypeInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}