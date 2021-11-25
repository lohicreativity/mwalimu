<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface PostponementInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}