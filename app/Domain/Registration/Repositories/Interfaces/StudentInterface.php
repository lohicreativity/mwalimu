<?php
namespace App\Domain\Registration\Repositories\Interfaces;

use Illuminate\Http\Request;

interface StudentInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}