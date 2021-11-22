<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface CourseWorkComponentInterface{
	
	public function store(Request $request);

}