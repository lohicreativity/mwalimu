<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface StreamComponentInterface{
	
	public function store(Request $request);
}