<?php
namespace App\Domain\Academic\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ModuleAssignmentInterface{
	
	public function store(Request $request);

	public function update(Request $request);

	public function acceptConfirmation(Request $request, $id);

	public function rejectConfirmation(Request $request, $id);
}