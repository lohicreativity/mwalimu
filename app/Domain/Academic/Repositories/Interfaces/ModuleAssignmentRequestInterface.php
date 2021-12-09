<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ModuleAssignmentRequestInterface{
	
	public function store(Request $request);
}