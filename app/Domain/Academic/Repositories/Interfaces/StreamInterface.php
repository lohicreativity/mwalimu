<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface StreamInterface{
	
	public function store(Request $request);
}